<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;
    protected $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function index()
    {
        return response()->json($this->userService->all());
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = $this->userService->find($id);

        if (! $user) {
            return response()->json(['message' => 'User não encontrado.'], 404);
        }

        return response()->json($user);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->update($request->validated(), $id);

        if (! $user) {
            return response()->json(['message' => 'User não encontrado.'], 404);
        }

        return response()->json($user);
    }

    public function destroy($id)
    {
        if (Auth::id() == $id) {
            return response()->json(['message' => 'Você não tem permissão para excluir este usuário.'], 403);
        }

        $deleted = $this->userService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'User não encontrado.'], 404);
        }

        return response()->json(null, 204);
    }

    public function listRoles()
    {
        $roles = $this->roleService->dropdownList();
        return response()->json($roles);
    }

    public function profile()
    {
        $user = $this->userService->profile(Auth::id());

        return response()->json($user);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->userService->updateProfile(
            $request->validated(),
            Auth::id()
        );

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user,
        ]);
    }
}
