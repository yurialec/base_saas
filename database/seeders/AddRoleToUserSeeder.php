<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AddRoleToUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::where('email', 'yuri@email.com')->first();
        $role = \App\Models\Role::where('name', 'Administrativo')->first();

        if ($user && $role) {
            $user->role_id = $role->id;
            $user->save();
        }
    }
}
