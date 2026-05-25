# AGENTS.md

## Project overview

This repository is a Laravel 12 application scaffolded as a Vue 3 + Inertia starter kit.
It contains a PHP backend under `app/`, Inertia-powered frontend code under `resources/js/`, and standard Laravel config under `config/`.

The app uses:
- PHP 8.2+, Laravel 12
- Inertia.js with Vue 3
- Vite for frontend builds
- MySQL-compatible database support via Laravel database config
- `phpunit` / Pest-style tests under `tests/`

## Recommended commands

Use these commands to install dependencies, run the app, and execute tests:

- `composer install`
- `npm install`
- `npm run dev`
- `npm run build`
- `php artisan migrate`
- `php artisan serve`
- `php artisan test`

If you need a development server with frontend hot reload and queue worker combined, use the `dev` script defined in `composer.json`.

## Important files and directories

- `routes/web.php` - main web routes
- `app/Http/Controllers/` - controller logic
- `app/Http/Middleware/` - middleware definitions
- `app/Models/` - application models
- `resources/js/pages/` - Inertia page components
- `resources/js/components/` - shared Vue components
- `resources/js/layouts/` - Vue layouts
- `resources/css/app.css` - frontend styles
- `config/database.php` - database connections and default connection
- `package.json` / `composer.json` - build and dependency scripts
- `tests/` - feature and unit tests

## Development conventions

- Preserve the Laravel/Inertia structure and avoid changing the default route/controller conventions unless necessary.
- Use the `resources/js` folder for Vue 3 component and page development.
- Keep server-side logic in `app/Http/Controllers` and `app/Models`.
- Do not modify generated files under `bootstrap/cache/` or `vendor/`.

## Database notes

This app is configured to support MySQL. The environment file should use `DB_CONNECTION=mysql` and the standard Laravel MySQL env vars (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## When writing or modifying code

- Prefer Laravel idioms and helper methods.
- Use existing controller patterns for auth, settings, and profile flows.
- Follow formatter and linting scripts from `package.json` when editing frontend code.

## Useful references

- Laravel docs: https://laravel.com/docs
- Inertia docs: https://inertiajs.com
- Vite + Laravel: https://vitejs.dev/guide/
