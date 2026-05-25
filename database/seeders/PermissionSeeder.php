<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos de contenidos
        $contentPermissions = [
            ['name' => 'Create Content', 'slug' => 'create-content'],
            ['name' => 'Edit Content', 'slug' => 'edit-content'],
            ['name' => 'Delete Content', 'slug' => 'delete-content'],
            ['name' => 'Publish Content', 'slug' => 'publish-content'],
            ['name' => 'View Content', 'slug' => 'view-content'],
        ];

        // Permisos de categorías
        $categoryPermissions = [
            ['name' => 'Create Category', 'slug' => 'create-category'],
            ['name' => 'Edit Category', 'slug' => 'edit-category'],
            ['name' => 'Delete Category', 'slug' => 'delete-category'],
            ['name' => 'View Category', 'slug' => 'view-category'],
        ];

        // Permisos de media
        $mediaPermissions = [
            ['name' => 'Upload Media', 'slug' => 'upload-media'],
            ['name' => 'Delete Media', 'slug' => 'delete-media'],
            ['name' => 'View Media', 'slug' => 'view-media'],
            ['name' => 'Edit Media', 'slug' => 'edit-media'],
        ];

        // Permisos de usuarios
        $userPermissions = [
            ['name' => 'Create User', 'slug' => 'create-user'],
            ['name' => 'Edit User', 'slug' => 'edit-user'],
            ['name' => 'Delete User', 'slug' => 'delete-user'],
            ['name' => 'View Users', 'slug' => 'view-users'],
            ['name' => 'Assign Roles', 'slug' => 'assign-roles'],
        ];

        // Permisos de menús
        $menuPermissions = [
            ['name' => 'Create Menu', 'slug' => 'create-menu'],
            ['name' => 'Edit Menu', 'slug' => 'edit-menu'],
            ['name' => 'Delete Menu', 'slug' => 'delete-menu'],
            ['name' => 'View Menu', 'slug' => 'view-menu'],
        ];

        // Permisos de settings
        $settingsPermissions = [
            ['name' => 'Edit Settings', 'slug' => 'edit-settings'],
            ['name' => 'View Settings', 'slug' => 'view-settings'],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings'],
        ];

        // Permisos de AI
        $aiPermissions = [
            ['name' => 'Use AI', 'slug' => 'use-ai'],
        ];

        // Crear todos los permisos
        $allPermissions = [
            ...$contentPermissions,
            ...$categoryPermissions,
            ...$mediaPermissions,
            ...$userPermissions,
            ...$menuPermissions,
            ...$settingsPermissions,
            ...$aiPermissions,
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['perm_cdslug' => $permission['slug']],
                [
                    'perm_nmname' => $permission['name'],
                    'perm_dsdesc' => "Permission to {$permission['name']}",
                ]
            );
        }

        // Asignar permisos a roles
        $adminRole = Role::where('role_cdslug', Role::ADMIN)->first();
        $editorRole = Role::where('role_cdslug', Role::EDITOR)->first();
        $viewerRole = Role::where('role_cdslug', Role::VIEWER)->first();

        // Admin tiene todos los permisos
        if ($adminRole) {
            $allPermissionSlugs = array_column($allPermissions, 'slug');
            $adminRole->syncPermissions($allPermissionSlugs);
        }

        // Editor tiene permisos de contenido, categorías, media y AI
        if ($editorRole) {
            $editorPermissions = [
                ...array_column($contentPermissions, 'slug'),
                ...array_column($categoryPermissions, 'slug'),
                ...array_column($mediaPermissions, 'slug'),
                'view-user',
                'use-ai',
            ];
            $editorRole->syncPermissions($editorPermissions);
        }

        // Viewer solo puede ver
        if ($viewerRole) {
            $viewerPermissions = [
                'view-content',
                'view-category',
                'view-media',
                'view-users',
                'view-menu',
                'view-settings',
            ];
            $viewerRole->syncPermissions($viewerPermissions);
        }

        $this->command->info('✅ Permissions seeded successfully');
    }
}
