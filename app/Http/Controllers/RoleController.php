<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->all();
        return response()->json($roles);
    }

    public function dropdownList()
    {
        $roles = $this->roleService->dropdownList();
        return response()->json($roles);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = $this->roleService->find($id);

        if ($role) {
            return response()->json($role);
        } else {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = $this->roleService->update($request->validated(), $id);
        return response()->json($role);
    }

    public function delete($id)
    {
        $role = $this->roleService->delete($id);
        return response()->json($role);
    }
}
