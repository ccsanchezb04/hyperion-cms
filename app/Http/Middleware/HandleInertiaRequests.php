<?php

namespace App\Http\Middleware;

use App\Services\SiteContentService;
use App\Services\ThemeManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Default root template (admin). Overridden per-request by rootView().
     */
    protected $rootView = 'app';

    /**
     * Pick the Blade root view per request:
     *   /admin/*  → app.blade.php  (CMS admin bundle)
     *   else      → site.blade.php (public site theme bundle)
     */
    public function rootView(Request $request): string
    {
        return $this->isAdminRequest($request) ? 'app' : 'site';
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $base = parent::share($request);

        if ($this->isAdminRequest($request)) {
            return array_merge($base, $this->adminShared($request));
        }

        return array_merge($base, $this->siteShared($request));
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminShared(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        return [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->user_iduser,
                    'name' => $user->user_nmname,
                    'email' => $user->user_dsemai,
                    'avatar' => $user->avatar ?? null,
                    'email_verified_at' => $user->email_verified_at,
                    'permissions' => $user->getPermissionSlugs(),
                ] : null,
            ],
            'flash' => [
                'status'  => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Shared props for the public site. Intentionally minimal — no auth.user.
     *
     * @return array<string, mixed>
     */
    protected function siteShared(Request $request): array
    {
        $themes = app(ThemeManager::class);
        $site = app(SiteContentService::class);

        // settings y menu como closures: el route-level middleware SetLocale
        // corre DESPUÉS de share(), así que evaluación eager devolvería
        // siempre el locale por defecto. Lazy eval garantiza que cuando
        // Inertia compone la respuesta final el locale ya está fijado.
        return [
            'name' => config('app.name'),
            'theme' => [
                'slug' => $themes->activeSlug(),
                'manifest' => $themes->manifest(),
            ],
            'site' => [
                'settings'     => fn () => $site->siteSettings(),
                'organization' => fn () => $site->organizationSettings(),
                'menu'         => fn () => $site->mainMenu(),
                'integrations' => fn () => $site->integrations(),
            ],
            'flash' => [
                'contact_status' => fn () => $request->session()->get('contact_status'),
            ],
        ];
    }

    protected function isAdminRequest(Request $request): bool
    {
        return $request->is('admin', 'admin/*');
    }
}
