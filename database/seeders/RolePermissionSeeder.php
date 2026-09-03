<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'tenant-padrao')->firstOrFail();
        $permissions = Permission::where('tenant_id', $tenant->id)->pluck('id');
        $roleAdmin = Role::where('tenant_id', $tenant->id)
            ->where('name', 'Administrativo')
            ->firstOrFail();

        foreach ($permissions as $permissionId) {
            RolePermission::updateOrCreate([
                'role_id' => $roleAdmin->id,
                'permission_id' => $permissionId,
            ], [
                'tenant_id' => $tenant->id,
            ]);
        }
    }
}
