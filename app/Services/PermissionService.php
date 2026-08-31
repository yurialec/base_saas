<?php

namespace App\Services;

use App\Repositories\PermissionRepository;

class PermissionService
{
    protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function all()
    {
        return $this->permissionRepository->all();
    }

    public function find($id)
    {
        return $this->permissionRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->permissionRepository->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->permissionRepository->update($data, $id);
    }

    public function delete($id)
    {
        return $this->permissionRepository->delete($id);
    }
}
