<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Models\Category;
use App\Models\Setting;
use App\Services\LocaleManager;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use App\Services\SiteSitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

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
    public function __construct(
        protected SiteSeoService $seo,
        protected LocaleManager $locale,
    ) {}

    public function index(Request $request): Response
    {
        $requested = $request->query('lang');
        $activeLang = in_array($requested, $this->locale->supported(), true) ? $requested : $this->locale->default();
        $settingLang = $activeLang === $this->locale->default() ? null : $activeLang;

        $sitemapXml = app(SiteSitemapService::class)->xml();
        $urlCount = substr_count($sitemapXml, '<loc>');

        return Inertia::render('Seo/Index', [
            'settings' => [
                'seo'          => Setting::getGroup('seo', $settingLang),
                'organization' => Setting::getGroup('organization', $settingLang),
                'integrations' => Setting::getGroup('integrations', $settingLang),
                'site'         => Setting::getGroup('site', $settingLang),
            ],
            'meta' => [
                'sitemap_url_count' => $urlCount,
                'sitemap_xml'       => $sitemapXml,
                'robots_txt'        => $this->seo->robotsTxt(),
            ],
            'i18n' => [
                'active'    => $activeLang,
                'default'   => $this->locale->default(),
                'supported' => $this->locale->supported(),
            ],
            'solutionCategories' => $this->solutionCategoriesList(),
        ]);
    }

    /**
     * Categorías hijas de 'soluciones' como [slug => name]. La usa el tab
     * Categories del admin para enumerar campos OG + description por
     * categoría.
     *
     * @return array<int, array{slug: string, name: string}>
     */
    protected function solutionCategoriesList(): array
    {
        return Category::whereHas('parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->orderBy('cate_nmname')
            ->get(['cate_cdslug', 'cate_nmname'])
            ->map(fn ($c) => ['slug' => $c->cate_cdslug, 'name' => $c->cate_nmname])
            ->all();
    }

    public function update(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $group = $request->settingGroup();
        $lang = $request->lang();
        $allowed = array_flip($request->allowedKeysForTab());

        foreach ($validated['values'] ?? [] as $key => $value) {
            if (! isset($allowed[$key])) {
                continue;  // ignorar keys fuera de la whitelist del tab
            }
            Setting::setValue($key, $value ?? '', $group, $lang);
        }

        SiteContentService::flush();
        SiteSitemapService::flush();

        return back()->with('success', 'SEO settings saved');
    }

    /**
     * Sube una imagen OG global. Si una extensión de imagen (GD/Imagick) está
     * disponible, optimiza: resize cover 1200x630 + encode JPEG quality 85 →
     * se guarda como og-default.jpg. Si no hay extensión disponible, cae al
     * upload raw (con un warning en log).
     */
    public function uploadOgImage(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $file = $request->file('image');
        $optimized = $this->optimizeOgImage($file->getRealPath());

        if ($optimized !== null) {
            $path = 'site/seo/og-default.jpg';
            Storage::disk('public')->put($path, $optimized);
        } else {
            // Sin GD/Imagick: respetar el formato original
            $ext = $file->getClientOriginalExtension();
            $path = "site/seo/og-default.{$ext}";
            Storage::disk('public')->putFileAs('site/seo', $file, "og-default.{$ext}");
        }

        Setting::setValue('site.seo.og_image', "/storage/{$path}", 'seo');

        SiteContentService::flush();

        return back()->with('success', 'OG image uploaded');
    }

    /**
     * Intenta optimizar la imagen: resize cover 1200x630 + JPEG quality 85.
     * Devuelve los bytes optimizados o null si no fue posible (driver de
     * imagen no disponible, fichero inválido, etc.).
     */
    protected function optimizeOgImage(string $sourcePath): ?string
    {
        try {
            $manager = ImageManager::gd();
        } catch (\Throwable $e) {
            try {
                $manager = ImageManager::imagick();
            } catch (\Throwable $e2) {
                Log::warning('OG image optimization skipped: no GD/Imagick available');
                return null;
            }
        }

        try {
            $image = $manager->read($sourcePath);
            $image->cover(1200, 630);
            return (string) $image->encode(new JpegEncoder(85));
        } catch (\Throwable $e) {
            Log::warning('OG image optimization failed: ' . $e->getMessage());
            return null;
        }
    }

    public function flushSitemap(): RedirectResponse
    {
        SiteSitemapService::flush();

        return back()->with('success', 'Sitemap cache cleared; will rebuild on next request');
    }

}
