<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        Auth::login($user);
        $request->session()->regenerate();
        $sessionUser = $this->userService->addSessionVariables($user->id);
        $request->session()->forget('url.intended');

        return redirect()->away(
            $this->userService->tenantUrl(
                $sessionUser['tenant']['slug'],
                RouteServiceProvider::HOME
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
}
