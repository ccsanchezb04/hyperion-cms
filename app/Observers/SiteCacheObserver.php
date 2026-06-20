<?php

namespace App\Observers;

use App\Services\SiteContentService;
use App\Services\SiteSitemapService;

/**
 * Invalida el cache del sitio público cuando cambia algún modelo cuyo contenido
 * se sirve a través de SiteContentService o SiteSitemapService.
 *
 * El blast radius es a propósito amplio: cualquier guardado o borrado dispara
 * un flush() (incrementa la versión de cache). Es más simple y barato que
 * razonar por categoría/menú/grupo. Los cambios en admin son infrecuentes.
 */
class SiteCacheObserver
{
    public function saved($model): void
    {
        SiteContentService::flush();
        SiteSitemapService::flush();
    }

    public function deleted($model): void
    {
        SiteContentService::flush();
        SiteSitemapService::flush();
    }

    public function restored($model): void
    {
        SiteContentService::flush();
        SiteSitemapService::flush();
    }
}
