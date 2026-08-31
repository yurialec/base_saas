<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = \App\Models\Permission::get()->pluck('id')->toArray();
        $roleAdmin = \App\Models\Role::where('name', 'Administrativo')->pluck('id')->first();

        foreach ($permissions as $permission) {
            RolePermission::firstOrCreate([
                'role_id' => $roleAdmin,
                'permission_id' => $permission,
            ]);
        }
    }
}
