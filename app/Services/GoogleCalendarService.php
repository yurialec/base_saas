<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\SocialAccount;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use GuzzleHttp\Client as HttpClient;
use RuntimeException;

class GoogleCalendarService
{
    public function createEvent(Agenda $agenda): string
    {
        $account = SocialAccount::where('user_id', $agenda->user_id)->where('provider', 'google')->first();

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

            $token = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);
            if (empty($token['access_token']) || isset($token['error'])) {
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
        $event = new Event([
            'summary' => 'Agendamento',
            'description' => $agenda->comentario,
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => $timezone],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => $timezone],
        ]);

        $created = (new Calendar($client))->events->insert('primary', $event);
        if (!$created->getId()) {
            throw new RuntimeException('O Google nao retornou o identificador do evento.');
        }

        return $created->getId();
    }
}
