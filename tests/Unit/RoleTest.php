<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_role_has_users_relationship()
    {
        $role = Role::first();
        $user = \App\Models\User::factory()->create();
        $user->roles()->attach($role);

        $this->assertTrue($role->users->contains($user));
    }

    public function test_role_has_permissions_relationship()
    {
        $role = Role::where('role_cdslug', 'author')->first();
        $permission = Permission::where('perm_cdslug', 'create-content')->first();

        $role->permissions()->attach($permission);

        $this->assertTrue($role->permissions->contains($permission));
    }

    public function test_has_permission_returns_true_for_assigned_permission()
    {
        $role = Role::where('role_cdslug', 'author')->first();
        $permission = Permission::where('perm_cdslug', 'create-content')->first();

        $role->permissions()->attach($permission);

        $this->assertTrue($role->hasPermission('create-content'));
    }

    public function test_has_permission_returns_false_for_unassigned_permission()
    {
        $role = Role::where('role_cdslug', 'viewer')->first();

        $this->assertFalse($role->hasPermission('create-content'));
    }

    public function test_give_permission_assigns_permission()
    {
        $role = Role::where('role_cdslug', 'viewer')->first();

        $role->givePermission('create-content');

        $this->assertTrue($role->hasPermission('create-content'));
    }

    public function test_give_permission_with_permission_object()
    {
        $role = Role::where('role_cdslug', 'viewer')->first();
        $permission = Permission::where('perm_cdslug', 'edit-content')->first();

        $role->givePermission($permission);

        $this->assertTrue($role->hasPermission('edit-content'));
    }

    public function test_revoke_permission_removes_permission()
    {
        $role = Role::where('role_cdslug', 'editor')->first();

        $role->revokePermission('create-content');

        $this->assertFalse($role->hasPermission('create-content'));
    }

    public function test_sync_permissions_replaces_all_permissions()
    {
        $role = Role::where('role_cdslug', 'viewer')->first();

        $role->syncPermissions(['create-content', 'edit-content']);

        $this->assertTrue($role->hasPermission('create-content'));
        $this->assertTrue($role->hasPermission('edit-content'));
        $this->assertFalse($role->hasPermission('delete-content'));
    }

    public function test_scope_by_slug_filters_by_slug()
    {
        $role = Role::bySlug('editor')->first();

        $this->assertEquals('editor', $role->role_cdslug);
    }
}
