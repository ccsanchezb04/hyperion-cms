<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Genera sitemap.xml dinámico. Cacheado 1h; SiteCacheObserver invalida cuando
 * cambia un Content publicado.
 *
 * Incluye:
 *   - Home (/)
 *   - Índice de soluciones (/soluciones)
 *   - Cada Content publicado dentro de una categoría hija de 'soluciones'
 *
 * Excluye: /admin/*, /api/*, drafts, testimonios, carousel.
 */
class SiteSitemapService
{
    public const CACHE_KEY = 'hyperion:seo:sitemap';
    public const CACHE_TTL = 3600;

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

        $urls[] = $this->urlEntry($host . '/', null, '1.0', 'weekly');
        $urls[] = $this->urlEntry($host . '/soluciones', null, '0.9', 'weekly');

        $solutions = Content::published()
            ->whereHas('categories.parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->orderBy('cont_dtupda', 'desc')
            ->get(['cont_idcont', 'cont_cdslug', 'cont_dtupda']);

        foreach ($solutions as $s) {
            $urls[] = $this->urlEntry(
                $host . '/soluciones/' . $s->cont_cdslug,
                $s->cont_dtupda?->toAtomString(),
                '0.8',
                'monthly'
            );
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
}
