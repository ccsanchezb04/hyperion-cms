<?php

namespace Tests\Feature\Site;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_passes_seo_prop_with_title_and_description(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home', false)
                ->has('seo', fn (Assert $seo) => $seo
                    ->where('title', 'Inicio | JuanFer Seguros')
                    ->where('description', fn ($d) => str_contains($d, 'Protección familiar'))
                    ->where('locale', 'es_CO')
                    ->where('robots', 'noindex,nofollow')
                    ->where('canonical', 'https://juanferseguros.com/')
                    ->has('og.image')
                    ->has('og.type')
                    ->has('json_ld')
                    ->etc()
                )
            );
    }

    public function test_home_emits_organization_jsonld(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->has('seo.json_ld', 1)
                ->where('seo.json_ld.0.@type', ['InsuranceAgency', 'LocalBusiness'])
                ->where('seo.json_ld.0.name', 'JUANFER SEGUROS LTDA.')
                ->where('seo.json_ld.0.taxID', '902025311-6')
                ->where('seo.json_ld.0.address.addressLocality', 'Envigado')
                ->where('seo.json_ld.0.address.addressCountry', 'CO')
                ->where('seo.json_ld.0.sameAs.0', 'https://www.facebook.com/juanfersegurossura')
                ->etc()
            );
    }

    public function test_solutions_index_includes_breadcrumb_jsonld(): void
    {
        $this->get('/soluciones')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Solutions/Index', false)
                ->has('seo.json_ld', 1)
                ->where('seo.json_ld.0.@type', 'BreadcrumbList')
                ->where('seo.json_ld.0.itemListElement.0.name', 'Inicio')
                ->where('seo.json_ld.0.itemListElement.1.name', 'Soluciones')
                ->etc()
            );
    }

    public function test_solution_show_uses_category_description_as_meta(): void
    {
        $this->get('/soluciones/seguro-de-vida')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Solutions/Show', false)
                ->where('solution.category', 'vida')
                ->where('seo.description', 'Protegemos tu autonomía, tus ingresos y el futuro de tu familia.')
                ->where('seo.title', 'Seguro de Vida | JuanFer Seguros')
                ->etc()
            );
    }

    public function test_solution_show_emits_service_and_breadcrumb_jsonld(): void
    {
        $this->get('/soluciones/seguro-de-salud')
            ->assertInertia(fn (Assert $page) => $page
                ->has('seo.json_ld', 2)
                ->where('seo.json_ld.0.@type', 'Service')
                ->where('seo.json_ld.0.name', 'Seguro de Salud')
                ->where('seo.json_ld.0.provider.name', 'JUANFER SEGUROS LTDA.')
                ->where('seo.json_ld.1.@type', 'BreadcrumbList')
                ->where('seo.json_ld.1.itemListElement.2.name', 'Seguro de Salud')
                ->etc()
            );
    }

    public function test_robots_setting_change_propagates_to_seo_prop(): void
    {
        Setting::setValue('site.seo.robots', 'index,follow', 'seo');

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('seo.robots', 'index,follow')
                ->etc()
            );
    }

    public function test_robots_txt_endpoint_respects_setting(): void
    {
        Setting::setValue('site.seo.robots', 'noindex,nofollow', 'seo');
        Setting::setValue('site.seo.canonical_host', 'https://juanferseguros.com', 'seo');

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $body = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Disallow: /', $body);
        $this->assertStringContainsString('Sitemap: https://juanferseguros.com/sitemap.xml', $body);
    }

    public function test_robots_txt_with_index_policy_only_blocks_admin_and_api(): void
    {
        Setting::setValue('site.seo.robots', 'index,follow', 'seo');

        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('Disallow: /admin/', $body);
        $this->assertStringContainsString('Disallow: /api/', $body);
        $this->assertStringNotContainsString("Disallow: /\n", $body);
    }
}
