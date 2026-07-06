# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech stack

- PHP 8.2+ / Laravel 12 backend (CI runs PHP 8.4)
- Vue 3 + TypeScript + Inertia.js frontend (no separate SPA — Inertia bridges server and client)
- Vite build (`resources/js/app.ts` is the entry)
- Tailwind CSS + Radix Vue + Bootstrap 5 + Lucide icons
- Laravel Sanctum for API token auth; session auth for Inertia pages
- Ziggy for sharing named routes with the frontend
- MySQL/MariaDB in dev; SQLite `:memory:` in tests (see `phpunit.xml`)

## Commands

Common workflow:

```bash
composer install && npm install
php artisan migrate --seed       # seeds roles, permissions, users, etc.
php artisan serve                # backend on :8000
npm run dev                      # vite dev server on 127.0.0.1:5173 (strictPort)
```

All-in-one dev (server + queue listener + vite, via concurrently):

```bash
composer run dev
```

Tests, lint, build:

```bash
php artisan test                              # full Pest/PHPUnit suite
./vendor/bin/phpunit --filter UserTest        # single test class
./vendor/bin/phpunit tests/Feature/AuthTest.php
npm run lint                                  # eslint --fix
npm run format                                # prettier write
npm run format:check
npm run build                                 # production assets
npm run build:ssr                             # SSR build
./vendor/bin/pint                             # PHP code style (Laravel Pint)
```

Default seeded logins (from `UserSeeder`): `admin@hyperion.local / admin123`, `editor@hyperion.local / editor123`, `viewer@hyperion.local / viewer123`.

## Database naming convention (important)

This project does NOT use Laravel defaults. Every table, primary key, timestamp, and column follows a strict `hycms_` convention — when adding a migration or model you must follow it or relations and auth will silently break.

- All tables prefixed `hycms_` (e.g. `hycms_users`, `hycms_contents`, `hycms_role_permissions`).
- Primary keys are NOT `id`. They are `<4-letter-table-abbr>_id<table-abbr>` (e.g. `user_iduser`, `cont_idcont`, `role_idrole`, `perm_idperm`, `noti_idnoti`).
- Columns are prefixed by the 4-letter table abbreviation + 2-letter type code:
  - `nm` = name, `ds` = description/email, `cd` = code/status/slug/password, `id` = foreign id, `dt` = datetime, `bo` = boolean, `nr` = number.
  - E.g. on `hycms_users`: `user_nmname`, `user_dsemai`, `user_cdpass`, `user_cdstat`, `user_dtcrea`, `user_dtupda`, `user_dtdele`.
- Each model overrides `$table`, `$primaryKey`, `CREATED_AT`, `UPDATED_AT`, and (when soft-deleting) `DELETED_AT` to map to these columns. See `app/Models/User.php` and `app/Models/Content.php` for the pattern.
- Pivot tables follow the same pattern with concatenated 4-letter abbrevs (e.g. `hycms_user_roles` with `usro_iduser`, `usro_idrole`; `hycms_role_permissions` with `rope_idrole`, `rope_idperm`; `hycms_content_category` with `coca_idcont`, `coca_idcate`).
- BelongsToMany / BelongsTo relations always pass the explicit foreign and parent key columns — never rely on Laravel inference.

### Auth identifier override

`User` overrides `getAuthIdentifierName()` to `user_dsemai` and `getAuthPassword()` to `user_cdpass`. Inertia/Sanctum auth flows work because of this — do NOT add an `email` or `password` column or rename these methods. Validation rules referencing the users table must use the real column names, e.g. `unique:hycms_users,user_dsemai`.

## Architecture

### Routing layers

- `routes/web.php` — Inertia pages under `/admin/*` (contents, media, categories, settings, menus). Each route just returns `Inertia::render('Path/Component')`; the real CRUD happens through the API layer below.
- `routes/auth.php`, `routes/settings.php` — auth + profile flows (also Inertia).
- `routes/api.php` — top-level Sanctum login/logout plus `/api/v1/*` group that `require`s files in `routes/api/v1/` (one per resource: `contents.php`, `categories.php`, `media.php`, `menus.php`, `settings.php`, `users.php`, `notifications.php`, `ai.php`, `auth.php`).
- API resource routes typically have a public read section and a `auth:sanctum` + `permission:...` group for writes. The Vue admin pages call these same `/api/v1/*` endpoints.

