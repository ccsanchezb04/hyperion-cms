<?php

namespace Tests\Feature\Admin;

use App\Models\Content;
use App\Models\ContentSeo;
use App\Models\User;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Cubre el flujo admin de overrides SEO per-content (tabla hycms_content_seo)
 * y verifica que SiteSeoService los respete frente a los defaults globales.
 */
class ContentSeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Content $solution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->solution = Content::published()
            ->where('cont_cdslug', 'seguro-de-vida')
            ->first();
        Cache::flush();
    }

    public function test_edit_form_loads_seo_relation(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contents/Edit')
                ->has('content.seo')
                ->where('content.seo.meta_title', '')
                ->where('content.seo.noindex', false)
                ->has('canonicalHost')
                ->etc()
            );
    }

    public function test_update_persists_seo_overrides(): void
    {
        $this->actingAs($this->admin)
            ->from("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->put("/admin/contents/{$this->solution->cont_idcont}", $this->basePayload([
                'seo' => [
                    'meta_title'       => 'Vida Premium | JF',
                    'meta_description' => 'Override description',
                    'og_image'         => '/storage/site/seo/og-custom.jpg',
                    'canonical'        => 'https://juanferseguros.com/custom',
                    'noindex'          => true,
                ],
            ]))
            ->assertRedirect('/admin/contents');

        $this->assertDatabaseHas('hycms_content_seo', [
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_nmtitl' => 'Vida Premium | JF',
            'cose_dsdesc' => 'Override description',
            'cose_dsogim' => '/storage/site/seo/og-custom.jpg',
            'cose_cdcano' => 'https://juanferseguros.com/custom',
            'cose_bonoix' => true,
        ]);
    }

    public function test_update_with_empty_seo_deletes_existing_row(): void
    {
        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_nmtitl' => 'Existing',
        ]);

        $this->actingAs($this->admin)
            ->from("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->put("/admin/contents/{$this->solution->cont_idcont}", $this->basePayload([
                'seo' => [
                    'meta_title'       => '',
                    'meta_description' => '',
                    'og_image'         => '',
                    'canonical'        => '',
                    'noindex'          => false,
                ],
            ]));

        $this->assertDatabaseMissing('hycms_content_seo', [
            'cose_idcont' => $this->solution->cont_idcont,
        ]);
    }

    public function test_invalid_canonical_url_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from("/admin/contents/{$this->solution->cont_idcont}/edit")
            ->put("/admin/contents/{$this->solution->cont_idcont}", $this->basePayload([
                'seo' => ['canonical' => 'not-a-url'],
            ]))
            ->assertSessionHasErrors('seo.canonical');
    }

    public function test_site_seo_service_uses_meta_description_override_over_category_default(): void
    {
        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_dsdesc' => 'Custom override description',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertSame('Custom override description', $seo['description']);
        // Category default ("Protegemos tu autonomía...") NO debe aparecer
        $this->assertStringNotContainsString('Protegemos tu autonomía', $seo['description']);
    }

    public function test_site_seo_service_uses_meta_title_override(): void
    {
        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_nmtitl' => 'Mi título custom',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertSame('Mi título custom', $seo['title']);
    }

    public function test_site_seo_service_uses_og_image_override(): void
    {
        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_dsogim' => '/storage/site/seo/og-vida-special.jpg',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertStringContainsString('og-vida-special.jpg', $seo['og']['image']);
    }

    public function test_site_seo_service_uses_canonical_override(): void
    {
        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_cdcano' => 'https://otra-url.com/landing',
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertSame('https://otra-url.com/landing', $seo['canonical']);
    }

    public function test_noindex_per_page_overrides_global_robots_policy(): void
    {
        // Global está en noindex; lo cambiamos a index para asegurar que el
        // override per-page funciona en el sentido contrario también
        \App\Models\Setting::setValue('site.seo.robots', 'index,follow', 'seo');

        ContentSeo::create([
            'cose_idcont' => $this->solution->cont_idcont,
            'cose_bonoix' => true,
        ]);
        SiteContentService::flush();

        $solution = app(SiteContentService::class)->solutionBySlug('seguro-de-vida');
        $seo = app(SiteSeoService::class)->forSolution($solution);

        $this->assertSame('noindex,nofollow', $seo['robots']);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'title'      => $this->solution->cont_nmtitl,
            'slug'       => $this->solution->cont_cdslug,
            'type'       => $this->solution->cont_cdtype,
            'status'     => $this->solution->cont_cdstat,
            'body'       => 'Test body',
            'categories' => $this->solution->categories->pluck('cate_idcate')->all(),
        ], $overrides);
    }
}
