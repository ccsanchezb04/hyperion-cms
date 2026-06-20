<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario super-admin por defecto
        $superAdmin = User::firstOrCreate(
            ['user_dsemai' => 'admin@hyperion.local'],
            [
                'user_nmname' => 'Super Administrator',
                'user_cdpass' => Hash::make('admin123'),
                'user_cdstat' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol super-admin
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->sync([$superAdminRole->role_idrole]);
        }

        // Crear usuario editor de ejemplo
        $editor = User::firstOrCreate(
            ['user_dsemai' => 'editor@hyperion.local'],
            [
                'user_nmname' => 'Editor User',
                'user_cdpass' => Hash::make('editor123'),
                'user_cdstat' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol editor
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        if ($editorRole) {
            $editor->roles()->sync([$editorRole->role_idrole]);
        }

        // Crear usuario viewer de ejemplo
        $viewer = User::firstOrCreate(
            ['user_dsemai' => 'viewer@hyperion.local'],
            [
                'user_nmname' => 'Viewer User',
                'user_cdpass' => Hash::make('viewer123'),
                'user_cdstat' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol viewer
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        if ($viewerRole) {
            $viewer->roles()->sync([$viewerRole->role_idrole]);
        }

        $this->command->info('✅ Users seeded successfully');
        $this->command->info('📧 Default credentials:');
        $this->command->info('   Super Admin: admin@hyperion.local / admin123');
        $this->command->info('   Editor: editor@hyperion.local / editor123');
        $this->command->info('   Viewer: viewer@hyperion.local / viewer123');
    }
}
