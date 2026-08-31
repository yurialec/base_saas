<?php

namespace Database\Seeders;

use App\Models\SidebarMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SidebarMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            SidebarMenu::updateOrCreate(
                [
                    'route' => '/',
                    'parent_id' => null,
                ],
                [
                    'title' => 'Dashboard',
                    'icon' => 'bi bi-speedometer',
                    'url' => null,
                    'order' => 1,
                ]
            );

            $administracao = SidebarMenu::updateOrCreate(
                [
                    'title' => 'Administração',
                    'parent_id' => null,
                ],
                [
                    'icon' => 'bi bi-gear',
                    'route' => '#',
                    'url' => '#',
                    'order' => 2,
                ]
            );

            $menus = [
                [
                    'title' => 'Menus',
                    'icon' => 'bi bi-list-ul',
                    'route' => '/menus',
                    'url' => null,
                    'order' => 1,
                ],
                [
                    'title' => 'Perfis',
                    'icon' => 'bi bi-person-badge',
                    'route' => '/roles',
                    'url' => null,
                    'order' => 2,
                ],
                [
                    'title' => 'Permissões',
                    'icon' => 'bi bi-shield-lock',
                    'route' => '/permissions',
                    'url' => null,
                    'order' => 3,
                ],
                [
                    'title' => 'Usuários',
                    'icon' => 'bi bi-people',
                    'route' => '/users',
                    'url' => null,
                    'order' => 4,
                ],
            ];

            foreach ($menus as $menu) {
                $administracao->children()->updateOrCreate(
                    [
                        'route' => $menu['route'],
                    ],
                    $menu
                );
            }
        });
    }
}
