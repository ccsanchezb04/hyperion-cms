<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\LocaleManager;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Cubre el nuevo tab Categories en /admin/seo: lista de categorías expuesta
 * en la prop, persistencia de OG image + description por categoría, y la
 * verificación de que el override per-locale funciona en SiteSeoService.
 */
class SeoCategoriesTabTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        Cache::flush();
    }

    public function test_index_exposes_solution_categories(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/seo')
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p
                ->has('solutionCategories', 6)
                ->where('solutionCategories.0.slug', fn ($s) => in_array($s, ['empresas','hogar','movilidad','rentas','salud','vida']))
                ->etc()
            );
    }

    public function test_categories_tab_persists_og_image_per_locale(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'categories',
                'lang' => 'en',
                'values' => [
                    'site.seo.category.vida.og_image'    => '/storage/site/seo/og-vida-en.jpg',
                    'site.seo.category.vida.description' => 'Protect your autonomy, income and family future.',
                ],
            ])
            ->assertRedirect('/admin/seo');

        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.seo.category.vida.og_image',
            'sett_cdlang' => 'en',
            'sett_nmgrou' => 'seo',
            'sett_dsvalu' => '/storage/site/seo/og-vida-en.jpg',
        ]);
        $this->assertDatabaseHas('hycms_settings', [
            'sett_cdkeys' => 'site.seo.category.vida.description',
            'sett_cdlang' => 'en',
            'sett_dsvalu' => 'Protect your autonomy, income and family future.',
        ]);
    }

    public function test_categories_tab_ignores_keys_outside_whitelist(): void
    {
        $beforeHeading = Setting::getValue('site.heading');

        $this->actingAs($this->admin)
            ->from('/admin/seo')
            ->put('/admin/seo', [
                'tab'  => 'categories',
                'lang' => 'en',
                'values' => [
                    'site.seo.category.vida.description' => 'OK',
                    'site.heading' => 'HACK',  // not in categories whitelist
                ],
            ]);

        $this->assertSame('OK', Setting::getValue('site.seo.category.vida.description', null, 'en'));
        $this->assertSame($beforeHeading, Setting::getValue('site.heading'));
    }

    public function test_en_solution_uses_localized_category_og_image(): void
    {
        Setting::setValue(
            'site.seo.category.vida.og_image',
            '/storage/site/seo/og-vida-en.jpg',
            'seo',
            'en'
        );
        SiteContentService::flush();

        app(LocaleManager::class)->setCurrent('en');
        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertStringContainsString('og-vida-en.jpg', $seo['og']['image']);
    }

    public function test_en_solution_falls_back_to_es_og_when_no_en_override(): void
    {
        SiteContentService::flush();
        app(LocaleManager::class)->setCurrent('en');

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        // El default (Spanish) sigue siendo og-vida.jpg
        $this->assertStringContainsString('og-vida.jpg', $seo['og']['image']);
    }
}
