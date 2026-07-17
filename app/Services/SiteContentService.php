<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Menu;
use App\Models\Setting;
use App\Services\LocaleManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Capa de consulta del sitio público. Devuelve estructuras planas listas para
 * pasar como props de Inertia.
 *
 * Todas las consultas se cachean con TTL e invalidación por versión:
 *   - Cada key incluye el número de versión actual (Cache::CACHE_PREFIX.'v{N}:...').
 *   - SiteContentService::flush() incrementa la versión, dejando huérfanos los
 *     keys viejos (que expiran por TTL).
 *
 * Esto funciona con cualquier driver (database, file, redis) sin depender de
 * cache tags. El observer SiteCacheObserver llama flush() cuando se guarda o
 * elimina cualquier modelo relevante (Content, Setting, Menu, etc.).
 */
class SiteContentService
{
    public const CACHE_PREFIX = 'hyperion:site:';
    public const CACHE_VERSION_KEY = self::CACHE_PREFIX . 'version';
    public const CACHE_TTL = 3600;

    public function __construct(protected LocaleManager $locale) {}

    /**
     * @return array<int, array{
     *   id: int, title: string, slug: string, body: string, image: ?string, href: string
     * }>
     */
    public function solutions(?int $limit = null): array
    {
        return $this->remember(
            'solutions:' . ($limit ?? 'all'),
            fn() => $this->fetchSolutions($limit)
        );
    }

    /**
     * @return array<int, array{name: string, quote: string}>
     */
    public function testimonials(?int $limit = null): array
    {
        return $this->remember(
            'testimonials:' . ($limit ?? 'all'),
            fn() => array_map(
                fn(array $c) => ['name' => $c['title'], 'quote' => $c['body']],
                $this->fetchContentsInCategory('testimonios', $limit)
            )
        );
    }

