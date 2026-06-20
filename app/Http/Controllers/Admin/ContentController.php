<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentSeo;
use App\Models\ContentTranslation;
use App\Models\ContentVersion;
use App\Models\Setting;
use App\Services\LocaleManager;
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
            'categories'       => $this->categoriesForSelect(),
            'canonicalHost'    => Setting::getValue('site.seo.canonical_host', ''),
            'translatableLocales' => $this->translatableLocales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->baseRules(), [
            'slug' => ['required', 'string', 'max:200', 'unique:hycms_contents,cont_cdslug'],
        ], $this->seoRules(), $this->translationRules()));

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

        $this->saveSeo($content, $validated['seo'] ?? []);
        $this->saveTranslations($content, $validated['translations'] ?? []);

        return redirect()->route('contents.index')->with('success', 'Content created successfully.');
    }

    public function edit(Content $content): Response
    {
        $content->load(['categories', 'latestVersion', 'seo', 'translations']);

        return Inertia::render('Contents/Edit', [
            'content' => [
                'id'           => $content->cont_idcont,
                'title'        => $content->cont_nmtitl,
                'slug'         => $content->cont_cdslug,
                'type'         => $content->cont_cdtype,
                'status'       => $content->cont_cdstat,
                'body'         => $content->latestVersion?->cove_dsbody ?? '',
                'categories'   => $content->categories->pluck('cate_idcate')->all(),
                'seo'          => $this->serializeSeo($content->seo),
                'translations' => $this->serializeTranslations($content),
            ],
            'categories'          => $this->categoriesForSelect(),
            'canonicalHost'       => Setting::getValue('site.seo.canonical_host', ''),
            'translatableLocales' => $this->translatableLocales(),
        ]);
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->baseRules(), [
            'slug' => [
                'required', 'string', 'max:200',
                Rule::unique('hycms_contents', 'cont_cdslug')->ignore($content->cont_idcont, 'cont_idcont'),
            ],
        ], $this->seoRules(), $this->translationRules()));

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

        $this->saveSeo($content, $validated['seo'] ?? []);
        $this->saveTranslations($content, $validated['translations'] ?? []);

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

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function baseRules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:200'],
            'type'         => ['required', Rule::in([Content::TYPE_POST, Content::TYPE_PAGE, Content::TYPE_CUSTOM])],
            'status'       => ['required', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'body'         => ['nullable', 'string'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:hycms_categories,cate_idcate'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function seoRules(): array
    {
        return [
            'seo'                  => ['nullable', 'array'],
            'seo.meta_title'       => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
            'seo.og_image'         => ['nullable', 'string', 'max:500'],
            'seo.canonical'        => ['nullable', 'string', 'url', 'max:500'],
            'seo.noindex'          => ['nullable', 'boolean'],
        ];
    }

    /**
     * Upsert ContentSeo row. Si todos los campos vienen vacíos, elimina la
     * fila (no contamina la tabla con registros vacíos).
     *
     * @param array<string, mixed> $payload
     */
    protected function saveSeo(Content $content, array $payload): void
    {
        $values = [
            'cose_nmtitl' => $payload['meta_title'] ?? null,
            'cose_dsdesc' => $payload['meta_description'] ?? null,
            'cose_dsogim' => $payload['og_image'] ?? null,
            'cose_cdcano' => $payload['canonical'] ?? null,
            'cose_bonoix' => (bool) ($payload['noindex'] ?? false),
        ];

        $allEmpty = empty($values['cose_nmtitl'])
            && empty($values['cose_dsdesc'])
            && empty($values['cose_dsogim'])
            && empty($values['cose_cdcano'])
            && ! $values['cose_bonoix'];

        if ($allEmpty) {
            ContentSeo::where('cose_idcont', $content->cont_idcont)->delete();
            return;
        }

        ContentSeo::updateOrCreate(
            ['cose_idcont' => $content->cont_idcont],
            $values
        );
    }

    /**
     * Devuelve la forma que consume el formulario SEO en el frontend.
     */
    protected function serializeSeo(?ContentSeo $seo): array
    {
        return [
            'meta_title'       => $seo?->cose_nmtitl ?? '',
            'meta_description' => $seo?->cose_dsdesc ?? '',
            'og_image'         => $seo?->cose_dsogim ?? '',
            'canonical'        => $seo?->cose_cdcano ?? '',
            'noindex'          => (bool) ($seo?->cose_bonoix ?? false),
        ];
    }

    /**
     * Reglas de validación para las traducciones. Cada locale traducible
     * acepta title y body opcionales.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function translationRules(): array
    {
        $rules = [
            'translations' => ['nullable', 'array'],
        ];
        foreach ($this->translatableLocales() as $lang) {
            $rules["translations.{$lang}"]          = ['nullable', 'array'];
            $rules["translations.{$lang}.title"]    = ['nullable', 'string', 'max:255'];
            $rules["translations.{$lang}.body"]     = ['nullable', 'string'];
        }
        return $rules;
    }

    /**
     * Persiste las traducciones. Si para un idioma ambos campos vienen
     * vacíos, elimina la fila correspondiente (no contamina la tabla con
     * registros placeholder).
     *
     * @param array<string, array{title?: ?string, body?: ?string}> $payload
     */
    protected function saveTranslations(Content $content, array $payload): void
    {
        foreach ($this->translatableLocales() as $lang) {
            $values = $payload[$lang] ?? [];
            $title = $values['title'] ?? null;
            $body = $values['body'] ?? null;

            if (empty($title) && empty($body)) {
                ContentTranslation::where('cotr_idcont', $content->cont_idcont)
                    ->where('cotr_cdlang', $lang)
                    ->delete();
                continue;
            }

            ContentTranslation::updateOrCreate(
                ['cotr_idcont' => $content->cont_idcont, 'cotr_cdlang' => $lang],
                ['cotr_nmtitl' => $title, 'cotr_dsbody' => $body],
            );
        }
    }

    /**
     * @return array<string, array{title: string, body: string}>
     */
    protected function serializeTranslations(Content $content): array
    {
        $result = [];
        foreach ($this->translatableLocales() as $lang) {
            $t = $content->translation($lang);
            $result[$lang] = [
                'title' => $t?->cotr_nmtitl ?? '',
                'body'  => $t?->cotr_dsbody ?? '',
            ];
        }
        return $result;
    }

    /**
     * Locales traducibles = soportados menos el default. El default usa los
     * campos nativos del Content (cont_nmtitl / cove_dsbody).
     *
     * @return array<int, string>
     */
    protected function translatableLocales(): array
    {
        $locale = app(LocaleManager::class);
        return array_values(array_diff($locale->supported(), [$locale->default()]));
    }

    private function categoriesForSelect(): array
    {
        return Category::orderBy('cate_nmname')->get()->map(fn ($c) => [
            'id'   => $c->cate_idcate,
            'name' => $c->cate_nmname,
        ])->all();
    }
}
