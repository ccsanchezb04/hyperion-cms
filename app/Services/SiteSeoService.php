<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Arma el array `seo` listo para inyectar como prop de Inertia en cada página
 * del sitio público. Lo consume el componente `SiteHead.vue` del tema activo.
 *
 * Forma del array devuelto (estable; el contrato lo respeta SiteHead):
 *   [
 *     'title'       => string,            // texto del <title> ya aplicando template
 *     'description' => string,            // meta name="description"
 *     'keywords'    => string,            // meta name="keywords"
 *     'canonical'   => string,            // <link rel="canonical">
 *     'robots'      => string,            // meta name="robots"
 *     'locale'      => string,            // og:locale + lang
 *     'og'          => array,             // og:* tags
 *     'twitter'     => array,             // twitter:* tags
 *     'json_ld'     => array<int, array>, // bloques structured data (uno o más)
 *     'alternates'  => array<int, array{hreflang: string, href: string}>, // hreflang links
 *   ]
 */
class SiteSeoService
{
    public const CACHE_PREFIX = 'hyperion:seo:';
    public const CACHE_TTL = 3600;

    public function __construct(
        protected SiteContentService $site,
        protected LocaleManager $locale,
    ) {}

    public function forHome(): array
    {
        $title = $this->title($this->labelForCurrentLocale('Inicio', 'Home'));
        $description = $this->getSetting('site.seo.description', 'seo');
        $url = $this->absoluteUrl($this->localizedPath('/'));

        return $this->compose([
            'title'       => $title,
            'description' => $description,
            'canonical'   => $url,
            'og' => [
                'title'       => $title,
                'description' => $this->getSetting('site.seo.og_description', 'seo', $description),
                'image'       => $this->absoluteUrl($this->getSetting('site.seo.og_image', 'seo')),
                'url'         => $url,
                'type'        => 'website',
            ],
            'json_ld' => [
                $this->organizationSchema(),
            ],
            'alternates' => $this->alternatesFor('/'),
        ]);
    }

    public function forSolutionsIndex(): array
    {
        $heading = $this->getSetting('site.solutions.heading', 'site',
            $this->labelForCurrentLocale('Soluciones', 'Solutions'));
        $title = $this->title($heading);
        $description = $this->getSetting('site.seo.description', 'seo');
        $url = $this->absoluteUrl($this->localizedPath('/soluciones'));
        $breadcrumbLabel = $this->labelForCurrentLocale('Soluciones', 'Solutions');

        return $this->compose([
            'title'       => $title,
            'description' => $description,
            'canonical'   => $url,
            'og' => [
                'title'       => $title,
                'description' => $this->getSetting('site.seo.og_description', 'seo', $description),
                'image'       => $this->absoluteUrl($this->getSetting('site.seo.og_image', 'seo')),
                'url'         => $url,
                'type'        => 'website',
            ],
            'json_ld' => [
                $this->breadcrumbSchema([
                    [$this->labelForCurrentLocale('Inicio', 'Home'), '/'],
                    [$breadcrumbLabel, '/soluciones'],
                ]),
            ],
            'alternates' => $this->alternatesFor('/soluciones'),
        ]);
    }

    /**
     * @param array{
     *   id?: int, title: string, slug: string, body?: string, image?: ?string,
     *   category?: ?string,
     *   slugs?: array<string, string>,
     *   seo_override?: ?array{
     *     meta_title?: ?string, meta_description?: ?string, og_image?: ?string,
     *     canonical?: ?string, noindex?: bool
     *   }
     * } $solution Array devuelto por SiteContentService::solutionBySlug.
     */
    public function forSolution(array $solution): array
    {
        $override = $solution['seo_override'] ?? null;
        $categorySlug = $solution['category'] ?? null;
        $slugs = $solution['slugs'] ?? [];

        // Title: override per-page > template aplicado al título del content
        $title = ! empty($override['meta_title'])
            ? $override['meta_title']
            : $this->title($solution['title']);

        // Description: override per-page > override por categoría > body trimmed > description global
        $description = $override['meta_description'] ?? null;

        if (! $description && $categorySlug) {
            $description = $this->getSetting("site.seo.category.{$categorySlug}.description", 'seo');
        }
        if (! $description && ! empty($solution['body'])) {
            $description = $this->truncate(strip_tags($solution['body']), 160);
        }
        if (! $description) {
            $description = $this->getSetting('site.seo.description', 'seo');
        }

        // OG image: override per-page > específica de categoría > imagen del content > global
        $ogImage = $override['og_image'] ?? null;
        if (! $ogImage && $categorySlug) {
            $ogImage = $this->getSetting("site.seo.category.{$categorySlug}.og_image", 'seo');
        }
        if (! $ogImage && ! empty($solution['image'])) {
            $ogImage = $solution['image'];
        }
        if (! $ogImage) {
            $ogImage = $this->getSetting('site.seo.og_image', 'seo');
        }

        // Canonical: override per-page > URL absoluta con slug del locale activo
        $currentLang = $this->locale->current();
        $currentSlug = $slugs[$currentLang] ?? $solution['slug'];
        $url = ! empty($override['canonical'])
            ? $override['canonical']
            : $this->absoluteUrl($this->localizedSolutionPath($currentSlug, $currentLang));

        $overrides = [
            'title'       => $title,
            'description' => $description,
            'canonical'   => $url,
            'og' => [
                'title'       => $title,
                'description' => $description,
                'image'       => $this->absoluteUrl($ogImage),
                'url'         => $url,
                'type'        => 'article',
            ],
            'json_ld' => [
                $this->serviceSchema($solution, $description),
                $this->breadcrumbSchema([
                    [$this->labelForCurrentLocale('Inicio', 'Home'), '/'],
                    [$this->labelForCurrentLocale('Soluciones', 'Solutions'), '/soluciones'],
                    [$solution['title'], '/soluciones/' . ($slugs[$this->locale->default()] ?? $solution['slug'])],
                ]),
            ],
            'alternates' => $this->alternatesForSolution($slugs),
        ];

        // Override per-page de noindex pisa la política global
        if (! empty($override['noindex'])) {
            $overrides['robots'] = 'noindex,nofollow';
        }

        return $this->compose($overrides);
    }

