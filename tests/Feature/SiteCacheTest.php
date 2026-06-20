<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Setting;
use App\Services\SiteContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiteCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    public function test_second_call_to_solutions_does_not_query_database(): void
    {
        $service = app(SiteContentService::class);

        $service->solutions();
        DB::enableQueryLog();

        $service->solutions();

        $queries = DB::getQueryLog();
        $this->assertCount(0, $queries, 'La segunda llamada a solutions() no debe consultar BD');
    }

    public function test_second_call_to_settings_does_not_query_database(): void
    {
        $service = app(SiteContentService::class);

        $service->siteSettings();
        DB::enableQueryLog();

        $service->siteSettings();

        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_second_call_to_menu_does_not_query_database(): void
    {
        $service = app(SiteContentService::class);

        $service->mainMenu();
        DB::enableQueryLog();

        $service->mainMenu();

        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_flush_invalidates_cache(): void
    {
        $service = app(SiteContentService::class);

        $first = $service->solutions();
        $versionBefore = SiteContentService::version();

        SiteContentService::flush();
        $versionAfter = SiteContentService::version();

        $this->assertGreaterThan($versionBefore, $versionAfter);

        DB::enableQueryLog();
        $service->solutions();

        // Tras flush, la siguiente llamada vuelve a consultar BD.
        $this->assertNotCount(0, DB::getQueryLog());
    }

    public function test_saving_content_invalidates_cache(): void
    {
        $service = app(SiteContentService::class);

        $initialCount = count($service->solutions());

        $solution = Content::published()
            ->whereHas('categories.parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->first();
        $solution->cont_nmtitl = 'Seguro de Vida Premium';
        $solution->save();

        $after = $service->solutions();
        $matchingTitle = collect($after)->firstWhere('slug', $solution->cont_cdslug);

        $this->assertSame('Seguro de Vida Premium', $matchingTitle['title']);
        $this->assertSame($initialCount, count($after));
    }

    public function test_saving_setting_invalidates_cache(): void
    {
        $service = app(SiteContentService::class);

        $service->siteSettings();

        Setting::setValue('site.heading', 'Nuevo Heading', 'site');

        $after = $service->siteSettings();
        $this->assertSame('Nuevo Heading', $after['site.heading']);
    }

    public function test_solution_by_slug_is_cached(): void
    {
        $service = app(SiteContentService::class);

        $service->solutionBySlug('seguro-de-vida');
        DB::enableQueryLog();

        $service->solutionBySlug('seguro-de-vida');

        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_deleting_content_invalidates_cache(): void
    {
        $service = app(SiteContentService::class);

        $service->solutions();

        $solution = Content::published()
            ->whereHas('categories.parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->first();
        $countBefore = count($service->solutions());

        $solution->delete();

        $countAfter = count($service->solutions());

        $this->assertSame($countBefore - 1, $countAfter);
    }
}
