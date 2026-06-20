<?php

namespace Tests\Feature;

use App\Services\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeFaviconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_juanfer_theme_declares_favicon_links(): void
    {
        $links = app(ThemeManager::class)->faviconLinks('juanfer');

        $this->assertNotEmpty($links);

        $rels = array_column($links, 'rel');
        $this->assertContains('icon', $rels);
        $this->assertContains('apple-touch-icon', $rels);

        $hrefs = array_column($links, 'href');
        foreach ($hrefs as $href) {
            $this->assertStringContainsString('themes/juanfer/favicons/', $href);
        }
    }

    public function test_default_theme_falls_back_to_global_favicon(): void
    {
        $links = app(ThemeManager::class)->faviconLinks('default');

        $this->assertNotEmpty($links);

        $hrefs = array_column($links, 'href');
        foreach ($hrefs as $href) {
            $this->assertStringNotContainsString('themes/default/', $href);
        }
        // Debe apuntar al favicon global del CMS
        $this->assertStringContainsString('favicon.ico', $hrefs[0]);
    }

    public function test_active_theme_drives_favicon_links_in_site_blade(): void
    {
        app(ThemeManager::class)->setActive('juanfer');
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertStringContainsString('themes/juanfer/favicons/favicon.ico', $response->getContent());

        app(ThemeManager::class)->setActive('default');
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertStringNotContainsString('themes/juanfer/favicons/', $response->getContent());
        $this->assertStringContainsString('favicon.ico', $response->getContent());
    }
}
