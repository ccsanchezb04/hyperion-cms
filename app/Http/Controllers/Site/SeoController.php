<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SiteSeoService;
use App\Services\SiteSitemapService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SiteSitemapService $service): Response
    {
        return response($service->xml(), 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    public function robots(SiteSeoService $service): Response
    {
        return response($service->robotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
