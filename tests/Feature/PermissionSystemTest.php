<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_has_all_permissions()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        Auth::login($user);

        $this->assertTrue($user->hasPermission('create-content'));
        $this->assertTrue($user->hasPermission('delete-user'));
        $this->assertTrue($user->hasPermission('manage-settings'));
    }

    public function test_regular_user_without_permission_is_denied()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        Auth::login($user);

        $this->assertFalse($user->hasPermission('create-content'));
        $this->assertFalse($user->hasPermission('delete-user'));
    }

    public function test_role_can_be_assigned_permissions()
    {
        $role = Role::where('role_cdslug', 'author')->first();

        $role->givePermission('create-content');

        $this->assertTrue($role->hasPermission('create-content'));
    }

    public function test_user_can_check_multiple_permissions()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        Auth::login($user);

        $this->assertTrue($user->hasAnyPermission(['create-content', 'delete-user']));
        $this->assertFalse($user->hasAllPermissions(['create-content', 'delete-user']));
    }

    public function test_permission_middleware_denies_unauthorized_access()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/contents', [
                'title' => 'Test Content',
                'slug' => 'test-content',
                'content' => 'Test content body',
                'status' => 'draft'
            ]);

        $response->assertStatus(403);
    }

    public function test_permission_middleware_allows_authorized_access()
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $user = User::factory()->create();
        $user->roles()->attach($editorRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/contents', [
                'title' => 'Test Content',
                'slug' => 'test-content',
                'content' => 'Test content body',
                'status' => 'draft'
            ]);

        // The permission middleware should not deny access (not 403)
        // The actual response may be different due to other validation issues
        $this->assertNotEquals(403, $response->status());
    }
}
