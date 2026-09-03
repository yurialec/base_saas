<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['slug' => 'tenant-padrao'],
            [
                'name' => 'Tenant Padrão',
                'active' => true,
            ]
        );
    }
}