    /**
     * Texto raw para robots.txt, respetando setting site.seo.robots.
     */
    public function robotsTxt(): string
    {
        $policy = $this->getSetting('site.seo.robots', 'seo', 'noindex,nofollow');
        $host = rtrim($this->getSetting('site.seo.canonical_host', 'seo', ''), '/');
        $disallowAll = str_contains($policy, 'noindex') || str_contains($policy, 'none');

        $lines = ['User-agent: *'];
        $lines[] = $disallowAll ? 'Disallow: /' : 'Disallow: /admin/';
        $lines[] = 'Disallow: /api/';
        $lines[] = '';
        if ($host !== '') {
            $lines[] = 'Sitemap: ' . $host . '/sitemap.xml';
        }

        return implode("\n", $lines) . "\n";
    }

    // ─── Composición / merge ──────────────────────────────────────────────

    /**
     * Aplica defaults globales y merge profundo. Mantiene el contrato del
     * array seo (todas las keys siempre presentes para que el componente Vue
     * no tenga que hacer null-checks).
     */
    protected function compose(array $overrides): array
    {
        $locale = $this->getSetting('site.seo.locale', 'seo', 'es_CO');
        $siteName = $this->getSetting('site.heading', 'site', 'JuanFer Seguros');
        $keywords = $this->getSetting('site.seo.keywords', 'seo', '');
        $robots = $this->getSetting('site.seo.robots', 'seo', 'noindex,nofollow');
        $defaultImage = $this->absoluteUrl($this->getSetting('site.seo.og_image', 'seo'));

        $og = array_merge([
            'title'       => $overrides['title'] ?? $siteName,
            'description' => $overrides['description'] ?? '',
            'image'       => $defaultImage,
            'url'         => $overrides['canonical'] ?? '',
            'type'        => 'website',
            'locale'      => $locale,
            'site_name'   => $siteName,
        ], $overrides['og'] ?? []);

        $twitter = array_merge([
            'card'        => 'summary_large_image',
            'title'       => $og['title'],
            'description' => $og['description'],
            'image'       => $og['image'],
        ], $overrides['twitter'] ?? []);

        return [
            'title'       => $overrides['title'] ?? $siteName,
            'description' => $overrides['description'] ?? '',
            'keywords'    => $keywords,
            'canonical'   => $overrides['canonical'] ?? '',
            'robots'      => $overrides['robots'] ?? $robots,
            'locale'      => $locale,
            'og'          => $og,
            'twitter'     => $twitter,
            'json_ld'     => $overrides['json_ld'] ?? [],
            'alternates'  => $overrides['alternates'] ?? [],
        ];
    }

    protected function title(string $page): string
    {
        $template = $this->getSetting('site.seo.title_template', 'seo', '{page} | JuanFer Seguros');
        return str_replace('{page}', $page, $template);
    }

    // ─── i18n helpers ─────────────────────────────────────────────────────

    /**
     * Devuelve el path localizado al locale actual a partir de la ruta default
     * (Spanish). Para /soluciones/{slug}:
     *   es -> /soluciones/{slug}
     *   en -> /en/solutions/{slug}
     */
    protected function localizedPath(string $defaultPath): string
    {
        return $this->locale->urlForLocale($this->locale->current(), $defaultPath);
    }

    /**
     * Path absoluto de una solución para un locale específico, usando el slug
     * traducido si existe. No depende de urlForLocale (que solo hace swap de
     * segmento, no de slug).
     */
    protected function localizedSolutionPath(string $slug, string $lang): string
    {
        if ($lang === $this->locale->default()) {
            return '/soluciones/' . $slug;
        }
        return '/' . $lang . '/solutions/' . $slug;
    }

