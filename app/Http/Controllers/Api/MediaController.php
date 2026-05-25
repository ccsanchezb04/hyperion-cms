<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a listing of media.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::with(['uploadedBy', 'mediable']);

        // Filtros
        if ($request->has('type')) {
            $type = $request->input('type');
            switch ($type) {
                case 'image':
                    $query->images();
                    break;
                case 'video':
                    $query->videos();
                    break;
                case 'document':
                    $query->documents();
                    break;
            }
        }

        if ($request->has('uploader')) {
            $query->byUploader($request->input('uploader'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('medi_dspath', 'like', "%{$search}%");
        }

        // Ordenamiento
        $orderBy = $request->input('order_by', 'medi_dtcrea');
        $orderDir = $request->input('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->input('per_page', 20);
        $media = $query->paginate($perPage);

        return response()->json([
            'data' => MediaResource::collection($media),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    /**
     * Upload a new media file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'mediable_type' => 'nullable|string',
            'mediable_id' => 'nullable|integer',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();

        // Verificar tipo MIME permitido
        if (!in_array($mimeType, Media::ALLOWED_TYPES)) {
            return response()->json([
                'message' => 'File type not allowed. Allowed types: ' . implode(', ', Media::ALLOWED_TYPES),
            ], 400);
        }

        // Generar nombre único
        $originalName = $file->getClientOriginalName();
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $uniqueName = $fileName . '-' . time() . '.' . $extension;

        // Determinar directorio basado en el tipo
        $directory = 'media';
        if (str_starts_with($mimeType, 'image/')) {
            $directory .= '/images';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $directory .= '/videos';
        } elseif ($mimeType === 'application/pdf') {
            $directory .= '/documents';
        } else {
            $directory .= '/other';
        }

        // Guardar archivo
        $path = $file->storeAs($directory, $uniqueName, 'public');

        // Crear registro en base de datos
        $media = Media::create([
            'medi_dspath' => $path,
            'medi_cdtype' => $mimeType,
            'medi_idusby' => $request->user()->user_iduser,
        ]);

        // Asociar a entidad mediable si se proporciona
        if ($request->has('mediable_type') && $request->has('mediable_id')) {
            $media->update([
                'medi_nmmdbl' => $request->input('mediable_type'),
                'medi_idmdbl' => $request->input('mediable_id'),
            ]);
        }

        return response()->json([
            'data' => MediaResource::make($media->load('uploadedBy', 'mediable')),
            'message' => 'File uploaded successfully',
        ], 201);
    }

    /**
     * Display the specified media.
     */
    public function show(Media $media): JsonResponse
    {
        return response()->json([
            'data' => MediaResource::make($media->load('uploadedBy', 'mediable')),
        ]);
    }

    /**
     * Update the specified media.
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'mediable_type' => 'nullable|string',
            'mediable_id' => 'nullable|integer',
        ]);

        $media->update([
            'medi_nmmdbl' => $request->input('mediable_type', $media->medi_nmmdbl),
            'medi_idmdbl' => $request->input('mediable_id', $media->medi_idmdbl),
        ]);

        return response()->json([
            'data' => MediaResource::make($media->load('uploadedBy', 'mediable')),
            'message' => 'Media updated successfully',
        ]);
    }

    /**
     * Remove the specified media.
     */
    public function destroy(Media $media): JsonResponse
    {
        // Eliminar archivo del storage
        if (Storage::disk('public')->exists($media->medi_dspath)) {
            Storage::disk('public')->delete($media->medi_dspath);
        }

        // Eliminar registro de base de datos
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Batch upload multiple files.
     */
    public function batchUpload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:10240',
            'mediable_type' => 'nullable|string',
            'mediable_id' => 'nullable|integer',
        ]);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $mimeType = $file->getMimeType();

                // Verificar tipo MIME permitido
                if (!in_array($mimeType, Media::ALLOWED_TYPES)) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => 'File type not allowed',
                    ];
                    continue;
                }

                // Generar nombre único
                $originalName = $file->getClientOriginalName();
                $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $extension = $file->getClientOriginalExtension();
                $uniqueName = $fileName . '-' . time() . '-' . $index . '.' . $extension;

                // Determinar directorio
                $directory = 'media';
                if (str_starts_with($mimeType, 'image/')) {
                    $directory .= '/images';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $directory .= '/videos';
                } elseif ($mimeType === 'application/pdf') {
                    $directory .= '/documents';
                } else {
                    $directory .= '/other';
                }

                // Guardar archivo
                $path = $file->storeAs($directory, $uniqueName, 'public');

                // Crear registro
                $media = Media::create([
                    'medi_dspath' => $path,
                    'medi_cdtype' => $mimeType,
                    'medi_idusby' => $request->user()->user_iduser,
                    'medi_nmmdbl' => $request->input('mediable_type'),
                    'medi_idmdbl' => $request->input('mediable_id'),
                ]);

                $uploadedFiles[] = MediaResource::make($media->load('uploadedBy', 'mediable'));
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'data' => $uploadedFiles,
            'errors' => $errors,
            'message' => count($uploadedFiles) . ' files uploaded successfully',
        ], 201);
    }
}
