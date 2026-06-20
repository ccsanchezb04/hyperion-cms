<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
    }

    public function test_index_requires_manage_roles_permission(): void
    {
        $viewer = User::factory()->create();
        $this->actingAs($viewer)->get('/admin/roles')->assertStatus(403);
    }

    public function test_admin_can_list_roles(): void
    {
        $this->actingAs($this->admin)->get('/admin/roles')->assertStatus(200);
    }

    public function test_admin_can_create_role_with_permissions(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/roles/create')
            ->post('/admin/roles', [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'permissions' => ['view-content', 'edit-content'],
            ]);

        $response->assertRedirect('/admin/roles');
        $role = Role::where('role_cdslug', 'marketing')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermission('view-content'));
        $this->assertTrue($role->hasPermission('edit-content'));
    }

    public function test_role_slug_rejects_invalid_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/roles/create')
            ->post('/admin/roles', [
                'name' => 'Bad',
                'slug' => 'Bad Slug!',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_role_slug_must_be_unique(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/roles/create')
            ->post('/admin/roles', [
                'name' => 'Dup',
                'slug' => 'editor',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::create(['role_nmname' => 'Test', 'role_cdslug' => 'test-role']);
        $role->givePermission('view-content');

        $this->actingAs($this->admin)
            ->from("/admin/roles/{$role->role_idrole}/edit")
            ->put("/admin/roles/{$role->role_idrole}", [
                'name' => 'Test Renamed',
                'slug' => 'test-role',
                'permissions' => ['view-content', 'edit-content', 'delete-content'],
            ])
            ->assertRedirect('/admin/roles');

        $role->refresh();
        $this->assertSame('Test Renamed', $role->role_nmname);
        $this->assertTrue($role->hasPermission('edit-content'));
        $this->assertTrue($role->hasPermission('delete-content'));
    }

    public function test_protected_role_cannot_be_deleted(): void
    {
        $admin = Role::where('role_cdslug', 'admin')->first();

        $response = $this->actingAs($this->admin)
            ->from('/admin/roles')
            ->delete("/admin/roles/{$admin->role_idrole}");

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('hycms_roles', ['role_idrole' => $admin->role_idrole]);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $role = Role::create(['role_nmname' => 'Pop', 'role_cdslug' => 'pop']);
        $user = User::factory()->create();
        $user->roles()->attach($role->role_idrole);

        $response = $this->actingAs($this->admin)
            ->from('/admin/roles')
            ->delete("/admin/roles/{$role->role_idrole}");

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('hycms_roles', ['role_idrole' => $role->role_idrole]);
    }

    public function test_role_without_users_can_be_deleted(): void
    {
        $role = Role::create(['role_nmname' => 'Temp', 'role_cdslug' => 'temp-role']);

        $this->actingAs($this->admin)
            ->from('/admin/roles')
            ->delete("/admin/roles/{$role->role_idrole}")
            ->assertRedirect('/admin/roles');

        $this->assertDatabaseMissing('hycms_roles', ['role_idrole' => $role->role_idrole]);
    }
}
