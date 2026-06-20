<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use App\Services\SiteSeoService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SolutionController extends Controller
{
    public function __construct(
        protected SiteContentService $site,
        protected SiteSeoService $seo,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Solutions/Index', [
            'solutions' => $this->site->solutions(),
            'seo'       => $this->seo->forSolutionsIndex(),
        ]);
    }

    public function show(string $slug): Response
    {
        $solution = $this->site->solutionBySlug($slug);

        if (! $solution) {
            throw new NotFoundHttpException("Solución '{$slug}' no encontrada.");
        }

        return Inertia::render('Solutions/Show', [
            'solution' => $solution,
            'seo'      => $this->seo->forSolution($solution),
        ]);
    }
}
