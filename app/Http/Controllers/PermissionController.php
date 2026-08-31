<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Services\PermissionService;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        return response()->json($this->permissionService->all());
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = $this->permissionService->create($request->validated());

        return response()->json($permission, 201);
    }

    public function show($id)
    {
        $permission = $this->permissionService->find($id);

        if (! $permission) {
            return response()->json(['message' => 'Permission não encontrado.'], 404);
        }

        return response()->json($permission);
    }

    public function update(UpdatePermissionRequest $request, $id)
    {
        $permission = $this->permissionService->update($request->validated(), $id);

        if (! $permission) {
            return response()->json(['message' => 'Permission não encontrado.'], 404);
        }

        return response()->json($permission);
    }

    public function destroy($id)
    {
        $deleted = $this->permissionService->delete($id);

        if (! $deleted) {
            return response()->json(['message' => 'Permission não encontrado.'], 404);
        }

        return response()->json(null, 204);
    }
}
