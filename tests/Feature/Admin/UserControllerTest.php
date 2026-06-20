<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
    }

    public function test_index_requires_view_users_permission(): void
    {
        $viewer = User::factory()->create();
        $this->actingAs($viewer)->get('/admin/users')->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $editorRole = Role::where('role_cdslug', 'editor')->first();

        $response = $this->actingAs($this->admin)
            ->from('/admin/users/create')
            ->post('/admin/users', [
                'name' => 'Nuevo',
                'email' => 'nuevo@example.com',
                'password' => 'password123',
                'status' => 'active',
                'roles' => ['editor'],
            ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('hycms_users', ['user_dsemai' => 'nuevo@example.com']);

        $user = User::where('user_dsemai', 'nuevo@example.com')->first();
        $this->assertTrue($user->roles->contains('role_idrole', $editorRole->role_idrole));
    }

    public function test_admin_cannot_create_user_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/users/create')
            ->post('/admin/users', [
                'name' => 'Dup',
                'email' => 'admin@hyperion.local',
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_user_without_password(): void
    {
        $user = User::factory()->create(['user_nmname' => 'Original']);
        $originalHash = $user->user_cdpass;

        $response = $this->actingAs($this->admin)
            ->from("/admin/users/{$user->user_iduser}/edit")
            ->put("/admin/users/{$user->user_iduser}", [
                'name' => 'Actualizado',
                'email' => $user->user_dsemai,
                'status' => 'active',
            ]);

        $response->assertRedirect('/admin/users');
        $this->assertSame('Actualizado', $user->fresh()->user_nmname);
        $this->assertSame($originalHash, $user->fresh()->user_cdpass);
    }

    public function test_admin_can_update_user_with_password_change(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->user_cdpass;

        $this->actingAs($this->admin)
            ->from("/admin/users/{$user->user_iduser}/edit")
            ->put("/admin/users/{$user->user_iduser}", [
                'name' => $user->user_nmname,
                'email' => $user->user_dsemai,
                'password' => 'newpassword123',
            ]);

        $this->assertNotSame($originalHash, $user->fresh()->user_cdpass);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$this->admin->user_iduser}");

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('hycms_users', ['user_iduser' => $this->admin->user_iduser, 'user_dtdele' => null]);
    }

    public function test_admin_can_soft_delete_other_user(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$other->user_iduser}")
            ->assertRedirect('/admin/users');

        $this->assertSoftDeleted('hycms_users', ['user_iduser' => $other->user_iduser]);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/users')
            ->post("/admin/users/{$this->admin->user_iduser}/deactivate");

        $response->assertSessionHasErrors('user');
        $this->assertSame('active', $this->admin->fresh()->user_cdstat);
    }

    public function test_admin_can_activate_and_deactivate_other_user(): void
    {
        $other = User::factory()->create(['user_cdstat' => User::STATUS_INACTIVE]);

        $this->actingAs($this->admin)
            ->from('/admin/users')
            ->post("/admin/users/{$other->user_iduser}/activate")
            ->assertRedirect();

        $this->assertSame('active', $other->fresh()->user_cdstat);

        $this->actingAs($this->admin)
            ->from('/admin/users')
            ->post("/admin/users/{$other->user_iduser}/deactivate")
            ->assertRedirect();

        $this->assertSame('inactive', $other->fresh()->user_cdstat);
    }
}
