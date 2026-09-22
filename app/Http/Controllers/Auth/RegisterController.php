<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->middleware('guest');

        $this->userService = $userService;
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
     * @param  \App\Http\Requests\Auth\RegisterCompanyRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterCompanyRequest $request)
    {
        $validated = $request->validated();
        $googleRegistration = $request->session()->get('google_registration');

        if ($googleRegistration && Str::lower($validated['email']) !== Str::lower($googleRegistration['email'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'O e-mail deve ser o mesmo autorizado na conta Google.',
                ]);
        }

        $user = DB::transaction(function () use ($validated, $googleRegistration) {
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'phone' => $validated['phone'],
                'cpf_cnpj' => $validated['cpf_cnpj'],
                'slug' => $this->uniqueTenantSlug($validated['company_name']),
                'active' => true,
            ]);

            $role = Role::create([
                'name' => 'Administrativo',
                'description' => 'Perfil administrativo do sistema',
                'is_active' => true,
                'tenant_id' => $tenant->id,
            ]);

            $permissions = [
                [
                    'name' => 'Listar Perfis',
                    'slug' => 'roles',
                    'description' => 'Permissão para listar todos os perfis do sistema.',
                ],
                [
                    'name' => 'Listar Usuários',
                    'slug' => 'users',
                    'description' => 'Permissão para listar todos os usuários do sistema.',
                ],
            ];

            foreach ($permissions as $permission) {
                $createdPermission = Permission::updateOrCreate(
                    [
                        'slug' => $permission['slug'],
                        'tenant_id' => $tenant->id,
                    ],
                    $permission + [
                        'is_active' => true,
                        'tenant_id' => $tenant->id,
                    ]
                );

                if (!in_array($createdPermission->slug, ['roles', 'permissions', 'users'], true)) {
                    continue;
                }

                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $createdPermission->id,
                    'tenant_id' => $tenant->id,
                ]);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($googleRegistration ? Str::random(64) : $validated['password']),
                'tenant_id' => $tenant->id,
                'role_id' => $role->id,
            ]);

            if ($googleRegistration) {
                SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => 'google',
                    'provider_id' => $googleRegistration['provider_id'],
                    'avatar' => $googleRegistration['avatar'],
                    'access_token' => Crypt::decryptString($googleRegistration['access_token']),
                    'refresh_token' => $googleRegistration['refresh_token']
                        ? Crypt::decryptString($googleRegistration['refresh_token'])
                        : null,
                    'token_expires_at' => $googleRegistration['token_expires_at'],
                ]);
            }

            return $user;
        });

        $request->session()->forget('google_registration');

        Auth::login($user);

        $request->session()->regenerate();

        $tenant = $user->tenant;

        $this->userService->addSessionVariables($user->id);

        return redirect("/{$tenant->slug}/dashboard");
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
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
