<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'desenvolvedor')->firstOrFail();
        $role = Role::where('tenant_id', $tenant->id)
            ->where('name', 'Administrativo')
            ->firstOrFail();

        User::updateOrCreate(
            ['email' => 'yuri@email.com'],
            [
                'name' => 'Yuri',
                'password' => Hash::make('123456a!'),
                'role_id' => $role->id,
                'tenant_id' => $tenant->id,
            ]
        );
    }
}
