<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function all()
    {
        return $this->user
            ->with([
                'role:id,name',
                'role.permissions:id,name'
            ])
            ->get();
    }

    public function find($id)
    {
        return $this->user
            ->with([
                'role:id,name',
                'role.permissions:id,name,slug'
            ])
            ->find($id);
    }

    public function create(array $data)
    {
        return $this->user->create($data);
    }

    public function update(array $data, $id)
    {
        $user = $this->find($id);

        if (! $user) {
            return null;
        }

        $user->update($data);

        return $user;
    }

    public function delete($id)
    {
        return $this->user->destroy($id);
    }
}
