<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Genera sitemap.xml dinámico. Cacheado 1h; SiteCacheObserver invalida cuando
 * cambia un Content publicado.
 *
 * Incluye, por cada locale soportado:
 *   - Home (/, /en)
 *   - Índice de soluciones (/soluciones, /en/solutions)
 *   - Cada Content publicado bajo 'soluciones' (con slug traducido cuando
 *     existe; fallback al slug ES)
 *
 * Excluye: /admin/*, /api/*, drafts, testimonios, carousel.
 */
class SiteSitemapService
{
    public const CACHE_KEY = 'hyperion:seo:sitemap';
    public const CACHE_TTL = 3600;

    public function __construct(protected LocaleManager $locale) {}

    public function xml(): string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->build();
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function build(): string
    {
        $host = rtrim(
            Setting::getValue('site.seo.canonical_host', config('app.url', '')),
            '/'
        );

        $urls = [];

        // Home + índice por cada locale
        foreach ($this->locale->supported() as $lang) {
            $homePath = $this->localizedHomePath($lang);
            $indexPath = $this->localizedIndexPath($lang);
            $urls[] = $this->urlEntry($host . $homePath, null, '1.0', 'weekly');
            $urls[] = $this->urlEntry($host . $indexPath, null, '0.9', 'weekly');
        }

        // Soluciones publicadas
        $solutions = Content::published()
            ->with('translations')
            ->whereHas('categories.parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->orderBy('cont_dtupda', 'desc')
            ->get(['cont_idcont', 'cont_cdslug', 'cont_dtupda']);

        foreach ($solutions as $s) {
            $lastmod = $s->cont_dtupda?->toAtomString();
            foreach ($this->locale->supported() as $lang) {
                $slug = $this->solutionSlugForLang($s, $lang);
                $urls[] = $this->urlEntry(
                    $host . $this->localizedSolutionPath($slug, $lang),
                    $lastmod,
                    '0.8',
                    'monthly'
                );
            }
        }

        $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $body .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $body .= implode("\n", $urls);
        $body .= "\n</urlset>\n";

        return $body;
    }

    protected function urlEntry(string $loc, ?string $lastmod, string $priority, string $changefreq): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
        if ($lastmod) {
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        }
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>";
        return $xml;
    }

    protected function localizedHomePath(string $lang): string
    {
        return $lang === $this->locale->default() ? '/' : '/' . $lang;
    }

    protected function localizedIndexPath(string $lang): string
    {
        return $lang === $this->locale->default() ? '/soluciones' : '/' . $lang . '/solutions';
    }

    protected function localizedSolutionPath(string $slug, string $lang): string
    {
        return $lang === $this->locale->default()
            ? '/soluciones/' . $slug
            : '/' . $lang . '/solutions/' . $slug;
    }

    /**
     * Slug de la solución en el idioma dado. Fallback al cont_cdslug ES.
     */
    protected function solutionSlugForLang(Content $content, string $lang): string
    {
        if ($lang === $this->locale->default()) {
            return $content->cont_cdslug;
        }
        $t = $content->translations->firstWhere('cotr_cdlang', $lang);
        return $t?->cotr_cdslug ?: $content->cont_cdslug;
    }
}
