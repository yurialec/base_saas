<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Log;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }


    public function all()
    {
        try {
            return $this->roleRepository->all();
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar perfis', [
                'message' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    public function dropdownList()
    {
        try {
            return $this->roleRepository->dropdownList();
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar itens.', [
                'message' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    public function find($id)
    {
        try {
            return $this->roleRepository->find($id);
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar perfil', [
                'message' => $e->getMessage(),
                'id' => $id,
            ]);
            return null;
        }
    }


    public function create(array $data)
    {
        try {
            $role = $this->roleRepository->create($data);
            return $role;
        } catch (\Throwable $e) {
            Log::error('Erro ao criar perfil.', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->roleRepository->delete($id);
            return $deleted;
        } catch (\Throwable $e) {
            Log::error('Erro ao excluir perfil', [
                'message' => $e->getMessage(),
                'id' => $id,
            ]);
            return null;
        }
    }

    public function update(array $data, $id)
    {
        try {
            return $this->roleRepository->update($data, $id);
        } catch (\Throwable $e) {
            Log::error('Erro ao editar perfil.', [
                'message' => $e->getMessage(),
                'id' => $id,
            ]);

            return null;
        }
    }
}
