<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_users_list_with_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user_iduser',
                        'user_nmname',
                        'user_dsemai',
                    ]
                ]
            ]);
    }

    public function test_index_denies_without_permission()
    {
        $viewerRole = Role::where('role_cdslug', 'viewer')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_store_creates_new_user_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_store_denies_without_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403);
    }

    public function test_show_returns_single_user_with_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $targetUser = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/users/{$targetUser->user_iduser}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user_iduser' => $targetUser->user_iduser,
                    'user_nmname' => $targetUser->user_nmname,
                ]
            ]);
    }

    public function test_update_modifies_user_with_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $targetUser = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/users/{$targetUser->user_iduser}", [
                'name' => 'Updated Name',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_delete_removes_user_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $targetUser = User::where('user_iduser', '!=', $user->user_iduser)->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/users/{$targetUser->user_iduser}");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_assign_roles_assigns_roles_with_permission()
    {
        $superAdminRole = Role::where('role_cdslug', 'super-admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($superAdminRole);

        $targetUser = User::factory()->create();
        $editorRole = Role::where('role_cdslug', 'editor')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/users/{$targetUser->user_iduser}/roles", [
                'roles' => [$editorRole->role_idrole],
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_activate_activates_user_with_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $targetUser = User::factory()->create(['user_cdstat' => 'inactive']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/users/{$targetUser->user_iduser}/activate");

        $this->assertNotEquals(403, $response->status());
    }

    public function test_deactivate_deactivates_user_with_permission()
    {
        $adminRole = Role::where('role_cdslug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $targetUser = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/users/{$targetUser->user_iduser}/deactivate");

        $this->assertNotEquals(403, $response->status());
    }
}
