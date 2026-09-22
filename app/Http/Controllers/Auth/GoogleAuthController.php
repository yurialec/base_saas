<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class GoogleAuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        $this->middleware(function (Request $request, $next) {
            if ($request->user()) {
                abort_unless($request->user()->tenant, 403);

                return redirect('/'.$request->user()->tenant->slug.'/dashboard');
            }

            return $next($request);
        });
    }

    public function redirectToGoogle(Request $request)
    {
        $intent = $request->query('intent', 'registration');

        if (! in_array($intent, ['registration', 'login'], true)) {
            abort(404);
        }

        $request->session()->forget(['google_registration', 'user', 'sidebar', 'sidebar_access_signature']);
        $request->session()->put('google_oauth_intent', $intent);

        if ($intent === 'login') {
            return Socialite::driver('google')
                ->setScopes(['openid', 'profile', 'email'])
                ->with(['prompt' => 'select_account'])
                ->redirect();
        }

        return Socialite::driver('google')
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/calendar.events',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $intent = $request->session()->pull('google_oauth_intent');
        $errorRoute = $intent === 'registration' ? 'register' : 'login';

        if (! in_array($intent, ['registration', 'login'], true)) {
            return redirect()->route('login')->withErrors([
                'google' => 'A solicitação de acesso expirou. Inicie novamente o acesso com Google.',
            ]);
        }

        if ($request->has('error')) {
            $request->session()->forget('state');

            return redirect()->route($errorRoute)->withErrors([
                'google' => 'A autorização do Google não foi concluída. Tente novamente.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            if (! $email) {
                throw new RuntimeException('O Google não retornou um e-mail para esta conta.');
            }

            $account = SocialAccount::where('provider', 'google')
                ->where('provider_id', $googleUser->getId())
                ->first();

            if ($account) {
                return $this->authenticateExistingUser($request, $account->user);
            }

            if ($intent === 'login') {
                return redirect()->route('login')->withErrors([
                    'google' => 'Esta conta Google não está vinculada a um usuário. Se já possui cadastro com senha, entre com sua senha; caso contrário, cadastre-se com Google.',
                ]);
            }

            if (User::withoutGlobalScopes()->where('email', $email)->exists()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Este e-mail já possui cadastro. Entre com sua senha para conectar o Google posteriormente.',
                ]);
            }

            $request->session()->put('google_registration', [
                'provider_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Usuário Google',
                'email' => $email,
                'avatar' => $googleUser->getAvatar(),
                'access_token' => Crypt::encryptString($googleUser->token),
                'refresh_token' => $googleUser->refreshToken
                    ? Crypt::encryptString($googleUser->refreshToken)
                    : null,
                'token_expires_at' => $googleUser->expiresIn
                    ? Carbon::now()->addSeconds((int) $googleUser->expiresIn)->toDateTimeString()
                    : null,
            ]);

            return redirect()->route('register');
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->forget(['user', 'sidebar', 'sidebar_access_signature']);

            return redirect()->route($errorRoute)->withErrors([
                'google' => 'Não foi possível autorizar sua conta Google. Tente novamente.',
            ]);
        }
    }

    protected function authenticateExistingUser(Request $request, ?User $user)
    {
        if (! $user || ! $user->tenant || ! $user->tenant->active
            || ! $user->role || ! $user->role->is_active
            || (string) $user->role->tenant_id !== (string) $user->tenant_id) {
            return redirect()->route('login')->withErrors([
                'email' => 'Esta conta Google não possui uma organização ativa no sistema.',
            ]);
        }

        // Define o tenant antes de carregar perfil e permissoes com global scopes.
        $request->session()->put('user.tenant.id', $user->tenant_id);
        $request->session()->regenerate();
        $this->userService->addSessionVariables($user->id);
        Auth::login($user);
        $request->session()->forget('google_registration');

        return redirect('/'.$user->tenant->slug.'/dashboard');
    }
}
