<?php

namespace App\Services;

use App\Repositories\UserRepository;
use LogicException;

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

        if (!$user || !$user->role || !$user->tenant) {
            throw new LogicException('O usuário deve possuir um tenant e um perfil válidos.');
        }

        $sessionUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tenant_id' => $user->tenant_id,
            'role_id' => $user->role_id,
            'tenant' => [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
                'slug' => $user->tenant->slug,
                'url' => $this->tenantUrl($user->tenant->slug),
            ],
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

    public function tenantUrl(string $slug, string $path = ''): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $parts = parse_url($baseUrl);

        if (! is_array($parts) || empty($parts['host'])) {
            throw new LogicException('A variável APP_URL deve conter uma URL válida.');
        }

        $scheme = $parts['scheme'] ?? 'http';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $basePath = trim($parts['path'] ?? '', '/');
        $path = trim($path, '/');
        $url = $scheme.'://'.$slug.'.'.$parts['host'].$port;

        if ($basePath !== '') {
            $url .= '/'.$basePath;
        }

        if ($path !== '') {
            $url .= '/'.$path;
        }

        return $url;
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
