<?php

namespace Tests\Feature\Site;

use App\Models\Content;
use App\Models\ContentTranslation;
use App\Services\LocaleManager;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Cubre el flujo de multi-idioma del sitio público (ES + EN):
 *   - LocaleManager: helpers de URL y locale
 *   - Routing /en/* con SetLocale middleware
 *   - SiteContentService devuelve translated title/body si hay traducción
 *   - SiteSeoService emite hreflang alternates correctos
 *   - Schema migration: hycms_content_translations existe
 */
class I18nTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    public function test_migration_creates_translations_table(): void
    {
        $this->assertTrue(
            \Schema::hasTable('hycms_content_translations'),
            'hycms_content_translations debe existir'
        );
        $this->assertTrue(
            \Schema::hasColumn('hycms_settings', 'sett_cdlang'),
            'sett_cdlang debe existir en hycms_settings'
        );
    }

    public function test_locale_manager_url_for_locale_handles_default_paths(): void
    {
        $locale = app(LocaleManager::class);

        $this->assertSame('/en', $locale->urlForLocale('en', '/'));
        $this->assertSame('/en/solutions/x', $locale->urlForLocale('en', '/soluciones/x'));
        $this->assertSame('/', $locale->urlForLocale('es', '/en'));
        $this->assertSame('/soluciones/x', $locale->urlForLocale('es', '/en/solutions/x'));
        $this->assertSame('/en/solutions', $locale->urlForLocale('en', '/soluciones'));
        $this->assertSame('/soluciones', $locale->urlForLocale('es', '/en/solutions'));
    }

    public function test_en_routes_resolve_with_locale_middleware(): void
    {
        $this->get('/en')->assertStatus(200);
        $this->get('/en/solutions')->assertStatus(200);
        $this->get('/en/solutions/seguro-de-vida')->assertStatus(200);
        $this->get('/en/solutions/no-existe')->assertStatus(404);
    }

    public function test_site_content_service_returns_translated_title_when_locale_is_en(): void
    {
        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        ContentTranslation::create([
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_nmtitl' => 'Life Insurance',
            'cotr_dsbody' => 'Protect your loved ones with our comprehensive life insurance.',
        ]);
        SiteContentService::flush();

        app(LocaleManager::class)->setCurrent('en');
        $result = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');

        $this->assertSame('Life Insurance', $result['title']);
        $this->assertSame('Protect your loved ones with our comprehensive life insurance.', $result['body']);
    }

    public function test_site_content_service_falls_back_to_default_when_no_translation(): void
    {
        SiteContentService::flush();
        app(LocaleManager::class)->setCurrent('en');
        $result = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');

        // Sin traducción EN → debe usar el Spanish nativo
        $this->assertSame('Seguro de Vida', $result['title']);
    }

    public function test_site_content_service_solutions_returns_localized_hrefs(): void
    {
        app(LocaleManager::class)->setCurrent('en');
        $solutions = app(SiteContentService::class)->solutions(1);

        $this->assertNotEmpty($solutions);
        $this->assertStringStartsWith('/en/solutions/', $solutions[0]['href']);
    }

    public function test_site_seo_service_for_home_emits_hreflang_alternates(): void
    {
        $seo = app(SiteSeoService::class)->forHome();

        $this->assertNotEmpty($seo['alternates']);

        $byLang = collect($seo['alternates'])->keyBy('hreflang');
        $this->assertArrayHasKey('es', $byLang);
        $this->assertArrayHasKey('en', $byLang);
        $this->assertArrayHasKey('x-default', $byLang);

        $this->assertStringEndsWith('/', $byLang['es']['href']);
        $this->assertStringEndsWith('/en', $byLang['en']['href']);
    }

    public function test_site_seo_service_for_solution_alternates_differ_per_locale(): void
    {
        SiteContentService::flush();
        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $byLang = collect($seo['alternates'])->keyBy('hreflang');
        $this->assertStringEndsWith('/soluciones/seguro-de-vida', $byLang['es']['href']);
        $this->assertStringEndsWith('/en/solutions/seguro-de-vida', $byLang['en']['href']);
    }

    public function test_en_solution_show_renders_translated_title_in_seo_prop(): void
    {
        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        ContentTranslation::create([
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_nmtitl' => 'Life Insurance',
            'cotr_dsbody' => 'Body in english',
        ]);
        SiteContentService::flush();

        $response = $this->get('/en/solutions/seguro-de-vida');
        $response->assertStatus(200);

        $html = $response->getContent();
        preg_match('/data-page="([^"]+)"/', $html, $m);
        $page = json_decode(html_entity_decode($m[1]), true);

        $this->assertSame('Life Insurance', $page['props']['solution']['title']);
        $this->assertStringContainsString('Life Insurance', $page['props']['seo']['title']);
    }

    public function test_es_solution_show_still_uses_spanish(): void
    {
        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        ContentTranslation::create([
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_nmtitl' => 'Life Insurance',
        ]);
        SiteContentService::flush();

        $response = $this->get('/soluciones/seguro-de-vida');
        $html = $response->getContent();
        preg_match('/data-page="([^"]+)"/', $html, $m);
        $page = json_decode(html_entity_decode($m[1]), true);

        $this->assertSame('Seguro de Vida', $page['props']['solution']['title']);
    }

    public function test_setting_lang_aware_get_value(): void
    {
        \App\Models\Setting::setValue('site.test.key', 'spanish', 'site');
        \App\Models\Setting::setValue('site.test.key', 'english', 'site', 'en');

        $this->assertSame('spanish', \App\Models\Setting::getValue('site.test.key'));
        $this->assertSame('english', \App\Models\Setting::getValue('site.test.key', null, 'en'));
        // Falla a default si no existe la traducción
        $this->assertSame('spanish', \App\Models\Setting::getValue('site.test.key', null, 'fr'));
    }

    public function test_admin_can_save_content_translations(): void
    {
        $admin = \App\Models\User::where('user_dsemai', 'admin@hyperion.local')->first();
        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();

        $payload = [
            'title'      => $content->cont_nmtitl,
            'slug'       => $content->cont_cdslug,
            'type'       => $content->cont_cdtype,
            'status'     => $content->cont_cdstat,
            'body'       => 'Spanish body',
            'categories' => $content->categories->pluck('cate_idcate')->all(),
            'translations' => [
                'en' => ['title' => 'Life Insurance', 'body' => 'Body in english'],
            ],
        ];

        $this->actingAs($admin)
            ->from("/admin/contents/{$content->cont_idcont}/edit")
            ->put("/admin/contents/{$content->cont_idcont}", $payload)
            ->assertRedirect('/admin/contents');

        $this->assertDatabaseHas('hycms_content_translations', [
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_nmtitl' => 'Life Insurance',
            'cotr_dsbody' => 'Body in english',
        ]);
    }

    public function test_admin_save_with_empty_translation_deletes_row(): void
    {
        $admin = \App\Models\User::where('user_dsemai', 'admin@hyperion.local')->first();
        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();

        ContentTranslation::create([
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
            'cotr_nmtitl' => 'Stale',
        ]);

        $payload = [
            'title'        => $content->cont_nmtitl,
            'slug'         => $content->cont_cdslug,
            'type'         => $content->cont_cdtype,
            'status'       => $content->cont_cdstat,
            'body'         => '',
            'categories'   => $content->categories->pluck('cate_idcate')->all(),
            'translations' => ['en' => ['title' => '', 'body' => '']],
        ];

        $this->actingAs($admin)
            ->from("/admin/contents/{$content->cont_idcont}/edit")
            ->put("/admin/contents/{$content->cont_idcont}", $payload);

        $this->assertDatabaseMissing('hycms_content_translations', [
            'cotr_idcont' => $content->cont_idcont,
            'cotr_cdlang' => 'en',
        ]);
    }
}
