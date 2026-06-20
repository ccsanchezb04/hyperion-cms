# Hyperion CMS — Themes

Cada tema en este directorio es una skin independiente del sitio público (`/`). El admin se queda como está; sólo el frontend público usa el tema activo.

## Estructura mínima

```
resources/themes/{slug}/
├── theme.json              # manifest (obligatorio)
├── site.entry.ts           # entry de Vite (Inertia + Vue + ZiggyVue)
├── layouts/
│   └── SiteLayout.vue      # wrapper con header / footer
├── pages/
│   ├── Home.vue              # GET /
│   ├── Solutions/Index.vue   # GET /soluciones
│   └── Solutions/Show.vue    # GET /soluciones/{slug}
└── css/                    # opcional, lo que importes desde site.entry.ts
```

Otros componentes y assets son libres — Vite procesa los `import` desde el entry.

## theme.json

```json
{
    "name": "My Theme",
    "slug": "my-theme",
    "version": "0.1.0",
    "description": "Una línea sobre qué hace.",
    "author": "Tu nombre",
    "entry": "site.entry.ts",
    "sections": ["hero", "solutions", "contact"]
}
```

- `slug` debe coincidir con el nombre del directorio.
- `entry` es relativo a la raíz del tema; cambialo solo si renombras el archivo.
- `sections` es informativo (se muestra en el admin); no afecta el renderizado.

## Props que recibe cada página

Los controllers comparten datos vía `Inertia::render` (page-specific) y `HandleInertiaRequests::siteShared` (global). Tu tema puede asumir que están disponibles:

**Globales (en `$page.props`):**
- `site.settings` — `Record<string, string>` con todas las claves del grupo `site`
- `site.menu` — `{ label, href }[]` del menú `site-main`
- `flash.contact_status` — `'sent'` cuando el form se envió OK

**Página específica:**
- `Home`: `solutions: Solution[]`, `testimonials: Testimonial[]`, `carousel: { src, alt }[]`
- `Solutions/Index`: `solutions: Solution[]`
- `Solutions/Show`: `solution: { id, title, slug, body, image, published_at }`

Donde `Solution = { id, title, slug, body, image: string|null, href: string }`.

## Aislamiento de estilos

Cada tema bundlea su propio CSS y JS. **No tomes CSS del admin** (Bootstrap/Tailwind/Radix). Importa lo que necesites desde el `site.entry.ts` y manten tus clases con un prefijo propio (`.jf-*`, `.df-*`, etc.) para evitar choques si más adelante se cargan varios temas en una misma página (no es el caso hoy, pero es buena higiene).

## Cómo añadir un tema nuevo

1. `mkdir resources/themes/mi-tema` y crea el `theme.json`.
2. Copia `site.entry.ts`, `layouts/SiteLayout.vue` y los `pages/...` del tema `default` como punto de partida.
3. `npm run build` — Vite auto-descubre el nuevo entry (mirá `vite.config.ts`, función `discoverThemeEntries`).
4. Reinicia el dev server (`npm run dev`) si estaba corriendo: el escaneo de temas ocurre en el arranque.
5. Activa el tema desde `/admin/themes` (necesitas permiso `manage-settings`).

## Cómo se resuelve el tema activo

`App\Services\ThemeManager::activeSlug()` aplica este orden:

1. `Setting::getValue('active_theme')` (grupo `system`)
2. `config('hyperion.theme')` — env `HYPERION_THEME`, default `juanfer`
3. Primer tema descubierto alfabéticamente

Si el slug almacenado no existe en disco, se loggea un warning y se cae al fallback.

## Tests

- `tests/Feature/ThemeManagerTest.php` — discover, fallback, set/get active
- `tests/Feature/Admin/ThemeControllerTest.php` — auth, permisos, activar

Cualquier tema nuevo debería pasar `ThemeManagerTest::test_discover_returns_installed_themes` sin cambios — el test enumera lo que encuentre.
