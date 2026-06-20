<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_requires_view_permissions(): void
    {
        $viewer = User::factory()->create();
        $this->actingAs($viewer)->get('/admin/permissions')->assertStatus(403);
    }

    public function test_admin_can_list_permissions(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->actingAs($admin)->get('/admin/permissions')->assertStatus(200);
    }
}