### Auth and permissions

- `bootstrap/app.php` registers middleware aliases: `role` → `RoleMiddleware`, `permission` → `PermissionMiddleware`, `verified` → built-in email verified.
- CSRF is disabled only for `login` and `register` paths.
- `PermissionMiddleware` requires ALL listed permissions; e.g. `permission:create-content,edit-content,delete-content` means the user must have every one of those slugs. Super-admin short-circuits all permission checks (`User::hasPermission` returns true if `isAdmin()`).
- `RoleMiddleware` accepts ANY of the listed role slugs.
- Roles relate to permissions via `hycms_role_permissions`. Use `Role::givePermission()`, `revokePermission()`, `syncPermissions()` instead of manipulating the pivot manually.
- There is also an `App\Traits\HasRoles` trait, but `User` defines `hasRole`/`hasPermission` directly — prefer the model methods on `User`.

### Inertia bridge

- `HandleInertiaRequests::rootView()` returns `app` for `/admin/*` and `site` for all other paths. The admin and public site are two separate Inertia applications sharing the same middleware.
- Admin shared props live in `adminShared()`: `auth.user` (with `permissions` array), `name`, `quote`, `flash.status`.
- Public site shared props live in `siteShared()`: `site.settings`, `site.menu`, `theme`, `flash.contact_status`. These must be **lazy closures** (not eager values) because `SetLocale` middleware runs after `share()` — a prop returned as a plain value would always use the default locale.
- Vue page resolution for admin: `Contents/Index` → `resources/js/pages/Contents/Index.vue`.
- Vue page resolution for public site: resolved from the active theme directory (see Theme system below).

### Frontend layout

- `resources/js/app.ts` boots Inertia + Vue + Ziggy and calls `initializeTheme()`.
- Path alias `@` → `resources/js` (configured in `vite.config.ts` and `tsconfig.json`).
- `resources/js/layouts/` holds `AppLayout.vue` (admin shell) and `AuthLayout.vue`, plus per-section layout folders.
- `resources/js/composables/` has shared composables (e.g. `useAppearance` for theme).
- Vite dev server is locked to `127.0.0.1:5173` with `strictPort: true` — don't change the port unless you also update HMR config.

### Public site layer