    /**
     * @return array<int, array{src: string, alt: string}>
     */
    public function carousel(): array
    {
        return $this->remember(
            'carousel',
            fn() =>
            Content::published()
                ->with('media')
                ->whereHas('categories', fn($q) => $q->where('cate_cdslug', 'carousel'))
                ->orderBy('cont_dtpubl')
                ->get()
                ->filter(fn(Content $c) => $c->media->isNotEmpty())
                ->map(fn(Content $c) => [
                    'src' => Storage::url($c->media->first()->medi_dspath),
                    'alt' => $c->cont_nmtitl,
                ])
                ->values()
                ->all()
        );
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    public function mainMenu(string $slug = 'site-main'): array
    {
        return $this->remember('menu:' . $slug, function () use ($slug) {
            $menu = Menu::where('menu_cdslug', $slug)->first();
            if (! $menu) {
                return [];
            }

            $currentLang = $this->locale->current();
            $isDefault = $this->locale->isDefault();

            return $menu->rootItems()
                ->with('translations')
                ->get()
                ->map(fn($item) => [
                    'label' => $isDefault ? $item->mnit_nmlabe : $item->labelFor($currentLang),
                    'href' => $item->mnit_cdtype === 'url'
                        ? ($item->mnit_dsurll ?? '#')
                        : $item->resolveUrl(),
                ])
                ->all();
        });
    }

    /**
     * @return array<string, string>
     */
    public function siteSettings(): array
    {
        $lang = $this->locale->current();
        return $this->remember('settings', fn() => Setting::getGroup('site', $lang));
    }

    public function organizationSettings(): array
    {
        $lang = $this->locale->current();
        return $this->remember('org-settings', fn() => Setting::getGroup('organization', $lang));
    }

    /**
     * @return array<string, string>
     */
    public function integrations(): array
    {
        return $this->remember('integrations', fn() => Setting::getGroup('integrations'));
    }

    /**
     * @return array<int, array{id: int, name: string, logo: ?string, url: ?string}>
     */
    public function allies(?int $limit = null): array
    {
        return $this->remember(
            'allies:' . ($limit ?? 'all'),
            function () use ($limit) {
                $query = Content::published()
                    ->with(['media'])
                    ->whereHas('categories', fn ($q) => $q->where('cate_cdslug', 'aliados'))
                    ->orderBy('cont_dtcrea');

                if ($limit !== null) {
                    $query->limit($limit);
                }

                return $query->get()
                    ->map(fn (Content $c) => [
                        'id'   => $c->cont_idcont,
                        'name' => $c->cont_nmtitl,
                        'logo' => $this->primaryImageUrl($c),
                        // El body del content puede contener la URL externa del aliado
                        'url'  => null,
                    ])
                    ->all();
            }
        );
    }

    public function solutionBySlug(string $slug): ?array
    {
        return $this->remember('solution:' . $slug, function () use ($slug) {
            $content = $this->findSolutionBySlug($slug);

            if (! $content) {
                return null;
            }

            [$title, $body] = $this->localizedTitleAndBody($content);
            $slugs = $this->slugsByLocale($content);
            $currentLang = $this->locale->current();

            return [
                'id' => $content->cont_idcont,
                // slug = el del locale activo (lo usa el Vue para nav interna)
                'title' => $title,
                'slug' => $slugs[$currentLang] ?? $content->cont_cdslug,
                'body' => $body,
                'embed_url' => $content->cont_dsembd ?? null,
                'image' => $this->primaryImageUrl($content),
                'media' => $content->media
                    ->filter(fn($m) => $m->isImage())
                    ->map(fn($m) => [
                        'url' => Storage::url($m->medi_dspath),
                        'alt' => $content->cont_nmtitl,
                    ])
                    ->values()
                    ->all(),
                'category' => $this->solutionCategorySlug($content),
                'published_at' => $content->cont_dtpubl?->toDateTimeString(),
                'slugs' => $slugs,
                'seo_override' => $content->seo ? [
                    'meta_title'       => $content->seo->cose_nmtitl,
                    'meta_description' => $content->seo->cose_dsdesc,
                    'og_image'         => $content->seo->cose_dsogim,
                    'canonical'        => $content->seo->cose_cdcano,
                    'noindex'          => (bool) $content->seo->cose_bonoix,
                ] : null,
            ];
        });
    }

    /**
     * Resuelve un slug a su Content, locale-aware. Primero busca el slug en
     * el idioma activo (vía ContentTranslation.cotr_cdslug), y cae al slug
     * default (cont_cdslug). Así una visita a /en/solutions/life-insurance
     * encuentra el content cuando cliente sembró cotr_cdslug='life-insurance'
     * para lang='en'; y /en/solutions/seguro-de-vida sigue funcionando
     * cuando no hay slug EN explícito (back-compat).
     */
    protected function findSolutionBySlug(string $slug): ?Content
    {
        $base = fn () => Content::published()
            ->with(['latestVersion', 'media', 'categories.parent', 'seo', 'translations'])
            ->whereHas('categories.parent', fn($q) => $q->where('cate_cdslug', 'soluciones'));

        if (! $this->locale->isDefault()) {
            $lang = $this->locale->current();
            $byTranslation = $base()
                ->whereHas('translations', fn ($q) => $q
                    ->where('cotr_cdlang', $lang)
                    ->where('cotr_cdslug', $slug)
                )
                ->first();
            if ($byTranslation) {
                return $byTranslation;
            }
        }

        return $base()->where('cont_cdslug', $slug)->first();
    }

    /**
     * @return array<string, string>
     */
    protected function slugsByLocale(Content $content): array
    {
        $slugs = [$this->locale->default() => $content->cont_cdslug];
        foreach ($content->translations as $t) {
            $slugs[$t->cotr_cdlang] = $t->cotr_cdslug ?: $content->cont_cdslug;
        }
        // Asegurar que todos los idiomas soportados tengan un slug (fallback al default)
        foreach ($this->locale->supported() as $lang) {
            if (! isset($slugs[$lang])) {
                $slugs[$lang] = $content->cont_cdslug;
            }
        }
        return $slugs;
    }

    /**
     * Incrementa la versión de cache, invalidando todos los keys sin enumerarlos.
     */
    public static function flush(): void
    {
        $current = Cache::get(self::CACHE_VERSION_KEY, 1);
        Cache::forever(self::CACHE_VERSION_KEY, $current + 1);
    }

    /**
     * Versión actual del namespace de cache (la usan los keys construidos).
     */
    public static function version(): int
    {
        return Cache::rememberForever(self::CACHE_VERSION_KEY, fn() => 1);
    }

    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    protected function remember(string $suffix, callable $callback)
    {
        $key = self::CACHE_PREFIX . 'v' . self::version() . ':' . $this->locale->current() . ':' . $suffix;
        return Cache::remember($key, self::CACHE_TTL, $callback);
    }

    /**
     * @return array<int, array{
     *   id: int, title: string, slug: string, body: string, image: ?string, href: string
     * }>
     */
    protected function fetchContentsInCategory(string $slug, ?int $limit): array
    {
        $query = Content::published()
            ->with(['latestVersion', 'media'])
            ->whereHas('categories', fn($q) => $q->where('cate_cdslug', $slug))
            ->orderBy('cont_dtpubl');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn(Content $c) => [
                'id' => $c->cont_idcont,
                'title' => $c->cont_nmtitl,
                'slug' => $c->cont_cdslug,
                'body' => $c->latestVersion?->cove_dsbody ?? '',
                'image' => $this->primaryImageUrl($c),
                'href' => '#',
            ])
            ->all();
    }

