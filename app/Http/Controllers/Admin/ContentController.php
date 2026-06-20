<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Content::query()->with(['author']);

        if ($request->filled('type')) {
            $query->where('cont_cdtype', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('cont_cdstat', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('cont_nmtitl', 'like', "%{$search}%")
                  ->orWhere('cont_cdslug', 'like', "%{$search}%");
            });
        }

        $contents = $query->orderByDesc('cont_dtcrea')->get()->map(function (Content $c) {
            return [
                'id'           => $c->cont_idcont,
                'title'        => $c->cont_nmtitl,
                'slug'         => $c->cont_cdslug,
                'type'         => $c->cont_cdtype,
                'status'       => $c->cont_cdstat,
                'published_at' => optional($c->cont_dtpubl)->toDateTimeString(),
                'created_at'   => optional($c->cont_dtcrea)->toDateTimeString(),
                'author'       => $c->author ? ['name' => $c->author->user_nmname] : null,
            ];
        });

        return Inertia::render('Contents/Index', [
            'contents' => $contents,
            'filters'  => $request->only(['type', 'status', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Contents/Create', [
            'categories' => $this->categoriesForSelect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:200'],
            'slug'       => ['required', 'string', 'max:200', 'unique:hycms_contents,cont_cdslug'],
            'type'       => ['required', Rule::in([Content::TYPE_POST, Content::TYPE_PAGE, Content::TYPE_CUSTOM])],
            'status'     => ['required', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'body'       => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:hycms_categories,cate_idcate'],
        ]);

        $content = Content::create([
            'cont_nmtitl' => $validated['title'],
            'cont_cdslug' => $validated['slug'],
            'cont_cdtype' => $validated['type'],
            'cont_cdstat' => $validated['status'],
            'cont_idauth' => $request->user()->user_iduser,
            'cont_dtpubl' => $validated['status'] === Content::STATUS_PUBLISHED ? now() : null,
        ]);

        if (! empty($validated['body'])) {
            ContentVersion::create([
                'cove_idcont' => $content->cont_idcont,
                'cove_nrvers' => 1,
                'cove_dsbody' => $validated['body'],
            ]);
        }

        if (! empty($validated['categories'])) {
            $content->categories()->sync($validated['categories']);
        }

        return redirect()->route('contents.index')->with('success', 'Content created successfully.');
    }

    public function edit(Content $content): Response
    {
        $content->load(['categories', 'latestVersion']);

        return Inertia::render('Contents/Edit', [
            'content' => [
                'id'         => $content->cont_idcont,
                'title'      => $content->cont_nmtitl,
                'slug'       => $content->cont_cdslug,
                'type'       => $content->cont_cdtype,
                'status'     => $content->cont_cdstat,
                'body'       => $content->latestVersion?->cove_dsbody ?? '',
                'categories' => $content->categories->pluck('cate_idcate')->all(),
            ],
            'categories' => $this->categoriesForSelect(),
        ]);
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        $validated = $request->validate([
            'title'  => ['required', 'string', 'max:200'],
            'slug'   => [
                'required', 'string', 'max:200',
                Rule::unique('hycms_contents', 'cont_cdslug')->ignore($content->cont_idcont, 'cont_idcont'),
            ],
            'type'   => ['required', Rule::in([Content::TYPE_POST, Content::TYPE_PAGE, Content::TYPE_CUSTOM])],
            'status' => ['required', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'body'   => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:hycms_categories,cate_idcate'],
        ]);

        $wasPublished = $content->cont_cdstat === Content::STATUS_PUBLISHED;
        $content->update([
            'cont_nmtitl' => $validated['title'],
            'cont_cdslug' => $validated['slug'],
            'cont_cdtype' => $validated['type'],
            'cont_cdstat' => $validated['status'],
            'cont_dtpubl' => $validated['status'] === Content::STATUS_PUBLISHED && ! $wasPublished
                ? now()
                : $content->cont_dtpubl,
        ]);

        if (array_key_exists('body', $validated) && $validated['body'] !== null) {
            ContentVersion::create([
                'cove_idcont' => $content->cont_idcont,
                'cove_nrvers' => ContentVersion::nextVersionNumber($content->cont_idcont),
                'cove_dsbody' => $validated['body'],
            ]);
        }

        $content->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('contents.index')->with('success', 'Content updated successfully.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $content->delete();
        return redirect()->route('contents.index')->with('success', 'Content deleted successfully.');
    }

    public function publish(Content $content): RedirectResponse
    {
        $content->publish();
        return back()->with('success', 'Content published.');
    }

    public function archive(Content $content): RedirectResponse
    {
        $content->archive();
        return back()->with('success', 'Content archived.');
    }

    private function categoriesForSelect(): array
    {
        return Category::orderBy('cate_nmname')->get()->map(fn ($c) => [
            'id'   => $c->cate_idcate,
            'name' => $c->cate_nmname,
        ])->all();
    }
}
