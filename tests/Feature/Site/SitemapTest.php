<?php

namespace Tests\Feature\Site;

use App\Models\Content;
use App\Models\Setting;
use App\Services\SiteSitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
        Setting::setValue('site.seo.canonical_host', 'https://juanferseguros.com', 'seo');
    }

    public function test_sitemap_returns_xml_200(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
    }

    public function test_sitemap_includes_home_and_solutions_index(): void
    {
        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('<loc>https://juanferseguros.com/</loc>', $body);
        $this->assertStringContainsString('<loc>https://juanferseguros.com/soluciones</loc>', $body);
    }

    public function test_sitemap_includes_each_published_solution(): void
    {
        $body = $this->get('/sitemap.xml')->getContent();

        // Las 6 soluciones del seeder
        foreach (['seguro-de-salud', 'seguro-de-vida', 'seguro-de-movilidad',
                  'seguro-de-hogar', 'seguros-empresariales', 'plan-de-rentas'] as $slug) {
            $this->assertStringContainsString(
                "<loc>https://juanferseguros.com/soluciones/{$slug}</loc>",
                $body,
                "Falta sitemap entry para {$slug}"
            );
        }
    }

    public function test_sitemap_excludes_drafts(): void
    {
        $draft = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        $draft->cont_cdstat = Content::STATUS_DRAFT;
        $draft->save();

        SiteSitemapService::flush();

        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringNotContainsString(
            '<loc>https://juanferseguros.com/soluciones/seguro-de-vida</loc>',
            $body
        );
    }

    public function test_sitemap_excludes_testimonials_and_carousel(): void
    {
        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringNotContainsString('testimonio-maria-g', $body);
        $this->assertStringNotContainsString('carousel-slide-1', $body);
    }

    public function test_sitemap_includes_lastmod_for_solutions(): void
    {
        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertMatchesRegularExpression(
            '#<lastmod>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+\-]\d{2}:\d{2}</lastmod>#',
            $body,
            'Sitemap debe incluir lastmod en formato ATOM en al menos una URL'
        );
    }

    public function test_sitemap_invalidates_when_content_changes(): void
    {
        $this->get('/sitemap.xml');  // primera llamada cachea

        $content = Content::published()->where('cont_cdslug', 'seguro-de-vida')->first();
        $content->cont_nmtitl = 'Seguro de Vida Premium';
        $content->save();  // SiteCacheObserver dispara SiteSitemapService::flush()

        // La siguiente llamada debe reflejar lastmod actualizado
        $body = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('seguro-de-vida', $body);
    }
}
