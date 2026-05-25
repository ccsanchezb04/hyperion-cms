<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Shared\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Filtros
        if ($request->has('parent')) {
            if ($request->input('parent') === 'null') {
                $query->root();
            } else {
                $query->where('cate_idpare', $request->input('parent'));
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('cate_nmname', 'like', "%{$search}%")
                  ->orWhere('cate_cdslug', 'like', "%{$search}%");
        }

        // Ordenamiento
        $query->orderBy('cate_nmname');

        $categories = $query->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display categories as a tree structure.
     */
    public function tree(): JsonResponse
    {
        $categories = Category::root()
            ->with('childrenRecursive')
            ->orderBy('cate_nmname')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'cate_nmname' => $request->input('name'),
            'cate_cdslug' => $request->input('slug'),
            'cate_idpare' => $request->input('parent_id'),
        ]);

        return response()->json([
            'data' => CategoryResource::make($category->load('parent', 'children')),
            'message' => 'Category created successfully',
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => CategoryResource::make($category->load('parent', 'children', 'contents')),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update([
            'cate_nmname' => $request->input('name', $category->cate_nmname),
            'cate_cdslug' => $request->input('slug', $category->cate_cdslug),
            'cate_idpare' => $request->input('parent_id', $category->cate_idpare),
        ]);

        return response()->json([
            'data' => CategoryResource::make($category->load('parent', 'children')),
            'message' => 'Category updated successfully',
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Verificar si tiene hijos
        if ($category->hasChildren()) {
            return response()->json([
                'message' => 'Cannot delete category with children. Delete or move children first.',
            ], 400);
        }

        // Verificar si tiene contenidos asociados
        if ($category->contents()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with contents. Remove contents first.',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Move a category to a new parent.
     */
    public function move(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'parent_id' => 'nullable|exists:hycms_categories,cate_idcate',
        ]);

        // Evitar ciclos (no puede ser padre de sí mismo)
        if ($request->input('parent_id') == $category->cate_idcate) {
            return response()->json([
                'message' => 'A category cannot be its own parent.',
            ], 400);
        }

        $category->update([
            'cate_idpare' => $request->input('parent_id'),
        ]);

        return response()->json([
            'data' => CategoryResource::make($category->load('parent', 'children')),
            'message' => 'Category moved successfully',
        ]);
    }
}
