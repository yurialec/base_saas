<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::updateOrCreate(
            [
                'name' => 'Administrativo',
                'parent_id' => null,
            ],
            [
                'description' => 'Perfil administrativo do sistema',
                'is_active' => true,
            ]
        );
    }
}
