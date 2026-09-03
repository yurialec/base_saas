<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
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
        $tenant = Tenant::where('slug', 'tenant-padrao')->firstOrFail();

        Role::updateOrCreate(
            [
                'name' => 'Administrativo',
                'parent_id' => null,
            ],
            [
                'description' => 'Perfil administrativo do sistema',
                'is_active' => true,
                'tenant_id' => $tenant->id,
            ]
        );
    }
}