    /**
     * Hreflang alternates para una solución, usando el slug correspondiente
     * a cada idioma (fallback al ES slug si no hay translation con slug
     * propio).
     *
     * @param array<string, string> $slugs
     * @return array<int, array{hreflang: string, href: string}>
     */
    protected function alternatesForSolution(array $slugs): array
    {
        $alternates = [];
        $defaultSlug = $slugs[$this->locale->default()] ?? null;

        foreach ($this->locale->supported() as $lang) {
            $slug = $slugs[$lang] ?? $defaultSlug;
            if (! $slug) continue;
            $alternates[] = [
                'hreflang' => $lang,
                'href'     => $this->absoluteUrl($this->localizedSolutionPath($slug, $lang)),
            ];
        }
        if ($defaultSlug) {
            $alternates[] = [
                'hreflang' => 'x-default',
                'href'     => $this->absoluteUrl($this->localizedSolutionPath($defaultSlug, $this->locale->default())),
            ];
        }
        return $alternates;
    }

    /**
     * Genera el array de hreflang alternates para todos los idiomas soportados,
     * a partir del path "canónico" en español.
     *
     * @return array<int, array{hreflang: string, href: string}>
     */
    protected function alternatesFor(string $defaultPath): array
    {
        $alternates = [];
        foreach ($this->locale->supported() as $lang) {
            $alternates[] = [
                'hreflang' => $lang,
                'href'     => $this->absoluteUrl($this->locale->urlForLocale($lang, $defaultPath)),
            ];
        }
        // x-default apunta al locale por defecto
        $alternates[] = [
            'hreflang' => 'x-default',
            'href'     => $this->absoluteUrl($this->locale->urlForLocale($this->locale->default(), $defaultPath)),
        ];
        return $alternates;
    }

    protected function labelForCurrentLocale(string $es, string $en): string
    {
        return $this->locale->current() === 'en' ? $en : $es;
    }

    // ─── JSON-LD builders ─────────────────────────────────────────────────

    /**
     * Schema InsuranceAgency + LocalBusiness anidados (array @type).
     */
    public function organizationSchema(): array
    {
        $lang = $this->locale->current();
        $org = Setting::getGroup('organization', $lang);
        $seo = Setting::getGroup('seo', $lang);

        $sameAs = array_values(array_filter([
            $org['site.organization.social.facebook'] ?? null,
            $org['site.organization.social.instagram'] ?? null,
            $org['site.organization.social.linkedin'] ?? null,
            $org['site.organization.social.tiktok'] ?? null,
        ]));

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => ['InsuranceAgency', 'LocalBusiness'],
            'name'        => $org['site.organization.name'] ?? 'JuanFer Seguros',
            'url'         => $this->absoluteUrl('/'),
            'logo'        => $this->absoluteUrl($seo['site.seo.logo'] ?? null),
            'telephone'   => $org['site.organization.phone'] ?? null,
            'email'       => $org['site.organization.email'] ?? null,
            'taxID'       => $org['site.organization.legal_code'] ?? null,
            'address'     => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $org['site.organization.address.street'] ?? null,
                'addressLocality' => $org['site.organization.address.locality'] ?? null,
                'addressRegion'   => $org['site.organization.address.region'] ?? null,
                'addressCountry'  => $org['site.organization.address.country'] ?? null,
                'postalCode'      => $org['site.organization.address.postal_code'] ?? null,
            ],
            'openingHours' => $org['site.organization.opening_hours'] ?? null,
            'sameAs'       => $sameAs,
        ];

        return $this->stripNulls($schema);
    }

    protected function serviceSchema(array $solution, ?string $description): array
    {
        $orgName = Setting::getValue('site.organization.name', 'JuanFer Seguros', $this->locale->current());

        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'name'        => $solution['title'],
            'description' => $description,
            'serviceType' => 'Insurance',
            'provider'    => [
                '@type' => 'InsuranceAgency',
                'name'  => $orgName,
            ],
            'areaServed'  => [
                '@type' => 'Country',
                'name'  => 'Colombia',
            ],
        ];
    }

    /**
     * @param array<int, array{0: string, 1: string}> $crumbs Pares [label, path].
     */
    protected function breadcrumbSchema(array $crumbs): array
    {
        $items = [];
        foreach ($crumbs as $idx => [$label, $path]) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $idx + 1,
                'name'     => $label,
                'item'     => $this->absoluteUrl($this->localizedPath($path)),
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    protected function getSetting(string $key, string $group, ?string $default = null): ?string
    {
        $value = Setting::getValue($key, $default, $this->locale->current());
        return $value !== '' ? $value : $default;
    }

    protected function absoluteUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }
        $host = rtrim($this->getSetting('site.seo.canonical_host', 'seo', config('app.url', '')), '/');
        return $host . '/' . ltrim($path, '/');
    }

    protected function truncate(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $max - 1)) . '…';
    }

    /**
     * Elimina recursivamente keys con valor null o '' del array (mantiene
     * sub-arrays no vacíos). Útil para no emitir campos vacíos en JSON-LD.
     */
    protected function stripNulls(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->stripNulls($value);
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
}
