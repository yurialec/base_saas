<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository
{
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function all()
    {
        return $this->permission->query()->get();
    }

    public function find($id)
    {
        return $this->permission->query()->find($id);
    }

    public function create(array $data)
    {
        return $this->permission->create($data);
    }

    public function update(array $data, $id)
    {
        $permission = $this->find($id);

        if (! $permission) {
            return null;
        }

        $permission->update($data);

        return $permission;
    }

    public function delete($id)
    {
        return $this->permission->destroy($id);
    }
}