    /**
     * Fetch de soluciones a través de las categorías hijas del umbrella
     * 'soluciones'. Incluye categoría hija como metadato (para SEO/OG).
     *
     * @return array<int, array{
     *   id: int, title: string, slug: string, body: string, image: ?string,
     *   href: string, category: ?string
     * }>
     */
    protected function fetchSolutions(?int $limit): array
    {
        $query = Content::published()
            ->with(['latestVersion', 'media', 'categories.parent', 'translations'])
            ->whereHas('categories.parent', fn($q) => $q->where('cate_cdslug', 'soluciones'))
            ->orderBy('cont_dtpubl');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $solutionsPrefix = $this->locale->isDefault() ? '/soluciones/' : '/' . $this->locale->current() . '/solutions/';
        $currentLang = $this->locale->current();

        return $query->get()
            ->map(function (Content $c) use ($solutionsPrefix, $currentLang) {
                [$title, $body] = $this->localizedTitleAndBody($c);
                $slugs = $this->slugsByLocale($c);
                $linkSlug = $slugs[$currentLang] ?? $c->cont_cdslug;
                return [
                    'id'       => $c->cont_idcont,
                    'title'    => $title,
                    'slug'     => $linkSlug,
                    'body'     => $body,
                    'image'    => $this->primaryImageUrl($c),
                    'href'     => $solutionsPrefix . $linkSlug,
                    'category' => $this->solutionCategorySlug($c),
                ];
            })
            ->all();
    }

    /**
     * Devuelve [title, body] aplicando traducción si existe en el locale
     * actual. Fallback: title del Content (cont_nmtitl) y body de la
     * última ContentVersion.
     *
     * @return array{0: string, 1: string}
     */
    protected function localizedTitleAndBody(Content $content): array
    {
        $defaultTitle = $content->cont_nmtitl;
        $defaultBody = $content->latestVersion?->cove_dsbody ?? '';

        if ($this->locale->isDefault()) {
            return [$defaultTitle, $defaultBody];
        }

        $translation = $content->translation($this->locale->current());
        if (! $translation) {
            return [$defaultTitle, $defaultBody];
        }

        return [
            $translation->cotr_nmtitl ?: $defaultTitle,
            $translation->cotr_dsbody ?: $defaultBody,
        ];
    }

    /**
     * Slug de la categoría hija de 'soluciones' a la que pertenece el content
     * (salud, vida, movilidad, etc.). Null si no encaja en la taxonomía.
     */
    protected function solutionCategorySlug(Content $content): ?string
    {
        foreach ($content->categories as $cat) {
            if ($cat->parent && $cat->parent->cate_cdslug === 'soluciones') {
                return $cat->cate_cdslug;
            }
        }
        return null;
    }

    protected function primaryImageUrl(Content $content): ?string
    {
        $media = $content->media->first();
        return $media ? Storage::url($media->medi_dspath) : null;
    }
}
