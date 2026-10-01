<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\SocialAccount;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleCalendarService
{
    public function createEvent(Agenda $agenda): string
    {
        $account = SocialAccount::where('user_id', $agenda->user_id)->where('provider', 'google')->first();

        Log::channel('google_calendar')->info('Iniciando sincronizacao de agendamento.', [
            'agenda_id' => $agenda->id,
            'tenant_id' => $agenda->tenant_id,
            'user_id' => $agenda->user_id,
            'google_account_found' => (bool) $account,
            'has_refresh_token' => (bool) ($account && $account->refresh_token),
            'token_expires_at' => $account && $account->token_expires_at
                ? $account->token_expires_at->toIso8601String()
                : null,
        ]);

        if (!$account) {
            throw new RuntimeException('Conta Google nao vinculada.');
        }

        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setHttpClient(new HttpClient(['connect_timeout' => 5, 'timeout' => 20]));

        if (!$account->token_expires_at || $account->token_expires_at->lte(Carbon::now()->addMinute())) {
            if (!$account->refresh_token) {
                throw new RuntimeException('Autorizacao Google expirada.');
            }

            try {
                $token = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);
            } catch (Throwable $exception) {
                $invalidGrant = $this->isInvalidGrant($exception);
                $this->logGoogleFailure('Falha ao renovar token de acesso Google.', $agenda, $exception);

                if ($invalidGrant) {
                    $this->invalidateConnection($account, $agenda);

                    throw new RuntimeException(
                        'A conexao com Google Calendar expirou ou foi revogada. Clique em "Reconectar Google Calendar" antes de sincronizar novamente.',
                        (int) $exception->getCode(),
                        $exception
                    );
                }

                throw new RuntimeException(
                    'Nao foi possivel renovar a autorizacao Google.',
                    (int) $exception->getCode(),
                    $exception
                );
            }

            if (empty($token['access_token']) || isset($token['error'])) {
                Log::channel('google_calendar')->error('Google recusou a renovacao do token.', [
                    'agenda_id' => $agenda->id,
                    'tenant_id' => $agenda->tenant_id,
                    'user_id' => $agenda->user_id,
                    'google_error' => $token['error'] ?? null,
                    'google_error_description' => $token['error_description'] ?? null,
                ]);

                if (($token['error'] ?? null) === 'invalid_grant') {
                    $this->invalidateConnection($account, $agenda);

                    throw new RuntimeException(
                        'A conexao com Google Calendar expirou ou foi revogada. Clique em "Reconectar Google Calendar" antes de sincronizar novamente.'
                    );
                }

                throw new RuntimeException('Nao foi possivel renovar a autorizacao Google.');
            }

            // Os mutators de SocialAccount mantem os tokens criptografados.
            $account->access_token = $token['access_token'];
            $account->token_expires_at = Carbon::now()->addSeconds((int) ($token['expires_in'] ?? 3600));
            if (!empty($token['refresh_token'])) {
                $account->refresh_token = $token['refresh_token'];
            }
            $account->save();
        }

        $client->setAccessToken([
            'access_token' => $account->access_token,
            'created' => time(),
            'expires_in' => max(1, $account->token_expires_at->getTimestamp() - time()),
        ]);

        $timezone = config('services.google.calendar_timezone', 'America/Sao_Paulo');
        $start = Carbon::parse($agenda->data->format('Y-m-d').' '.$agenda->hora, $timezone);
        $end = $start->copy()->addMinutes(max(1, (int) config('services.google.calendar_duration', 30)));
        // Um ID estavel permite recuperar o evento depois de timeout ou falha local.
        $eventId = hash_hmac('sha256', implode(':', [
            'agenda', $agenda->tenant_id, $agenda->user_id, $agenda->id, $agenda->getRawOriginal('created_at'),
        ]), config('app.key'));
        $calendar = new Calendar($client);
        try {
            return $this->confirmedEventId($calendar->events->get('primary', $eventId), $eventId);
        } catch (GoogleServiceException $exception) {
            if ((int) $exception->getCode() !== 404) {
                throw $exception;
            }
        }

        $event = new Event([
            'id' => $eventId,
            'extendedProperties' => ['private' => ['agenda_reference' => $eventId]],
            'summary' => 'Agendamento',
            'description' => $agenda->comentario,
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => $timezone],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => $timezone],
        ]);

        try {
            $created = $calendar->events->insert('primary', $event);
        } catch (GoogleServiceException $exception) {
            if ((int) $exception->getCode() !== 409) {
                $this->logGoogleFailure('Falha ao criar evento no Google Calendar.', $agenda, $exception);
                throw $exception;
            }
            $created = $calendar->events->get('primary', $eventId);
        }

        return $this->confirmedEventId($created, $eventId);
    }

    private function confirmedEventId(Event $event, string $eventId): string
    {
        $properties = $event->getExtendedProperties();
        $private = $properties ? $properties->getPrivate() : [];
        if ($event->getStatus() === 'cancelled' || $event->getId() !== $eventId
            || ($private['agenda_reference'] ?? null) !== $eventId) {
            throw new RuntimeException('O evento remoto nao pode ser confirmado para este agendamento.');
        }

        return $eventId;
    }

    private function logGoogleFailure(string $message, Agenda $agenda, Throwable $exception): void
    {
        $context = [
            'agenda_id' => $agenda->id,
            'tenant_id' => $agenda->tenant_id,
            'user_id' => $agenda->user_id,
            'exception' => get_class($exception),
            'code' => (int) $exception->getCode(),
            'message' => $exception->getMessage(),
        ];

        if ($exception instanceof ClientException && $exception->getResponse()) {
            $context['google_response'] = substr(
                (string) $exception->getResponse()->getBody(),
                0,
                1000
            );
        }

        if ($exception instanceof GoogleServiceException) {
            $context['google_errors'] = $exception->getErrors();
        }

        Log::channel('google_calendar')->error($message, $context);
    }

    private function isInvalidGrant(Throwable $exception): bool
    {
        if (! $exception instanceof ClientException || ! $exception->getResponse()) {
            return false;
        }

        $response = json_decode((string) $exception->getResponse()->getBody(), true);

        return is_array($response) && ($response['error'] ?? null) === 'invalid_grant';
    }

    private function invalidateConnection(SocialAccount $account, Agenda $agenda): void
    {
        // Mantém o access token expirado apenas porque a coluna atual não aceita NULL.
        // Sem refresh token e com expiração no passado, a conexão deixa de estar "ready".
        $account->refresh_token = null;
        $account->token_expires_at = Carbon::now()->subSecond();
        $account->save();

        Log::channel('google_calendar')->notice('Conexao Google Calendar marcada como expirada.', [
            'agenda_id' => $agenda->id,
            'tenant_id' => $agenda->tenant_id,
            'user_id' => $agenda->user_id,
            'social_account_id' => $account->id,
            'reason' => 'invalid_grant',
        ]);
    }
}
