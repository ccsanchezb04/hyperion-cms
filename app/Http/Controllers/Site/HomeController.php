<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected SiteContentService $site,
        protected SiteSeoService $seo,
    ) {
    }

    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'solutions' => $this->site->solutions(3),
            'testimonials' => $this->site->testimonials(6),
            'carousel' => $this->site->carousel(),
            'seo' => $this->seo->forHome(),
        ]);
    }
}
