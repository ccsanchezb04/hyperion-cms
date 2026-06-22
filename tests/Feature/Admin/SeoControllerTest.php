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

    public function test_index_exposes_i18n_state_with_default_locale(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/seo')
            ->assertInertia(fn (Assert $page) => $page
                ->where('i18n.active', 'es')
                ->where('i18n.default', 'es')
                ->where('i18n.supported.0', 'es')
                ->where('i18n.supported.1', 'en')
                ->etc()
            );
    }

    public function test_index_with_lang_query_activates_that_locale(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/seo?lang=en')
            ->assertInertia(fn (Assert $page) => $page
                ->where('i18n.active', 'en')
                ->etc()
            );
    }

    public function test_index_with_unknown_lang_falls_back_to_default(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/seo?lang=fr')
            ->assertInertia(fn (Assert $page) => $page
                ->where('i18n.active', 'es')
                ->etc()
            );
    }

    public function test_index_with_en_lang_shows_translated_settings_when_available(): void
    {
        Setting::setValue('site.seo.description', 'ES desc', 'seo');
        Setting::setValue('site.seo.description', 'EN desc', 'seo', 'en');

        // Las claves de settings contienen puntos (site.seo.description), así
        // que AssertableInertia con dot-paths no las matchea. Inspeccionamos
        // el array de la prop directamente.
        $resp = $this->actingAs($this->admin)->get('/admin/seo?lang=en');
        $resp->assertStatus(200);
        $page = $this->extractInertiaPage($resp->getContent());
        $this->assertSame('EN desc', $page['props']['settings']['seo']['site.seo.description']);

        $resp = $this->actingAs($this->admin)->get('/admin/seo');
        $page = $this->extractInertiaPage($resp->getContent());
        $this->assertSame('ES desc', $page['props']['settings']['seo']['site.seo.description']);
    }

    /**
     * Parsea el data-page de Inertia desde el HTML de respuesta.
     *
     * @return array<string, mixed>
     */
    protected function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="([^"]+)"/', $html, $m);
        return json_decode(html_entity_decode($m[1]), true);
    }

    public function test_update_with_lang_persists_translation_row(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'general',
                'lang' => 'en',
                'values' => ['site.seo.description' => 'Family, asset and business protection.'],
            ]);

        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.seo.description',
            'sett_cdlang' => 'en',
            'sett_dsvalu' => 'Family, asset and business protection.',
        ]);

        // El default ES no se tocó
        $defaultRow = Setting::where('sett_cdkeys', 'site.seo.description')
            ->whereNull('sett_cdlang')
            ->first();
        $this->assertNotNull($defaultRow);
        $this->assertNotSame('Family, asset and business protection.', $defaultRow->sett_dsvalu);
    }

    public function test_update_without_lang_persists_to_default(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'general',
                'lang' => 'es',  // explicit default
                'values' => ['site.seo.description' => 'Spanish updated'],
            ]);

        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.seo.description',
            'sett_cdlang' => null,
            'sett_dsvalu' => 'Spanish updated',
        ]);
    }

    public function test_chrome_tab_persists_site_group_settings(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'chrome',
                'lang' => 'en',
                'values' => [
                    'site.heading'           => 'JuanFer Insurance',
                    'site.solutions.heading' => 'Our Solutions',
                    'site.footer.copy'       => '© 2025 JuanFer Insurance. All rights reserved.',
                ],
            ]);

        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.heading',
            'sett_cdlang' => 'en',
            'sett_nmgrou' => 'site',
            'sett_dsvalu' => 'JuanFer Insurance',
        ]);
        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.solutions.heading',
            'sett_cdlang' => 'en',
            'sett_dsvalu' => 'Our Solutions',
        ]);
    }

    public function test_chrome_tab_ignores_non_site_keys(): void
    {
        $beforeRobots = Setting::getValue('site.seo.robots');

        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'chrome',
                'lang' => 'en',
                'values' => [
                    'site.heading'    => 'OK',
                    'site.seo.robots' => 'index,follow',  // not in chrome whitelist
                ],
            ]);

        $this->assertSame('OK', Setting::getValue('site.heading', null, 'en'));
        $this->assertSame($beforeRobots, Setting::getValue('site.seo.robots'));
    }

    public function test_invalid_lang_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab' => 'general',
                'lang' => 'pwn',
                'values' => ['site.seo.description' => 'x'],
            ])
            ->assertSessionHasErrors('lang');
    }
}
