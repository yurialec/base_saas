<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleCalendarConnectionController extends Controller
{
    private function provider()
    {
        return Socialite::driver('google')->redirectUrl(
            config('services.google.calendar_redirect') ?: route('google.calendar.callback')
        );
    }

    public function redirect(Request $request)
    {
        abort_unless($request->user()->tenant && $request->user()->tenant->active, 403);

        $response = $this->provider()
            ->setScopes(['openid', 'email', 'profile', GoogleCalendarConnectionService::SCOPE])
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account', 'include_granted_scopes' => 'true'])
            ->redirect();

        $request->session()->put('google_calendar_connection', [
            'user_id' => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'state' => $request->session()->get('state'),
            'expires_at' => time() + 600,
        ]);

        // O browser navega para o Google; axios nao segue um redirect OAuth entre dominios.
        return response()->json(['url' => $response->getTargetUrl()]);
    }

    public function callback(Request $request, GoogleCalendarConnectionService $service)
    {
        $user = $request->user();
        abort_unless($user->tenant && $user->tenant->active, 403);
        $context = $request->session()->pull('google_calendar_connection');
        $state = $request->query('state');

        if (!$context || !is_string($state) || !is_string($context['state'] ?? null)
            || !hash_equals($context['state'], $state)
            || ($context['expires_at'] ?? 0) < time()
            || (string) ($context['user_id'] ?? '') !== (string) $user->id
            || (string) ($context['tenant_id'] ?? '') !== (string) $user->tenant_id) {
            return $this->finish($request, 'error', 'A conexão expirou ou a sessão foi alterada. Inicie novamente pela Agenda.');
        }

        if ($request->has('error')) {
            $request->session()->forget('state');
            return $this->finish($request, 'error', 'A autorização do Google foi cancelada ou recusada.');
        }

        try {
            // Socialite tambem valida e consome o state da sessao.
            $googleUser = $this->provider()->user();
            $service->connect($user, $googleUser);
            return $this->finish($request, 'success', 'Google Calendar conectado. Você já pode sincronizar seus agendamentos pendentes.');
        } catch (ValidationException $exception) {
            return $this->finish($request, 'error', $exception->errors()['google'][0]);
        } catch (Throwable $exception) {
            Log::warning('Falha ao conectar Google Calendar.', ['user_id' => $user->id, 'exception' => get_class($exception)]);
            return $this->finish($request, 'error', 'Não foi possível conectar o calendário. Verifique a autorização e tente novamente.');
        }
    }

    private function finish(Request $request, string $status, string $message)
    {
        // Consumido pela API depois do carregamento da pagina Vue.
        $request->session()->put('google_calendar_result', compact('status', 'message'));
        return redirect('/'.$request->user()->tenant->slug.'/agenda');
    }
}
