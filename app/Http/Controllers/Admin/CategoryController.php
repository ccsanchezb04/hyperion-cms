<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\CategoryResource;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $tree = Category::whereNull('cate_idpare')
            ->with('childrenRecursive')
            ->orderBy('cate_nmname')
            ->get();

        // Conteo de contenidos por categoría en una sola query
        $contentCounts = DB::table('hycms_content_category')
            ->select('coca_idcate', DB::raw('count(*) as cnt'))
            ->groupBy('coca_idcate')
            ->pluck('cnt', 'coca_idcate');

        return Inertia::render('Categories/Index', [
            'categories' => $this->serializeTree($tree, $contentCounts),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Create', [
            'categories' => $this->flatList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'slug'      => ['required', 'string', 'max:120', 'unique:hycms_categories,cate_cdslug'],
            'parent_id' => ['nullable', 'integer', 'exists:hycms_categories,cate_idcate'],
        ]);

        Category::create([
            'cate_nmname' => $validated['name'],
            'cate_cdslug' => $validated['slug'],
            'cate_idpare' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Edit', [
            'category' => [
                'id'        => $category->cate_idcate,
                'name'      => $category->cate_nmname,
                'slug'      => $category->cate_cdslug,
                'parent_id' => $category->cate_idpare,
            ],
            'categories' => $this->flatList($category->cate_idcate),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'slug'      => [
                'required',
                'string',
                'max:120',
                Rule::unique('hycms_categories', 'cate_cdslug')->ignore($category->cate_idcate, 'cate_idcate'),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:hycms_categories,cate_idcate'],
        ]);

        if (($validated['parent_id'] ?? null) === $category->cate_idcate) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.']);
        }

        $category->update([
            'cate_nmname' => $validated['name'],
            'cate_cdslug' => $validated['slug'],
            'cate_idpare' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->hasChildren()) {
            return back()->with('error', 'Cannot delete category with children. Delete or move children first.');
        }

        if ($category->contents()->exists()) {
            return back()->with('error', 'Cannot delete category with contents. Remove contents first.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Serialize a category collection (with eager-loaded children) into a
     * nested tree shape that the Vue tree component can render directly.
     */
    private function serializeTree($categories, $contentCounts = null): array
    {
        return $categories->map(function (Category $category) use ($contentCounts): array {
            return [
                'id'            => $category->cate_idcate,
                'name'          => $category->cate_nmname,
                'slug'          => $category->cate_cdslug,
                'parent_id'     => $category->cate_idpare,
                'content_count' => $contentCounts ? (int) ($contentCounts[$category->cate_idcate] ?? 0) : 0,
                'children'      => $this->serializeTree($category->childrenRecursive, $contentCounts),
            ];
        })->all();
    }

    /**
     * Flat ascending list used in <select> parent pickers. Optionally excludes
     * a category (and its descendants would belong to it) to avoid self-cycles
     * on edit screens.
     */
    private function flatList(?int $excludeId = null): array
    {
        $tree = Category::whereNull('cate_idpare')
            ->with('childrenRecursive')
            ->orderBy('cate_nmname')
            ->get();

        $out = [];
        $walk = function ($items, int $level) use (&$walk, &$out, $excludeId) {
            foreach ($items as $cat) {
                if ($excludeId !== null && $cat->cate_idcate === $excludeId) {
                    continue; // skip subtree to prevent cycles
                }
                $out[] = [
                    'id'    => $cat->cate_idcate,
                    'name'  => str_repeat('— ', $level) . $cat->cate_nmname,
                    'slug'  => $cat->cate_cdslug,
                    'level' => $level,
                ];
                if ($cat->childrenRecursive->isNotEmpty()) {
                    $walk($cat->childrenRecursive, $level + 1);
                }
            }
        };
        $walk($tree, 0);
        return $out;
    }
}
