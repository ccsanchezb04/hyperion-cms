<?php

namespace App\Services;

/**
 * Estado per-request del idioma activo del sitio público. Se registra como
 * singleton en el contenedor; el middleware ResolveLocale lo configura al
 * inicio del request según el prefix de la URL.
 *
 * Locales soportados:
 *   - 'es' (default, sin prefijo en URL)
 *   - 'en' (URL prefix '/en')
 *
 * Para agregar un idioma nuevo:
 *   1. Añadirlo a self::SUPPORTED
 *   2. Sembrar route group equivalente bajo el prefix correspondiente
 *   3. Sembrar traducciones de los Content y site settings para ese idioma
 */
class LocaleManager
{
    public const DEFAULT = 'es';
    public const SUPPORTED = ['es', 'en'];

    protected string $current = self::DEFAULT;

    public function current(): string
    {
        return $this->current;
    }

    public function setCurrent(string $lang): void
    {
        if (! in_array($lang, self::SUPPORTED, true)) {
            return;
        }
        $this->current = $lang;
    }

    /**
     * @return array<int, string>
     */
    public function supported(): array
    {
        return self::SUPPORTED;
    }

    public function default(): string
    {
        return self::DEFAULT;
    }

    public function isDefault(): bool
    {
        return $this->current === self::DEFAULT;
    }

    /**
     * Convierte una ruta del sitio (con o sin prefix de idioma) a su
     * equivalente en otro idioma. Útil para el LanguageSwitcher.
     *
     * Ejemplos:
     *   urlForLocale('en', '/soluciones/x')        => '/en/solutions/x'
     *   urlForLocale('es', '/en/solutions/x')      => '/soluciones/x'
     *   urlForLocale('en', '/')                    => '/en'
     */
    public function urlForLocale(string $targetLang, string $currentPath): string
    {
        // 1) Quitar el prefix actual si tiene
        $path = $currentPath;
        foreach (self::SUPPORTED as $lang) {
            if ($lang === self::DEFAULT) continue;
            if ($path === "/{$lang}" || str_starts_with($path, "/{$lang}/")) {
                $path = substr($path, strlen("/{$lang}")) ?: '/';
                break;
            }
        }

        // 2) Traducir segmento "solutions" <-> "soluciones" basado en idiomas
        $segmentMap = [
            'es' => ['solutions' => 'soluciones'],
            'en' => ['soluciones' => 'solutions'],
        ];

        if (isset($segmentMap[$targetLang])) {
            foreach ($segmentMap[$targetLang] as $from => $to) {
                $path = preg_replace("#^/{$from}(/|$)#", "/{$to}$1", $path);
            }
        }

        // 3) Agregar prefix del target lang si no es default
        if ($targetLang === self::DEFAULT) {
            return $path === '' ? '/' : $path;
        }

        if ($path === '/' || $path === '') {
            return "/{$targetLang}";
        }
        return "/{$targetLang}{$path}";
    }
}
