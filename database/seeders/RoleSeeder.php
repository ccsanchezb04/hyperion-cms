<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_nmname' => 'Super Administrator',
                'role_cdslug' => 'super-admin',
            ],
            [
                'role_nmname' => 'Administrator',
                'role_cdslug' => 'admin',
            ],
            [
                'role_nmname' => 'Editor',
                'role_cdslug' => 'editor',
            ],
            [
                'role_nmname' => 'Author',
                'role_cdslug' => 'author',
            ],
            [
                'role_nmname' => 'Viewer',
                'role_cdslug' => 'viewer',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['role_cdslug' => $role['role_cdslug']],
                ['role_nmname' => $role['role_nmname']]
            );
        }

        $this->command->info('✅ Roles seeded successfully');
    }
}
