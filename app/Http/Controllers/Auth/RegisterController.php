<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    private const HANDOFF_TTL_SECONDS = 60;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the initial tenant and administrator registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $this->uniqueTenantSlug($validated['company_name']),
                'active' => true,
            ]);

            $role = Role::create([
                'name' => 'Administrativo',
                'description' => 'Perfil administrativo do sistema',
                'is_active' => true,
                'tenant_id' => $tenant->id,
            ]);

            foreach ([2, 3, 4] as $permissionId) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'tenant_id' => $tenant->id,
                ]);
            }

            return User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tenant_id' => $tenant->id,
                'role_id' => $role->id,
            ]);
        });

        $token = Str::random(64);
        $expiresAt = now()->addSeconds(self::HANDOFF_TTL_SECONDS);
        $stored = Cache::put($this->handoffCacheKey($token), [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'tenant_slug' => $user->tenant->slug,
            'remember' => false,
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

    /**
     * Generate an available URL slug for a tenant.
     *
     * @param  string  $companyName
     * @return string
     */
    protected function uniqueTenantSlug(string $companyName): string
    {
        $baseSlug = Str::slug($companyName) ?: 'empresa';
        $slug = $baseSlug;
        $suffix = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function handoffCacheKey(string $token): string
    {
        return 'tenant-login-handoff:'.hash('sha256', $token);
    }
}
