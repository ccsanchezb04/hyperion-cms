<?php

namespace App\Providers;

use App\Models\Content;
use App\Models\ContentSeo;
use App\Models\ContentTranslation;
use App\Models\ContentVersion;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use App\Observers\SiteCacheObserver;
use App\Services\LocaleManager;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocaleManager::class);
    }

    public function boot(): void
    {
        // Map canonical credential keys (email, password) to hycms_users columns
        // so Laravel's password broker and EloquentUserProvider work unchanged.
        Auth::provider('hycms-eloquent', function ($app, array $config) {
            return new class($app['hash'], $config['model']) extends EloquentUserProvider {
                public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
                {
                    if (isset($credentials['email'])) {
                        $credentials['user_dsemai'] = $credentials['email'];
                        unset($credentials['email']);
                    }
                    return parent::retrieveByCredentials($credentials);
                }
            };
        });

        // Invalidación de cache del sitio público al cambiar contenido.
        $models = [Content::class, ContentSeo::class, ContentTranslation::class, ContentVersion::class, Media::class, Menu::class, MenuItem::class, Setting::class];
        foreach ($models as $model) {
            $model::observe(SiteCacheObserver::class);
        }
    }
}
