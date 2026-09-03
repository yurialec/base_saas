<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\UserService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const HANDOFF_TTL_SECONDS = 60;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->middleware('guest')->except(['logout', 'consumeHandoff']);

        $this->userService = $userService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::once($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'E-mail ou senha inválidos.',
                ])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (!$user || !$user->tenant || !$user->role || !$user->tenant->active) {
            abort(403, 'Usuário sem tenant ou perfil válido.');
        }

        $token = Str::random(64);
        $expiresAt = now()->addSeconds(self::HANDOFF_TTL_SECONDS);
        $stored = Cache::put($this->handoffCacheKey($token), [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'tenant_slug' => $user->tenant->slug,
            'remember' => $request->boolean('remember'),
        ], $expiresAt);

        if (! $stored) {
            abort(500, 'Não foi possível iniciar a sessão do tenant.');
        }

        $request->session()->forget('url.intended');

        return redirect()->away(
            URL::temporarySignedRoute(
                'tenant.auth.handoff',
                $expiresAt,
                [
                    'tenant' => $user->tenant->slug,
                    'token' => $token,
                ]
            )
        );
    }

    public function consumeHandoff(Request $request, string $tenant, string $token)
    {
        $handoff = Cache::pull($this->handoffCacheKey($token));

        if (!is_array($handoff)
            || !isset($handoff['user_id'], $handoff['tenant_id'], $handoff['tenant_slug'])
            || !hash_equals((string) $handoff['tenant_slug'], $tenant)) {
            abort(403, 'Link de autenticação inválido ou expirado.');
        }

        $user = $this->userService->find($handoff['user_id']);

        if (!$user
            || !$user->tenant
            || !$user->role
            || !$user->tenant->active
            || (int) $user->tenant_id !== (int) $handoff['tenant_id']
            || !hash_equals((string) $user->tenant->slug, $tenant)) {
            abort(403, 'Usuário ou tenant inválido.');
        }

        Auth::login($user, (bool) ($handoff['remember'] ?? false));
        $request->session()->regenerate();
        $this->userService->addSessionVariables($user->id);
        $request->session()->forget('url.intended');

        return redirect(RouteServiceProvider::HOME);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('login');
    }

    private function handoffCacheKey(string $token): string
    {
        return 'tenant-login-handoff:'.hash('sha256', $token);
    }
}
