<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\SiteSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->viewer = User::where('user_dsemai', 'viewer@hyperion.local')->first();
    }

    public function test_index_requires_authentication(): void
    {
        $this->get('/admin/seo')->assertRedirect('/admin/login');
    }

    public function test_index_requires_manage_seo_permission(): void
    {
        $this->actingAs($this->viewer)->get('/admin/seo')->assertStatus(403);
    }

    public function test_admin_sees_seo_index_with_grouped_settings(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/seo')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Seo/Index')
                ->has('settings.seo')
                ->has('settings.organization')
                ->has('settings.integrations')
                ->has('meta.sitemap_url_count')
                ->has('meta.sitemap_xml')
                ->has('meta.robots_txt')
            );
    }

    public function test_general_tab_persists_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'general',
                'values' => [
                    'site.seo.title_template'    => '{page} • Custom',
                    'site.seo.description'       => 'Nueva description',
                    'site.seo.canonical_host'    => 'https://otro.com',
                    'site.seo.robots'            => 'index,follow',
                    'site.seo.locale'            => 'es_ES',
                    'site.seo.schema_type'       => 'FinancialService',
                ],
            ]);

        $response->assertRedirect('/admin/seo');
        $this->assertSame('{page} • Custom', Setting::getValue('site.seo.title_template'));
        $this->assertSame('https://otro.com', Setting::getValue('site.seo.canonical_host'));
        $this->assertSame('index,follow', Setting::getValue('site.seo.robots'));
    }

    public function test_invalid_tab_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'pwn',
                'values' => ['site.seo.description' => 'x'],
            ]);

        $response->assertSessionHasErrors('tab');
    }

    public function test_general_tab_ignores_keys_outside_whitelist(): void
    {
        $before = Setting::getValue('site.organization.name');

        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'general',
                'values' => [
                    'site.seo.description'   => 'OK',
                    'site.organization.name' => 'HACK',
                ],
            ]);

        $this->assertSame('OK', Setting::getValue('site.seo.description'));
        $this->assertSame($before, Setting::getValue('site.organization.name'));
    }

    public function test_schema_tab_persists_organization_data(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'schema',
                'values' => [
                    'site.organization.name'           => 'New Co',
                    'site.organization.legal_code'     => '123-4',
                    'site.organization.phone'          => '3000000000',
                    'site.organization.address.street' => 'Calle X #1-2',
                ],
            ]);

        $this->assertSame('New Co', Setting::getValue('site.organization.name'));
        $this->assertSame('123-4', Setting::getValue('site.organization.legal_code'));
        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.organization.name',
            'sett_nmgrou' => 'organization',
        ]);
    }

    public function test_integrations_tab_persists_ids(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'integrations',
                'values' => [
                    'site.integrations.gtm_id'             => 'GTM-ABC123',
                    'site.integrations.ga4_measurement_id' => 'G-XYZ',
                ],
            ]);

        $this->assertSame('GTM-ABC123', Setting::getValue('site.integrations.gtm_id'));
        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.integrations.gtm_id',
            'sett_nmgrou' => 'integrations',
        ]);
    }

    public function test_robots_tab_persists_policy(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'robots',
                'values' => ['site.seo.robots' => 'index,follow'],
            ]);

        $this->assertSame('index,follow', Setting::getValue('site.seo.robots'));
    }

    public function test_save_invalidates_site_cache(): void
    {
        Cache::flush();
        $service = app(SiteSeoService::class);
        $service->forHome();  // popula cache

        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'general',
                'values' => ['site.seo.description' => 'Brand new'],
            ]);

        $seo = $service->forHome();
        $this->assertSame('Brand new', $seo['description']);
    }

    public function test_admin_can_upload_og_image(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Requires PHP GD extension to generate fake images');
        }

        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->post('/admin/seo/og-image', [
                'image' => UploadedFile::fake()->image('og.jpg', 1200, 630),
            ]);

        $response->assertRedirect('/admin/seo');
        Storage::disk('public')->assertExists('site/seo/og-default.jpg');
        $this->assertSame('/storage/site/seo/og-default.jpg', Setting::getValue('site.seo.og_image'));
    }

    public function test_og_upload_rejects_non_image(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->post('/admin/seo/og-image', [
                'image' => UploadedFile::fake()->create('script.php', 100, 'application/php'),
            ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_admin_can_flush_sitemap_cache(): void
    {
        Cache::flush();
        Cache::put('hyperion:seo:sitemap', '<stale/>', 3600);

        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->post('/admin/seo/sitemap/flush')
            ->assertRedirect('/admin/seo');

        $this->assertNull(Cache::get('hyperion:seo:sitemap'));
    }
}
