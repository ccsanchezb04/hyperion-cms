<?php

namespace Tests\Feature\Site;

use App\Models\Content;
use App\Models\ContentTranslation;
use App\Models\User;
use App\Services\LocaleManager;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use App\Services\SiteSitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Cubre per-language slugs:
 *   - Schema: cotr_cdslug + unique compuesto
 *   - Lookup locale-aware en SiteContentService
 *   - Canonical + alternates con slug del locale activo
 *   - Sitemap incluye URLs por idioma con slugs traducidos
 *   - Admin: persistencia + validación
 */
class PerLanguageSlugsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Content $solution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->solution = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        Cache::flush();
    }

    public function test_schema_has_cotr_cdslug_column(): void
    {
        $this->assertTrue(
            \Schema::hasColumn('hycms_content_translations', 'cotr_cdslug'),
            'cotr_cdslug debe existir en hycms_content_translations'
        );
    }

    public function test_en_route_resolves_by_translated_slug(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
            'cotr_nmtitl' => 'Life Insurance',
        ]);
        SiteContentService::flush();

        $this->get('/en/solutions/life-insurance')->assertStatus(200);
    }

    public function test_en_route_falls_back_to_es_slug_when_no_translation_slug(): void
    {
        // Sin cotr_cdslug → /en/solutions/seguro-de-vida sigue funcionando
        $this->get('/en/solutions/seguro-de-vida')->assertStatus(200);
    }

    public function test_es_url_with_en_slug_returns_404(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
        ]);
        SiteContentService::flush();

        // El slug EN no debe resolver en la ruta ES
        $this->get('/soluciones/life-insurance')->assertStatus(404);
    }

    public function test_solution_payload_includes_slugs_map(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $this->assertSame('seguro-de-vida', $solution['slugs']['es']);
        $this->assertSame('life-insurance', $solution['slugs']['en']);
    }

    public function test_alternates_use_translated_slug_per_locale(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $byLang = collect($seo['alternates'])->keyBy('hreflang');
        $this->assertStringEndsWith('/soluciones/seguro-de-vida', $byLang['es']['href']);
        $this->assertStringEndsWith('/en/solutions/life-insurance', $byLang['en']['href']);
    }

    public function test_canonical_uses_current_locale_slug(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
        ]);
        SiteContentService::flush();

        app(LocaleManager::class)->setCurrent('en');
        $solution = app(SiteContentService::class)->solutionBySlug('life-insurance');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertStringEndsWith('/en/solutions/life-insurance', $seo['canonical']);
    }

    public function test_sitemap_includes_en_urls_with_translated_slugs(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
        ]);
        SiteSitemapService::flush();

        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('/soluciones/seguro-de-vida', $body);
        $this->assertStringContainsString('/en/solutions/life-insurance', $body);
        // Home + index por idioma
        $this->assertStringContainsString('<loc>https://juanferseguros.com/en</loc>', $body);
        $this->assertStringContainsString('<loc>https://juanferseguros.com/en/solutions</loc>', $body);
    }

    public function test_admin_saves_translation_slug(): void
    {
        $this->actingAs($this->admin)
            ->from("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->put("/admin/contents/{$this->solution->cont_idcont}", [
                'title'      => $this->solution->cont_nmtitl,
                'slug'       => $this->solution->cont_cdslug,
                'type'       => $this->solution->cont_cdtype,
                'status'     => $this->solution->cont_cdstat,
                'body'       => 'Body es',
                'categories' => $this->solution->categories->pluck('cate_idcate')->all(),
                'translations' => [
                    'en' => [
                        'title' => 'Life Insurance',
                        'body'  => 'Protect your family',
                        'slug'  => 'life-insurance',
                    ],
                ],
            ])
            ->assertRedirect('/admin/contents');

        $this->assertDatabaseHas('hycms_content_translations', [
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
            'cotr_nmtitl' => 'Life Insurance',
        ]);
    }

    public function test_invalid_slug_pattern_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->put("/admin/contents/{$this->solution->cont_idcont}", [
                'title'      => $this->solution->cont_nmtitl,
                'slug'       => $this->solution->cont_cdslug,
                'type'       => $this->solution->cont_cdtype,
                'status'     => $this->solution->cont_cdstat,
                'body'       => 'x',
                'categories' => $this->solution->categories->pluck('cate_idcate')->all(),
                'translations' => [
                    'en' => ['slug' => 'Life Insurance!'],  // espacios + caps + !
                ],
            ])
            ->assertSessionHasErrors('translations.en.slug');
    }

    public function test_edit_form_serializes_translation_slug(): void
    {
        ContentTranslation::create([
            'cotr_idcont' => $this->solution->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_cdslug' => 'life-insurance',
            'cotr_nmtitl' => 'Life Insurance',
        ]);

        $this->actingAs($this->admin)
            ->get("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->assertInertia(fn ($p) => $p
                ->where('content.translations.en.slug', 'life-insurance')
                ->where('content.translations.en.title', 'Life Insurance')
                ->etc()
            );
    }
}
