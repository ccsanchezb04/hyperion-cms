<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Models\Setting;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use App\Services\SiteSitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Módulo /admin/seo. Permite editar settings de los grupos seo, organization
 * e integrations a través de 6 tabs (General, Open Graph, Schema.org, Sitemap,
 * Robots.txt, Integraciones).
 *
 * Las settings persisten vía el modelo Setting; el cambio invalida el cache
 * del sitio público (SiteContentService) y el del sitemap (SiteSitemapService).
 */
class SeoController extends Controller
{
    public function __construct(protected SiteSeoService $seo) {}

    public function index(): Response
    {
        $sitemapXml = app(SiteSitemapService::class)->xml();
        $urlCount = substr_count($sitemapXml, '<loc>');

        return Inertia::render('Seo/Index', [
            'settings' => [
                'seo'          => Setting::getGroup('seo'),
                'organization' => Setting::getGroup('organization'),
                'integrations' => Setting::getGroup('integrations'),
                'site'         => Setting::getGroup('site'),
            ],
            'meta' => [
                'sitemap_url_count' => $urlCount,
                'sitemap_xml'       => $sitemapXml,
                'robots_txt'        => $this->seo->robotsTxt(),
            ],
        ]);
    }

    public function update(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $group = $request->settingGroup();
        $allowed = array_flip($request->allowedKeysForTab());

        foreach ($validated['values'] ?? [] as $key => $value) {
            if (! isset($allowed[$key])) {
                continue;  // ignorar keys fuera de la whitelist del tab
            }
            Setting::setValue($key, $value ?? '', $group);
        }

        SiteContentService::flush();
        SiteSitemapService::flush();

        return back()->with('success', 'SEO settings saved');
    }

    /**
     * Sube una imagen OG global a storage/site/seo/og-default.<ext> y actualiza
     * el setting site.seo.og_image.
     */
    public function uploadOgImage(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $path = "site/seo/og-default.{$ext}";

        Storage::disk('public')->putFileAs('site/seo', $file, "og-default.{$ext}");

        Setting::setValue('site.seo.og_image', "/storage/{$path}", 'seo');

        SiteContentService::flush();

        return back()->with('success', 'OG image uploaded');
    }

    public function flushSitemap(): RedirectResponse
    {
        SiteSitemapService::flush();

        return back()->with('success', 'Sitemap cache cleared; will rebuild on next request');
    }

}
