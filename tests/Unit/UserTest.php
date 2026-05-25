<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_has_roles_relationship()
    {
        $user = User::factory()->create();
        $role = Role::first();

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
    }

    public function test_has_role_returns_true_for_assigned_role()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_has_role_returns_false_for_unassigned_role()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'viewer')->first();
        $user->roles()->attach($role);

        $this->assertFalse($user->hasRole('editor'));
    }

    public function test_is_admin_returns_true_for_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'super-admin')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_active_returns_true_for_active_user()
    {
        $user = User::factory()->create(['user_cdstat' => 'active']);

        $this->assertTrue($user->isActive());
    }

    public function test_is_active_returns_false_for_inactive_user()
    {
        $user = User::factory()->create(['user_cdstat' => 'inactive']);

        $this->assertFalse($user->isActive());
    }

    public function test_has_permission_returns_true_for_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'super-admin')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('any-permission'));
    }

    public function test_has_permission_returns_true_for_role_permission()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('create-content'));
    }

    public function test_has_permission_returns_false_for_unauthorized_user()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'viewer')->first();
        $user->roles()->attach($role);

        $this->assertFalse($user->hasPermission('create-content'));
    }

    public function test_has_any_permission_returns_true_if_one_matches()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasAnyPermission(['create-content', 'delete-user']));
    }

    public function test_has_all_permissions_returns_true_if_all_match()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertTrue($user->hasAllPermissions(['create-content', 'edit-content']));
    }

    public function test_has_all_permissions_returns_false_if_one_missing()
    {
        $user = User::factory()->create();
        $role = Role::where('role_cdslug', 'editor')->first();
        $user->roles()->attach($role);

        $this->assertFalse($user->hasAllPermissions(['create-content', 'delete-user']));
    }
}
