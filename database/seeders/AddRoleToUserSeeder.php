<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddRoleToUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'desenvolvedor')->firstOrFail();
        $user = User::where('tenant_id', $tenant->id)
            ->where('email', 'yuri@email.com')
            ->first();
        $role = Role::where('tenant_id', $tenant->id)
            ->where('name', 'Administrativo')
            ->first();

        if ($user && $role) {
            $user->role_id = $role->id;
            $user->save();
        }
    }
}
