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

    /**
     * @return array{id: int, title: string, body: string, embed_url: ?string}|null
     */
    public function highlight(): ?array
    {
        return $this->remember('highlight', function () {
            $content = Content::published()
                ->with(['latestVersion'])
                ->whereHas('categories', fn($q) => $q->where('cate_cdslug', 'destacado'))
                ->latest('cont_dtpubl')
                ->first();

            if (! $content) {
                return null;
            }

            [$title, $body] = $this->localizedTitleAndBody($content);

            [$embedUrl, $body] = $this->extractBlockquoteEmbed($body);

            return [
                'id'        => $content->cont_idcont,
                'title'     => $title,
                'body'      => $body,
                'embed_url' => $embedUrl,
            ];
        });
    }

    /**
     * Extrae la URL de embed del primer <blockquote> del body HTML.
     * TipTap envuelve el texto en <p>, por lo que el patrón soporta ambas
     * formas: <blockquote><p>URL</p></blockquote> y <blockquote>URL</blockquote>.
     * El blockquote se elimina del body resultante para no mostrarlo al usuario.
     *
     * @return array{0: ?string, 1: string}
     */
    protected function extractBlockquoteEmbed(string $body): array
    {
        if (! str_contains($body, '<blockquote')) {
            return [null, $body];
        }

        // Captura URL dentro del primer blockquote (con o sin <p> intermedio)
        $pattern = '/<blockquote[^>]*>\s*(?:<p[^>]*>)?\s*(https?:\/\/\S+?)\s*(?:<\/p>)?\s*<\/blockquote>/is';

        if (! preg_match($pattern, $body, $m)) {
            return [null, $body];
        }

        $embedUrl = trim(strip_tags($m[1]));
        $cleanBody = trim(preg_replace($pattern, '', $body, 1));

        return [$embedUrl, $cleanBody];
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
            $settingLang = $this->locale->isDefault() ? null : $currentLang;
            $catSlug = $this->solutionCategorySlug($content);

            return [
                'id' => $content->cont_idcont,
                // slug = el del locale activo (lo usa el Vue para nav interna)
                'title' => $title,
                'slug' => $slugs[$currentLang] ?? $content->cont_cdslug,
                'body' => $this->buildPortfolioBody($body),
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
                'category' => $catSlug,
                'description' => $catSlug
                    ? (Setting::getValue("site.seo.category.{$catSlug}.description", '', $settingLang) ?? '')
                    : '',
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
        $settingLang = $this->locale->isDefault() ? null : $currentLang;

        $descriptions = $this->categoryDescriptions($settingLang);

        return $query->get()
            ->map(function (Content $c) use ($solutionsPrefix, $currentLang, $descriptions) {
                [$title, $body] = $this->localizedTitleAndBody($c);
                $slugs = $this->slugsByLocale($c);
                $linkSlug = $slugs[$currentLang] ?? $c->cont_cdslug;
                $catSlug = $this->solutionCategorySlug($c);
                return [
                    'id'          => $c->cont_idcont,
                    'title'       => $title,
                    'slug'        => $linkSlug,
                    'body'        => $body,
                    'image'       => $this->primaryImageUrl($c),
                    'href'        => $solutionsPrefix . $linkSlug,
                    'category'    => $catSlug,
                    'description' => $catSlug ? ($descriptions["site.seo.category.{$catSlug}.description"] ?? '') : '',
                ];
            })
            ->all();
    }

    /**
     * Carga en una sola query todas las descripciones de categorías de soluciones,
     * mergeando locale sobre defaults cuando corresponde.
     *
     * @return array<string, string>
     */
    protected function categoryDescriptions(?string $lang): array
    {
        $defaults = Setting::where('sett_cdkeys', 'like', 'site.seo.category.%.description')
            ->whereNull('sett_cdlang')
            ->pluck('sett_dsvalu', 'sett_cdkeys')
            ->toArray();

        if ($lang === null) {
            return $defaults;
        }

        $localized = Setting::where('sett_cdkeys', 'like', 'site.seo.category.%.description')
            ->where('sett_cdlang', $lang)
            ->pluck('sett_dsvalu', 'sett_cdkeys')
            ->toArray();

        return array_merge($defaults, $localized);
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

    /**
     * Transforma HTML plano (H2/H3 + UL del editor WYSIWYG) en la estructura
     * de tarjetas jf-portfolio. Si el body ya tiene .jf-portfolio__insurer
     * lo devuelve sin modificar.
     */
    protected function buildPortfolioBody(string $body): string
    {
        if (empty($body)) {
            return $body;
        }

        // Ya tiene estructura completa de tarjetas → no transformar
        if (str_contains($body, 'jf-portfolio__insurer')) {
            return $body;
        }

        // Sin H2/H3 → no es un portafolio, devolver tal cual
        if (! preg_match('/<h[23][^>]*>/i', $body)) {
            return $body;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $body, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $portfolio = $dom->createElement('div');
        $portfolio->setAttribute('class', 'jf-portfolio');

        $card = null;
        $source = $dom->documentElement ?? $dom;

        // Recorre nodos hijos directos del body parseado
        $nodes = iterator_to_array($source->childNodes);
        foreach ($nodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $tag = strtoupper($node->nodeName);

            if ($tag === 'H2' || $tag === 'H3') {
                $card = $dom->createElement('div');
                $card->setAttribute('class', 'jf-portfolio__insurer');

                $nameDiv = $dom->createElement('div');
                $nameDiv->setAttribute('class', 'jf-portfolio__insurer-name');
                $nameDiv->textContent = $node->textContent;

                $card->appendChild($nameDiv);
                $portfolio->appendChild($card);

            } elseif (($tag === 'UL' || $tag === 'OL') && $card !== null) {
                $list = $node->cloneNode(true);
                $existing = $list->getAttribute('class');
                $list->setAttribute('class', trim($existing . ' jf-portfolio__products'));
                // TipTap wraps <li> content in <p> — unwrap to keep uniform spacing
                foreach ($list->childNodes as $li) {
                    if (! $li instanceof \DOMElement || strtoupper($li->nodeName) !== 'LI') {
                        continue;
                    }
                    $children = iterator_to_array($li->childNodes);
                    if (count($children) === 1 && $children[0] instanceof \DOMElement && strtoupper($children[0]->nodeName) === 'P') {
                        $p = $children[0];
                        foreach (iterator_to_array($p->childNodes) as $pChild) {
                            $li->insertBefore($pChild->cloneNode(true), $p);
                        }
                        $li->removeChild($p);
                    }
                }
                $card->appendChild($list);

            } elseif ($card !== null) {
                $card->appendChild($node->cloneNode(true));
            }
        }

        return $dom->saveHTML($portfolio);
    }
}
