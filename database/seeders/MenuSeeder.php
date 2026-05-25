<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear menú principal
        $mainMenu = Menu::firstOrCreate(
            ['menu_cdslug' => 'main'],
            ['menu_nmname' => 'Main Menu']
        );

        // Items del menú principal
        $mainMenuItems = [
            [
                'mnit_nmlabe' => 'Home',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/',
                'mnit_idpare' => null,
                'mnit_nrorde' => 1,
            ],
            [
                'mnit_nmlabe' => 'About',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/about',
                'mnit_idpare' => null,
                'mnit_nrorde' => 2,
            ],
            [
                'mnit_nmlabe' => 'Blog',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/blog',
                'mnit_idpare' => null,
                'mnit_nrorde' => 3,
            ],
            [
                'mnit_nmlabe' => 'Contact',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/contact',
                'mnit_idpare' => null,
                'mnit_nrorde' => 4,
            ],
        ];

        foreach ($mainMenuItems as $item) {
            MenuItem::firstOrCreate(
                [
                    'mnit_idmenu' => $mainMenu->menu_idmenu,
                    'mnit_nmlabe' => $item['mnit_nmlabe'],
                ],
                [
                    'mnit_cdtype' => $item['mnit_cdtype'],
                    'mnit_dsurll' => $item['mnit_dsurll'],
                    'mnit_idpare' => $item['mnit_idpare'],
                    'mnit_nrorde' => $item['mnit_nrorde'],
                ]
            );
        }

        // Crear menú footer
        $footerMenu = Menu::firstOrCreate(
            ['menu_cdslug' => 'footer'],
            ['menu_nmname' => 'Footer Menu']
        );

        // Items del menú footer
        $footerMenuItems = [
            [
                'mnit_nmlabe' => 'Privacy Policy',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/privacy',
                'mnit_idpare' => null,
                'mnit_nrorde' => 1,
            ],
            [
                'mnit_nmlabe' => 'Terms of Service',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/terms',
                'mnit_idpare' => null,
                'mnit_nrorde' => 2,
            ],
            [
                'mnit_nmlabe' => 'Cookie Policy',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/cookies',
                'mnit_idpare' => null,
                'mnit_nrorde' => 3,
            ],
        ];

        foreach ($footerMenuItems as $item) {
            MenuItem::firstOrCreate(
                [
                    'mnit_idmenu' => $footerMenu->menu_idmenu,
                    'mnit_nmlabe' => $item['mnit_nmlabe'],
                ],
                [
                    'mnit_cdtype' => $item['mnit_cdtype'],
                    'mnit_dsurll' => $item['mnit_dsurll'],
                    'mnit_idpare' => $item['mnit_idpare'],
                    'mnit_nrorde' => $item['mnit_nrorde'],
                ]
            );
        }

        // Crear menú de administración
        $adminMenu = Menu::firstOrCreate(
            ['menu_cdslug' => 'admin'],
            ['menu_nmname' => 'Admin Menu']
        );

        // Items del menú admin
        $adminMenuItems = [
            [
                'mnit_nmlabe' => 'Dashboard',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/dashboard',
                'mnit_idpare' => null,
                'mnit_nrorde' => 1,
            ],
            [
                'mnit_nmlabe' => 'Contents',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/admin/contents',
                'mnit_idpare' => null,
                'mnit_nrorde' => 2,
            ],
            [
                'mnit_nmlabe' => 'Media',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/admin/media',
                'mnit_idpare' => null,
                'mnit_nrorde' => 3,
            ],
            [
                'mnit_nmlabe' => 'Categories',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/admin/categories',
                'mnit_idpare' => null,
                'mnit_nrorde' => 4,
            ],
            [
                'mnit_nmlabe' => 'Users',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/admin/users',
                'mnit_idpare' => null,
                'mnit_nrorde' => 5,
            ],
            [
                'mnit_nmlabe' => 'Settings',
                'mnit_cdtype' => 'url',
                'mnit_dsurll' => '/admin/settings',
                'mnit_idpare' => null,
                'mnit_nrorde' => 6,
            ],
        ];

        foreach ($adminMenuItems as $item) {
            MenuItem::firstOrCreate(
                [
                    'mnit_idmenu' => $adminMenu->menu_idmenu,
                    'mnit_nmlabe' => $item['mnit_nmlabe'],
                ],
                [
                    'mnit_cdtype' => $item['mnit_cdtype'],
                    'mnit_dsurll' => $item['mnit_dsurll'],
                    'mnit_idpare' => $item['mnit_idpare'],
                    'mnit_nrorde' => $item['mnit_nrorde'],
                ]
            );
        }

        $this->command->info('✅ Menus seeded successfully');
    }
}
