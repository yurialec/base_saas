<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function all()
    {
        return $this->role
            ->with(['permissions' => function ($query) {
                $query->select('permissions.id', 'permissions.name');
            }])
            ->defaultOrder()
            ->get()
            ->toTree();
    }

    public function dropdownList()
    {
        return $this->role
            ->select([
                'id',
                'name'
            ])
            ->defaultOrder()
            ->get();
    }

    public function find($id)
    {
        return $this->role
            ->query()
            ->with('permissions')
            ->find($id);
    }

    public function create(array $data)
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = $this->role->create($data);
        $role->permissions()->sync($permissionIds);

        return $role->load('permissions');
    }

    public function delete($id)
    {
        $role = $this->find($id);

        if (!$role) {
            return 0;
        }

        $role->permissions()->detach();

        return $role->delete();
    }

    public function update($data, $id)
    {
        $role = $this->find($id);

        return $this->updateModel($role, $data);
    }

    public function updateModel(Role $role, array $data)
    {
        $permissionIds = $data['permissions'] ?? null;
        unset($data['permissions']);

        $role->update($data);

        if ($permissionIds !== null) {
            $role->permissions()->sync($permissionIds);
        }

        return $role->load('permissions');
    }
}
