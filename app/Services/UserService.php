<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function all()
    {
        return $this->userRepository->all();
    }

    public function find($id)
    {
        return $this->userRepository->find($id);
    }

    public function create(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        return $this->userRepository->create($data);
    }

    public function update(array $data, $id)
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $this->userRepository->update($data, $id);
    }

    public function delete($id)
    {
        return $this->userRepository->delete($id);
    }

    public function addSessionVariables($id)
    {
        $user = $this->userRepository->find($id);

        $sessionUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'permissions' => $user->role->permissions
                    ->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'slug' => $permission->slug,
                        ];
                    })
                    ->toArray(),
            ],
        ];

        session(['user' => $sessionUser]);

        return $sessionUser;
    }

    public function profile($id)
    {
        return $this->userRepository->find($id);
    }

    public function updateProfile(array $data, $id)
    {
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        $this->userRepository->update($data, $id);

        // Mantém a sessão sincronizada enquanto ela ainda for usada pelo menu
        // e pelo middleware de permissões.
        $this->addSessionVariables($id);

        return $this->profile($id);
    }
}
