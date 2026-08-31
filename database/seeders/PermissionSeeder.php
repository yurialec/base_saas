<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'Listar Menus',
                'slug' => 'menus',
                'description' => 'Permissão para listar todas os menus do sistema.',
            ],
            [
                'name' => 'Listar Perfis',
                'slug' => 'roles',
                'description' => 'Permissão para listar todos os perfis do sistema.',
            ],
            [
                'name' => 'Listar Permissões',
                'slug' => 'permissions',
                'description' => 'Permissão para listar uma permissão existente no sistema.',
            ],
            [
                'name' => 'Listar Usuários',
                'slug' => 'users',
                'description' => 'Permissão para listar todos os usuários do sistema.',
            ],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
