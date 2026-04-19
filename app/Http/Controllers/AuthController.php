<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AuthController extends Controller
{
    public function formLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if (!$user->tenant_id) {
            return response()->json(['message' => 'Usuário sem tenant vinculado.'], 403);
        }

        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant não encontrado.'], 404);
        }

        $domainRecord = $tenant->domains->first();

        if (!$domainRecord) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Empresa sem domínio configurado.',
            ]);
        }

        $domainName = $domainRecord->domain;

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    public function formRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string',
            'domain'      => 'required|string|unique:domains,domain',
            'user_name'   => 'required|string',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
        ]);

        DB::beginTransaction();
        try {
            $tenant = Tenant::create(['name' => $request->tenant_name]);
            $tenant->domains()->create(['domain' => $request->domain]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            $ownerRole = Role::firstOrCreate(
                [
                    'name' => 'owner',
                    'guard_name' => 'web',
                    'tenant_id' => $tenant->id
                ]
            );

            $user = User::create([
                'name'      => $request->user_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole($ownerRole);

            $token = $user->createToken('api-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Conta criada com sucesso',
                'token'   => $token,
                'user'    => $user,
                'tenant'  => $tenant
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao criar conta: ' . $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
