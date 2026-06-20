<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Resolución de temas instalados bajo resources/themes/.
 *
 * Cada tema vive en su propio directorio y declara un theme.json en la raíz:
 *
 *   resources/themes/{slug}/
 *     theme.json
 *     site.entry.ts
 *     pages/Home.vue
 *     pages/Solutions/Index.vue
 *     pages/Solutions/Show.vue
 *     ...
 *
 * El tema activo se persiste en Setting 'active_theme' (grupo 'system'),
 * con fallback a config('hyperion.theme') y luego al primer tema disponible.
 */
class ThemeManager
{
    public const SETTING_KEY = 'active_theme';
    public const SETTING_GROUP = 'system';

    protected string $themesPath;

    /** @var array<string, array<string, mixed>>|null */
    protected ?array $discoveryCache = null;

    public function __construct(?string $themesPath = null)
    {
        $this->themesPath = $themesPath ?? resource_path('themes');
    }

    /**
     * Slug del tema activo. Lee de DB; si la tabla no existe (durante migrate)
     * o el slug no resuelve, cae al config y luego al primero descubierto.
     */
    public function activeSlug(): string
    {
        $fromDb = $this->safeReadActiveFromDb();
        $candidate = $fromDb ?: config('hyperion.theme', 'juanfer');

        $themes = $this->discover();

        if (isset($themes[$candidate])) {
            return $candidate;
        }

        if ($candidate && $fromDb) {
            Log::warning("Tema activo '{$candidate}' no encontrado en el disco; usando fallback.");
        }

        return array_key_first($themes) ?: 'juanfer';
    }

    /**
     * Cambia el tema activo. Devuelve true si el slug es válido.
     */
    public function setActive(string $slug): bool
    {
        if (! isset($this->discover()[$slug])) {
            return false;
        }

        Setting::setValue(self::SETTING_KEY, $slug, self::SETTING_GROUP);
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(?string $slug = null): array
    {
        $slug = $slug ?? $this->activeSlug();
        return $this->discover()[$slug] ?? [];
    }

    public function activePath(): string
    {
        return $this->themesPath.DIRECTORY_SEPARATOR.$this->activeSlug();
    }

    /**
     * Ruta del entry de Vite (relativa al proyecto) para el tema activo.
     */
    public function viteEntry(?string $slug = null): string
    {
        $slug = $slug ?? $this->activeSlug();
        $entry = $this->manifest($slug)['entry'] ?? 'site.entry.ts';

        return 'resources/themes/'.$slug.'/'.$entry;
    }

    /**
     * Lista todos los temas detectados, indexados por slug.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        if ($this->discoveryCache !== null) {
            return $this->discoveryCache;
        }

        $themes = [];

        if (! File::isDirectory($this->themesPath)) {
            return $this->discoveryCache = [];
        }

        foreach (File::directories($this->themesPath) as $dir) {
            $manifestPath = $dir.DIRECTORY_SEPARATOR.'theme.json';

            if (! File::exists($manifestPath)) {
                continue;
            }

            $manifest = json_decode(File::get($manifestPath), true);

            if (! is_array($manifest)) {
                Log::warning("theme.json inválido en {$dir}");
                continue;
            }

            $slug = $manifest['slug'] ?? basename($dir);
            $themes[$slug] = $manifest;
        }

        ksort($themes);
        return $this->discoveryCache = $themes;
    }

    /**
     * Útil para tests: limpia el cache de descubrimiento.
     */
    public function flushDiscoveryCache(): void
    {
        $this->discoveryCache = null;
    }

    /**
     * Etiquetas <link rel="icon"> del tema activo, listas para emitir en blade.
     * Si el tema no declara 'favicon' en theme.json, se usa el favicon global
     * (Hyperion) como fallback.
     *
     * @return array<int, array{rel: string, href: string, type?: string, sizes?: string}>
     */
    public function faviconLinks(?string $slug = null): array
    {
        $favicon = $this->manifest($slug)['favicon'] ?? null;

        if (! is_array($favicon) || empty($favicon)) {
            return $this->defaultFaviconLinks();
        }

        $links = [];

        if (! empty($favicon['ico'])) {
            $links[] = [
                'rel' => 'icon',
                'href' => asset($favicon['ico']),
                'sizes' => 'any',
            ];
        }

        if (! empty($favicon['png_32'])) {
            $links[] = [
                'rel' => 'icon',
                'type' => 'image/png',
                'sizes' => '32x32',
                'href' => asset($favicon['png_32']),
            ];
        }

        if (! empty($favicon['png_16'])) {
            $links[] = [
                'rel' => 'icon',
                'type' => 'image/png',
                'sizes' => '16x16',
                'href' => asset($favicon['png_16']),
            ];
        }

        if (! empty($favicon['apple_touch_180'])) {
            $links[] = [
                'rel' => 'apple-touch-icon',
                'sizes' => '180x180',
                'href' => asset($favicon['apple_touch_180']),
            ];
        }

        return $links ?: $this->defaultFaviconLinks();
    }

    /**
     * @return array<int, array{rel: string, href: string, sizes?: string, type?: string}>
     */
    protected function defaultFaviconLinks(): array
    {
        return [
            ['rel' => 'icon', 'href' => asset('favicon.ico'), 'sizes' => 'any'],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '32x32', 'href' => asset('images/favicon-32.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '16x16', 'href' => asset('images/favicon-16.png')],
            ['rel' => 'apple-touch-icon', 'sizes' => '180x180', 'href' => asset('images/favicon-180.png')],
        ];
    }

    protected function safeReadActiveFromDb(): ?string
    {
        try {
            $value = Setting::getValue(self::SETTING_KEY);
            return is_string($value) && $value !== '' ? $value : null;
        } catch (\Throwable $e) {
            // Durante migrate:fresh la tabla puede no existir todavía.
            return null;
        }
    }
}
