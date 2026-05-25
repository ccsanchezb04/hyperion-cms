<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreContentRequest;
use App\Http\Requests\Content\UpdateContentRequest;
use App\Http\Resources\Content\ContentResource;
use App\Http\Resources\Content\ContentVersionResource;
use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    /**
     * Display a listing of contents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Content::with(['author', 'categories']);

        // Filtros
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('cont_cdstat', $request->input('status'));
        }

        if ($request->has('author')) {
            $query->byAuthor($request->input('author'));
        }

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('cate_idcate', $request->input('category'));
            });
        }

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('cont_nmtitl', 'like', "%{$search}%")
                  ->orWhere('cont_cdslug', 'like', "%{$search}%");
        }

        // Ordenamiento
        $orderBy = $request->input('order_by', 'cont_dtcrea');
        $orderDir = $request->input('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $contents = $query->paginate($perPage);

        return response()->json([
            'data' => ContentResource::collection($contents),
            'meta' => [
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'per_page' => $contents->perPage(),
                'total' => $contents->total(),
            ],
        ]);
    }

    /**
     * Store a newly created content.
     */
    public function store(StoreContentRequest $request): JsonResponse
    {
        $content = Content::create([
            'cont_nmtitl' => $request->input('title'),
            'cont_cdslug' => $request->input('slug'),
            'cont_cdtype' => $request->input('type', Content::TYPE_POST),
            'cont_cdstat' => $request->input('status', Content::STATUS_DRAFT),
            'cont_idauth' => $request->user()->user_iduser,
            'cont_dtpubl' => $request->input('status') === Content::STATUS_PUBLISHED 
                ? now() 
                : null,
        ]);

        // Crear primera versión
        if ($request->has('body')) {
            ContentVersion::create([
                'cove_idcont' => $content->cont_idcont,
                'cove_nrvers' => 1,
                'cove_dsbodt' => $request->input('body_type', 'html'),
                'cove_dsbody' => $request->input('body'),
                'cove_dssumm' => $request->input('summary'),
                'cove_dsmeta' => $request->input('meta', []),
            ]);
        }

        // Asignar categorías
        if ($request->has('categories')) {
            $content->categories()->sync($request->input('categories'));
        }

        // Asignar media
        if ($request->has('media')) {
            $content->media()->sync($request->input('media'));
        }

        return response()->json([
            'data' => ContentResource::make($content->load(['author', 'categories', 'media', 'latestVersion'])),
            'message' => 'Content created successfully',
        ], 201);
    }

    /**
     * Display the specified content.
     */
    public function show(string $slug): JsonResponse
    {
        $content = Content::where('cont_cdslug', $slug)
            ->with(['author', 'categories', 'media', 'versions'])
            ->firstOrFail();

        return response()->json([
            'data' => ContentResource::make($content),
        ]);
    }

    /**
     * Update the specified content.
     */
    public function update(UpdateContentRequest $request, Content $content): JsonResponse
    {
        $content->update([
            'cont_nmtitl' => $request->input('title', $content->cont_nmtitl),
            'cont_cdslug' => $request->input('slug', $content->cont_cdslug),
            'cont_cdtype' => $request->input('type', $content->cont_cdtype),
            'cont_cdstat' => $request->input('status', $content->cont_cdstat),
        ]);

        // Actualizar fecha de publicación si cambia a published
        if ($request->input('status') === Content::STATUS_PUBLISHED && 
            $content->cont_cdstat !== Content::STATUS_PUBLISHED) {
            $content->cont_dtpubl = now();
            $content->save();
        }

        // Crear nueva versión si hay cambios en el body
        if ($request->has('body')) {
            $latestVersion = $content->versions()->orderByDesc('cove_nrvers')->first();
            $nextVersion = $latestVersion ? $latestVersion->cove_nrvers + 1 : 1;

            ContentVersion::create([
                'cove_idcont' => $content->cont_idcont,
                'cove_nrvers' => $nextVersion,
                'cove_dsbodt' => $request->input('body_type', 'html'),
                'cove_dsbody' => $request->input('body'),
                'cove_dssumm' => $request->input('summary'),
                'cove_dsmeta' => $request->input('meta', []),
            ]);
        }

        // Actualizar categorías
        if ($request->has('categories')) {
            $content->categories()->sync($request->input('categories'));
        }

        // Actualizar media
        if ($request->has('media')) {
            $content->media()->sync($request->input('media'));
        }

        return response()->json([
            'data' => ContentResource::make($content->load(['author', 'categories', 'media', 'latestVersion'])),
            'message' => 'Content updated successfully',
        ]);
    }

    /**
     * Remove the specified content.
     */
    public function destroy(Content $content): JsonResponse
    {
        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully',
        ]);
    }

    /**
     * Publish a content.
     */
    public function publish(Content $content): JsonResponse
    {
        $content->publish();

        return response()->json([
            'data' => ContentResource::make($content->load(['author', 'categories', 'media', 'latestVersion'])),
            'message' => 'Content published successfully',
        ]);
    }

    /**
     * Archive a content.
     */
    public function archive(Content $content): JsonResponse
    {
        $content->archive();

        return response()->json([
            'data' => ContentResource::make($content->load(['author', 'categories', 'media', 'latestVersion'])),
            'message' => 'Content archived successfully',
        ]);
    }

    /**
     * Get content versions.
     */
    public function versions(Content $content): JsonResponse
    {
        $versions = $content->versions()->orderByDesc('cove_nrvers')->get();

        return response()->json([
            'data' => ContentVersionResource::collection($versions),
        ]);
    }

    /**
     * Restore a specific version.
     */
    public function restoreVersion(Content $content, int $versionNumber): JsonResponse
    {
        $version = $content->versions()->where('cove_nrvers', $versionNumber)->firstOrFail();

        // Crear nueva versión basada en la versión restaurada
        $latestVersion = $content->versions()->orderByDesc('cove_nrvers')->first();
        $nextVersion = $latestVersion ? $latestVersion->cove_nrvers + 1 : 1;

        ContentVersion::create([
            'cove_idcont' => $content->cont_idcont,
            'cove_nrvers' => $nextVersion,
            'cove_dsbodt' => $version->cove_dsbodt,
            'cove_dsbody' => $version->cove_dsbody,
            'cove_dssumm' => $version->cove_dssumm,
            'cove_dsmeta' => $version->cove_dsmeta,
        ]);

        return response()->json([
            'data' => ContentResource::make($content->load(['author', 'categories', 'media', 'latestVersion'])),
            'message' => 'Version restored successfully',
        ]);
    }
}
