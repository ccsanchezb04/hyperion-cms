<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Menu;
use App\Models\Setting;
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

            return $menu->rootItems()
                ->get()
                ->map(fn($item) => [
                    'label' => $item->mnit_nmlabe,
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
        return $this->remember('settings', fn() => Setting::getGroup('site'));
    }

    public function solutionBySlug(string $slug): ?array
    {
        return $this->remember('solution:' . $slug, function () use ($slug) {
            $content = Content::published()
                ->with(['latestVersion', 'media', 'categories.parent', 'seo'])
                ->where('cont_cdslug', $slug)
                ->whereHas('categories.parent', fn($q) => $q->where('cate_cdslug', 'soluciones'))
                ->first();

            if (! $content) {
                return null;
            }

            return [
                'id' => $content->cont_idcont,
                'title' => $content->cont_nmtitl,
                'slug' => $content->cont_cdslug,
                'body' => $content->latestVersion?->cove_dsbody ?? '',
                'image' => $this->primaryImageUrl($content),
                'category' => $this->solutionCategorySlug($content),
                'published_at' => $content->cont_dtpubl?->toDateTimeString(),
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
        $key = self::CACHE_PREFIX . 'v' . self::version() . ':' . $suffix;
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
            ->with(['latestVersion', 'media', 'categories.parent'])
            ->whereHas('categories.parent', fn($q) => $q->where('cate_cdslug', 'soluciones'))
            ->orderBy('cont_dtpubl');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn(Content $c) => [
                'id'       => $c->cont_idcont,
                'title'    => $c->cont_nmtitl,
                'slug'     => $c->cont_cdslug,
                'body'     => $c->latestVersion?->cove_dsbody ?? '',
                'image'    => $this->primaryImageUrl($c),
                'href'     => '/soluciones/' . $c->cont_cdslug,
                'category' => $this->solutionCategorySlug($c),
            ])
            ->all();
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
