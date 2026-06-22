<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use App\Services\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $menus = Menu::with([
            'items' => fn ($q) => $q->orderBy('mnit_nrorde')->with('translations'),
        ])->get()->map(function (Menu $m) {
            return [
                'id'    => $m->menu_idmenu,
                'name'  => $m->menu_nmname,
                'slug'  => $m->menu_cdslug,
                'is_site_main' => $m->menu_cdslug === 'site-main',
                'items' => $m->items->map(fn (MenuItem $it) => [
                    'id'        => $it->mnit_idmnit,
                    'title'     => $it->mnit_nmlabe,
                    'type'      => $it->mnit_cdtype,
                    'link'      => $it->mnit_dsurll,
                    'ref_id'    => $it->mnit_idrefi,
                    'parent_id' => $it->mnit_idpare,
                    'order'     => $it->mnit_nrorde,
                    'translations' => $it->translations
                        ->mapWithKeys(fn ($t) => [$t->mitr_cdlang => $t->mitr_nmlabe])
                        ->all(),
                ])->all(),
            ];
        });

        $contents = Content::published()
            ->orderBy('cont_nmtitl')
            ->get(['cont_idcont', 'cont_nmtitl', 'cont_cdslug'])
            ->map(fn (Content $c) => [
                'id' => $c->cont_idcont,
                'title' => $c->cont_nmtitl,
                'slug' => $c->cont_cdslug,
            ]);

        $categories = Category::orderBy('cate_nmname')
            ->get(['cate_idcate', 'cate_nmname', 'cate_cdslug'])
            ->map(fn (Category $c) => [
                'id' => $c->cate_idcate,
                'name' => $c->cate_nmname,
                'slug' => $c->cate_cdslug,
            ]);

        return Inertia::render('Menus/Index', [
            'menus' => $menus,
            'contents' => $contents,
            'categories' => $categories,
            'translatableLocales' => $this->translatableLocales(),
        ]);
    }

    /**
     * Idiomas traducibles del navbar = soportados menos default.
     *
     * @return array<int, string>
     */
    protected function translatableLocales(): array
    {
        $locale = app(LocaleManager::class);
        return array_values(array_diff($locale->supported(), [$locale->default()]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:hycms_menus,menu_cdslug'],
        ]);

        Menu::create([
            'menu_nmname' => $validated['name'],
            'menu_cdslug' => $validated['slug'],
        ]);

        return back()->with('success', 'Menu created successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->items()->delete();
        $menu->delete();

        return back()->with('success', 'Menu deleted successfully.');
    }

    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validateItem($request, $menu);

        $maxOrder = (int) $menu->items()
            ->where('mnit_idpare', $data['parent_id'] ?? null)
            ->max('mnit_nrorde');

        $item = MenuItem::create([
            'mnit_idmenu' => $menu->menu_idmenu,
            'mnit_nmlabe' => $data['title'],
            'mnit_cdtype' => $data['type'],
            'mnit_dsurll' => $data['link'] ?? null,
            'mnit_idrefi' => $data['ref_id'] ?? null,
            'mnit_idpare' => $data['parent_id'] ?? null,
            'mnit_nrorde' => $maxOrder + 1,
        ]);

        $this->saveItemTranslations($item, $data['translations'] ?? []);

        return back()->with('success', 'Menu item added.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->mnit_idmenu !== $menu->menu_idmenu) {
            abort(404);
        }

        $data = $this->validateItem($request, $menu, $menuItem);

        $menuItem->update([
            'mnit_nmlabe' => $data['title'],
            'mnit_cdtype' => $data['type'],
            'mnit_dsurll' => $data['link'] ?? null,
            'mnit_idrefi' => $data['ref_id'] ?? null,
            'mnit_idpare' => $data['parent_id'] ?? null,
        ]);

        $this->saveItemTranslations($menuItem, $data['translations'] ?? []);

        return back()->with('success', 'Menu item updated.');
    }

    /**
     * Upsert translations per language. Empty label deletes the row for that
     * language (no contamina la tabla con placeholders).
     *
     * @param array<string, ?string> $payload  ['en' => 'Home', ...]
     */
    protected function saveItemTranslations(MenuItem $item, array $payload): void
    {
        foreach ($this->translatableLocales() as $lang) {
            $label = $payload[$lang] ?? null;
            if (empty($label)) {
                MenuItemTranslation::where('mitr_idmnit', $item->mnit_idmnit)
                    ->where('mitr_cdlang', $lang)
                    ->delete();
                continue;
            }
            MenuItemTranslation::updateOrCreate(
                ['mitr_idmnit' => $item->mnit_idmnit, 'mitr_cdlang' => $lang],
                ['mitr_nmlabe' => $label],
            );
        }
    }

    public function destroyItem(MenuItem $menuItem): RedirectResponse
    {
        // Reparent children to the deleted item's parent so they don't quedar huérfanos.
        MenuItem::where('mnit_idpare', $menuItem->mnit_idmnit)
            ->update(['mnit_idpare' => $menuItem->mnit_idpare]);

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted.');
    }

    /**
     * Bulk reorder: actualiza order + parent de varios ítems en una sola pasada.
     *
     * Body: { items: [{ id, order, parent_id }, ...] }
     */
    public function reorder(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.id'         => ['required', 'integer'],
            'items.*.order'      => ['required', 'integer', 'min:0'],
            'items.*.parent_id'  => ['nullable', 'integer'],
        ]);

        $itemIds = collect($validated['items'])->pluck('id')->all();
        $belongs = MenuItem::whereIn('mnit_idmnit', $itemIds)
            ->where('mnit_idmenu', $menu->menu_idmenu)
            ->pluck('mnit_idmnit')
            ->all();

        if (count($belongs) !== count($itemIds)) {
            throw ValidationException::withMessages(['items' => 'Algún ítem no pertenece al menú.']);
        }

        // Validar parent_ids
        $allMenuItemIds = $menu->items()->pluck('mnit_idmnit')->all();
        foreach ($validated['items'] as $entry) {
            $parentId = $entry['parent_id'] ?? null;
            if ($parentId !== null) {
                if ($parentId === $entry['id']) {
                    throw ValidationException::withMessages(['items' => 'Un ítem no puede ser su propio padre.']);
                }
                if (! in_array($parentId, $allMenuItemIds, true)) {
                    throw ValidationException::withMessages(['items' => 'Parent inválido.']);
                }
            }
        }

        DB::transaction(function () use ($validated, $menu) {
            foreach ($validated['items'] as $entry) {
                MenuItem::where('mnit_idmnit', $entry['id'])
                    ->where('mnit_idmenu', $menu->menu_idmenu)
                    ->update([
                        'mnit_nrorde' => $entry['order'],
                        'mnit_idpare' => $entry['parent_id'] ?? null,
                    ]);
            }

            // Loop detection: tras escribir, verificamos que ningún ítem sea su propio ancestro.
            $items = $menu->items()->get(['mnit_idmnit', 'mnit_idpare'])->keyBy('mnit_idmnit');
            foreach ($items as $id => $item) {
                $this->ensureNoLoop($id, $items);
            }
        });

        return back()->with('success', 'Menu reordered.');
    }

    /**
     * @return array{title:string,type:string,link:?string,ref_id:?int,parent_id:?int,translations?:array<string,?string>}
     */
    protected function validateItem(Request $request, Menu $menu, ?MenuItem $editing = null): array
    {
        $rules = [
            'title'        => ['required', 'string', 'max:120'],
            'type'         => ['required', Rule::in([MenuItem::TYPE_URL, MenuItem::TYPE_CONTENT, MenuItem::TYPE_CATEGORY])],
            'link'         => ['nullable', 'string', 'max:255'],
            'ref_id'       => ['nullable', 'integer'],
            'parent_id'    => ['nullable', 'integer'],
            'translations' => ['nullable', 'array'],
        ];
        foreach ($this->translatableLocales() as $lang) {
            $rules["translations.{$lang}"] = ['nullable', 'string', 'max:120'];
        }
        $data = $request->validate($rules);

        // type=url requiere link; type=content/category requiere ref_id
        if ($data['type'] === MenuItem::TYPE_URL && empty($data['link'])) {
            throw ValidationException::withMessages(['link' => 'La URL es obligatoria para items tipo URL.']);
        }
        if (in_array($data['type'], [MenuItem::TYPE_CONTENT, MenuItem::TYPE_CATEGORY], true) && empty($data['ref_id'])) {
            throw ValidationException::withMessages(['ref_id' => 'Debes elegir un contenido o categoría.']);
        }

        // parent_id debe pertenecer al mismo menú
        if (! empty($data['parent_id'])) {
            if ($editing && $data['parent_id'] === $editing->mnit_idmnit) {
                throw ValidationException::withMessages(['parent_id' => 'Un ítem no puede ser su propio padre.']);
            }

            $exists = MenuItem::where('mnit_idmnit', $data['parent_id'])
                ->where('mnit_idmenu', $menu->menu_idmenu)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages(['parent_id' => 'Parent inválido.']);
            }

            // Loop detection: si estamos editando, parent no puede ser descendiente del item
            if ($editing) {
                $descendantIds = $this->collectDescendants($editing->mnit_idmnit, $menu);
                if (in_array($data['parent_id'], $descendantIds, true)) {
                    throw ValidationException::withMessages(['parent_id' => 'El parent no puede ser descendiente del ítem.']);
                }
            }
        }

        return $data;
    }

    /**
     * IDs de todos los descendientes (hijos, nietos, ...) de un ítem.
     * @return array<int>
     */
    protected function collectDescendants(int $itemId, Menu $menu): array
    {
        $items = $menu->items()->get(['mnit_idmnit', 'mnit_idpare']);
        $descendants = [];
        $stack = [$itemId];

        while ($stack) {
            $current = array_pop($stack);
            foreach ($items as $it) {
                if ($it->mnit_idpare === $current) {
                    $descendants[] = $it->mnit_idmnit;
                    $stack[] = $it->mnit_idmnit;
                }
            }
        }

        return $descendants;
    }

    /**
     * Sigue la cadena de parents desde $id; lanza si vuelve a $id (loop).
     *
     * @param array<int, MenuItem> $items keyed by id
     */
    protected function ensureNoLoop(int $startId, $items): void
    {
        $cursor = $items[$startId]->mnit_idpare;
        $depth = 0;
        while ($cursor !== null) {
            if ($cursor === $startId) {
                throw ValidationException::withMessages(['items' => 'Loop detectado en la jerarquía del menú.']);
            }
            if ($depth++ > 100) {
                throw ValidationException::withMessages(['items' => 'Profundidad del menú excesiva.']);
            }
            $cursor = $items[$cursor]?->mnit_idpare;
        }
    }
}
