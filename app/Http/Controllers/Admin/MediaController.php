<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Media::query()->with('uploadedBy');

        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'image') {
                $query->where('medi_cdtype', 'like', 'image/%');
            } elseif ($type === 'video') {
                $query->where('medi_cdtype', 'like', 'video/%');
            } elseif ($type === 'document') {
                $query->where('medi_cdtype', 'application/pdf');
            }
        }

        if ($request->filled('search')) {
            $query->where('medi_dspath', 'like', '%' . $request->input('search') . '%');
        }

        $items = $query->orderByDesc('medi_dtcrea')->get()->map(function (Media $m) {
            return [
                'id'         => $m->medi_idmedi,
                'path'       => $m->medi_dspath,
                'url'        => $m->medi_dspath ? asset('storage/' . $m->medi_dspath) : '',
                'filename'   => $m->medi_dspath ? basename($m->medi_dspath) : '',
                'mime_type'  => $m->medi_cdtype,
                'type'       => $m->medi_cdtype,
                'created_at' => optional($m->medi_dtcrea)->toDateTimeString(),
            ];
        });

        return Inertia::render('Media/Index', [
            'media'   => $items,
            'filters' => $request->only(['type', 'search']),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        if (! in_array($mime, Media::ALLOWED_TYPES, true)) {
            return back()->withErrors(['file' => 'File type not allowed.']);
        }

        $directory = 'media/' . match (true) {
            str_starts_with($mime, 'image/')   => 'images',
            str_starts_with($mime, 'video/')   => 'videos',
            $mime === 'application/pdf'        => 'documents',
            default                            => 'other',
        };

        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueName = $slug . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $uniqueName, 'public');

        Media::create([
            'medi_dspath' => $path,
            'medi_cdtype' => $mime,
            'medi_idusby' => $request->user()->user_iduser,
        ]);

        return redirect()->route('media.index')->with('success', 'File uploaded successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        if (Storage::disk('public')->exists($media->medi_dspath)) {
            Storage::disk('public')->delete($media->medi_dspath);
        }
        $media->delete();

        return redirect()->route('media.index')->with('success', 'Media deleted successfully.');
    }
}
