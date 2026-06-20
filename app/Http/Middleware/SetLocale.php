<?php

namespace App\Http\Middleware;

use App\Services\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marca el idioma activo en el LocaleManager según el parámetro pasado.
 * Aplicado a route groups con `->middleware('locale:en')` para el grupo /en/*.
 *
 * El default (es) no requiere middleware: el LocaleManager arranca en es.
 */
class SetLocale
{
    public function __construct(protected LocaleManager $locale) {}

    public function handle(Request $request, Closure $next, string $lang): Response
    {
        $this->locale->setCurrent($lang);
        return $next($request);
    }
}