- `routes/site.php` (required from `routes/web.php`) defines the public-facing routes: `/` (home), `/soluciones[/{slug}]`, `/contact`, `/sitemap.xml`, `/robots.txt`, and their `/en/*` mirrors.
- Public controllers are in `App\Http\Controllers\Site\` and render theme pages via `Inertia::render('Home')`, `Inertia::render('Solutions/Index')`, etc. — the theme's active page directory is resolved at render time.
- `App\Services\SiteContentService` is the query layer for public pages. It returns flat arrays ready for Inertia props. All queries are cached with versioned keys (`hyperion:site:vN:lang:suffix`). Call `SiteContentService::flush()` to bust all public cache — this is done automatically by `SiteCacheObserver` on every `saved`/`deleted`/`restored` event for Content, Setting, Menu, etc.
- `App\Services\SiteSeoService` and `App\Services\SiteSitemapService` follow the same versioned-cache pattern.

### Theme system

- Each public-facing theme lives in `resources/themes/{slug}/` and declares a `theme.json` manifest (`slug`, `name`, `version`, `entry`, `sections`).
- Required files per theme: `theme.json`, `site.entry.ts` (Vite entry, boots its own Inertia+Vue instance), `layouts/SiteLayout.vue`, `pages/Home.vue`, `pages/Solutions/Index.vue`, `pages/Solutions/Show.vue`.
- `vite.config.ts` auto-discovers theme entries via `discoverThemeEntries()` at build time — adding a new theme directory is enough; no manual Vite config changes needed.
- Active theme is resolved by `App\Services\ThemeManager::activeSlug()`: DB setting `active_theme` (group `system`) → `config('hyperion.theme')` / env `HYPERION_THEME` → first discovered theme alphabetically.
- Theme styles must be self-contained — never import admin CSS (Bootstrap/Tailwind/Radix) from a theme entry. Use a theme-specific class prefix (e.g. `.jf-*`) to avoid collisions.
- Switch theme via `/admin/themes` (requires permission `manage-settings`) or `ThemeManager::setActive($slug)`.

### Multi-language (i18n)

- Supported locales: `es` (default, no URL prefix) and `en` (URL prefix `/en`). Managed by `App\Services\LocaleManager` (singleton).
- The `locale:en` middleware alias (`App\Http\Middleware\SetLocale`) sets the active locale for a request. **Always add this middleware to non-default locale route groups**, not individual routes.
- To add a new locale: (1) add it to `LocaleManager::SUPPORTED`, (2) add a route group with the right prefix and `locale:{lang}` middleware in `routes/site.php`, (3) seed translations for Content and Settings.
- Content translations: `ContentTranslation` model (`hycms_content_translations`, prefix `cotr_`). Fields: `cotr_cdlang`, `cotr_cdslug`, `cotr_nmtitl`, `cotr_dsbody`. Access via `$content->translation($lang)` (requires eager-loading `translations`).
- Menu item translations: `MenuItemTranslation` model (`hycms_menu_item_translations`, prefix `mitr_`). Access via `$menuItem->labelFor($lang)`.
- Setting translations: `Setting::getValue($key, $default, $lang)` and `Setting::getGroup($group, $lang)` merge locale-specific rows over the default (null-lang) rows.

### Content and versioning

- `Content` model (`hycms_contents`, prefix `cont_`) stores metadata only. Body lives in `ContentVersion` (`hycms_content_versions`, prefix `cove_`).
- `Content::latestVersion()` is a `hasOne` + `latestOfMany('cove_nrvers')` — use it for single-record access (`$content->latestVersion->cove_dsbody`). `Content::versions()` gives the full ordered history.
- Content types: `Content::TYPE_POST`, `TYPE_PAGE`, `TYPE_CUSTOM`. Statuses: `STATUS_DRAFT`, `STATUS_PUBLISHED`, `STATUS_ARCHIVED`.
- Per-content SEO overrides: `ContentSeo` model (`hycms_content_seo`, prefix `cose_`) via `$content->seo`. Fields: `cose_nmtitl`, `cose_dsdesc`, `cose_dsogim`, `cose_cdcano`, `cose_bonoix`.

### Settings model

- `Setting::getValue($key, $default, $lang)` — fetches a single value, locale-aware.
- `Setting::setValue($key, $value, $group, $lang)` — upserts a value; `$lang = null` means the default (language-neutral) row.
- `Setting::getGroup($group, $lang)` — returns `['key' => 'value']` array for a group, merging locale translations over defaults.
- Standard groups: `general`, `seo`, `media`, `mail`, `site`, `organization`, `integrations`.

### AI translation

- `App\Services\AITranslator` translates text via OpenAI (or falls back to a `[Translated to X]` stub when unconfigured).
- Configure via: `services.ai.provider` (default `openai`), `services.ai.api_key`, `services.ai.endpoint`.
- The service is shared by `AIController` (Sanctum API) and the admin `ContentController` (session auth) to avoid duplicating HTTP logic.

### Testing

- Pest is used for Feature tests (`tests/Pest.php` binds `Tests\TestCase` + `RefreshDatabase` to everything in `tests/Feature`). Unit tests use plain PHPUnit.
- The test DB is SQLite in-memory (`phpunit.xml`), so migrations must be SQLite-compatible. Avoid MySQL-only features in migrations or guard them.
- CI (`.github/workflows/tests.yml`) runs on push/PR to `develop` and `main`, builds frontend assets, runs `php artisan ziggy:generate`, then `phpunit`.

## Conventions to preserve

- When adding a model: set `$table`, `$primaryKey`, `CREATED_AT`/`UPDATED_AT` constants, and `DELETED_AT` if soft-deleting. Define every relation with explicit FK/PK arguments.
- When adding a migration: use the `hycms_` table prefix and the column-prefix scheme; mirror the timestamp pattern (`useCurrent()`, `useCurrentOnUpdate()`, `softDeletes('xxxx_dtdele')`).
- When adding an API endpoint: put the route file under `routes/api/v1/`, `require` it from `routes/api.php`, and gate writes behind `auth:sanctum` + `permission:<slugs>`.
- When adding an admin page: create the `Inertia::render` route in `routes/web.php` under the `admin` prefix and the matching `.vue` file under `resources/js/pages/`.
- Don't touch `vendor/` or `bootstrap/cache/`.
