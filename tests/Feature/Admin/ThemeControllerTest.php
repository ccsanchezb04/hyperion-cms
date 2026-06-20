<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_requires_authentication(): void
    {
        $this->get('/admin/themes')->assertRedirect('/admin/login');
    }

    public function test_index_requires_manage_settings_permission(): void
    {
        $viewer = User::where('user_dsemai', 'viewer@hyperion.local')->first();
        $this->actingAs($viewer)->get('/admin/themes')->assertStatus(403);
    }

    public function test_admin_can_list_themes(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();

        $this->actingAs($admin)->get('/admin/themes')->assertStatus(200);
    }

    public function test_admin_can_activate_theme(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();

        $response = $this->actingAs($admin)
            ->from('/admin/themes')
            ->post('/admin/themes/activate', ['slug' => 'default']);

        $response->assertRedirect('/admin/themes');
        $response->assertSessionHas('status');

        $this->assertSame('default', Setting::getValue(ThemeManager::SETTING_KEY));
    }

    public function test_activate_rejects_unknown_slug(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();

        $response = $this->actingAs($admin)
            ->from('/admin/themes')
            ->post('/admin/themes/activate', ['slug' => 'ghost-theme']);

        $response->assertRedirect('/admin/themes');
        $response->assertSessionHasErrors('slug');
        $this->assertNull(Setting::getValue(ThemeManager::SETTING_KEY));
    }
}
