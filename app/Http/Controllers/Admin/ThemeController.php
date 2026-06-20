<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    public function __construct(protected ThemeManager $themes)
    {
    }

    public function index(): Response
    {
        $active = $this->themes->activeSlug();
        $items = collect($this->themes->discover())
            ->map(fn (array $manifest, string $slug) => [
                'slug' => $slug,
                'name' => $manifest['name'] ?? $slug,
                'version' => $manifest['version'] ?? null,
                'description' => $manifest['description'] ?? null,
                'author' => $manifest['author'] ?? null,
                'sections' => $manifest['sections'] ?? [],
                'is_active' => $slug === $active,
            ])
            ->values()
            ->all();

        return Inertia::render('Themes/Index', [
            'themes' => $items,
            'activeSlug' => $active,
        ]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
        ]);

        if (! $this->themes->setActive($data['slug'])) {
            return back()->withErrors(['slug' => "El tema '{$data['slug']}' no existe."]);
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('status', "Tema activado: {$data['slug']}");
    }
}
