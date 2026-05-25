<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Display a listing of menus.
     */
    public function index(): JsonResponse
    {
        $menus = Menu::with(['items' => function ($query) {
            $query->orderBy('mnit_nrorde');
        }])->get();

        return response()->json([
            'data' => $menus->map(function ($menu) {
                return [
                    'id' => $menu->menu_idmenu,
                    'name' => $menu->menu_nmname,
                    'slug' => $menu->menu_cdslug,
                    'items_count' => $menu->items->count(),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created menu.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = Menu::create([
            'menu_nmname' => $request->input('name'),
            'menu_cdslug' => $request->input('slug'),
        ]);

        return response()->json([
            'data' => [
                'id' => $menu->menu_idmenu,
                'name' => $menu->menu_nmname,
                'slug' => $menu->menu_cdslug,
            ],
            'message' => 'Menu created successfully',
        ], 201);
    }

    /**
     * Display the specified menu.
     */
    public function show(string $slug): JsonResponse
    {
        $menu = Menu::where('menu_cdslug', $slug)
            ->with(['rootItems' => function ($query) {
                $query->orderBy('mnit_nrorde');
            }])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $menu->menu_idmenu,
                'name' => $menu->menu_nmname,
                'slug' => $menu->menu_cdslug,
                'items' => $this->buildMenuTree($menu->rootItems),
            ],
        ]);
    }

    /**
     * Update the specified menu.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $menu->update([
            'menu_nmname' => $request->input('name', $menu->menu_nmname),
            'menu_cdslug' => $request->input('slug', $menu->menu_cdslug),
        ]);

        return response()->json([
            'data' => [
                'id' => $menu->menu_idmenu,
                'name' => $menu->menu_nmname,
                'slug' => $menu->menu_cdslug,
            ],
            'message' => 'Menu updated successfully',
        ]);
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        // Eliminar todos los items del menú
        $menu->items()->delete();

        // Eliminar el menú
        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }

    /**
     * Add a menu item to the specified menu.
     */
    public function addItem(StoreMenuItemRequest $request, Menu $menu): JsonResponse
    {
        // Determinar el orden
        $maxOrder = $menu->items()->max('mnit_nrorde') ?? 0;
        $order = $request->input('order', $maxOrder + 1);

        $menuItem = MenuItem::create([
            'mnit_idmenu' => $menu->menu_idmenu,
            'mnit_nmtitl' => $request->input('title'),
            'mnit_cdtype' => $request->input('type', 'url'),
            'mnit_dslink' => $request->input('link'),
            'mnit_idpare' => $request->input('parent_id'),
            'mnit_nrorde' => $order,
            'mnit_cdclss' => $request->input('css_class'),
            'mnit_nmtarg' => $request->input('target', '_self'),
            'mnit_cdenab' => $request->input('enabled', true),
        ]);

        return response()->json([
            'data' => $this->formatMenuItem($menuItem),
            'message' => 'Menu item added successfully',
        ], 201);
    }

    /**
     * Update a menu item.
     */
    public function updateItem(UpdateMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update([
            'mnit_nmtitl' => $request->input('title', $menuItem->mnit_nmtitl),
            'mnit_cdtype' => $request->input('type', $menuItem->mnit_cdtype),
            'mnit_dslink' => $request->input('link', $menuItem->mnit_dslink),
            'mnit_idpare' => $request->input('parent_id', $menuItem->mnit_idpare),
            'mnit_nrorde' => $request->input('order', $menuItem->mnit_nrorde),
            'mnit_cdclss' => $request->input('css_class', $menuItem->mnit_cdclss),
            'mnit_nmtarg' => $request->input('target', $menuItem->mnit_nmtarg),
            'mnit_cdenab' => $request->input('enabled', $menuItem->mnit_cdenab),
        ]);

        return response()->json([
            'data' => $this->formatMenuItem($menuItem),
            'message' => 'Menu item updated successfully',
        ]);
    }

    /**
     * Delete a menu item.
     */
    public function deleteItem(MenuItem $menuItem): JsonResponse
    {
        // Eliminar hijos recursivamente
        $this->deleteMenuItemChildren($menuItem);

        // Eliminar el item
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully',
        ]);
    }

    /**
     * Reorder menu items.
     */
    public function reorderItems(Request $request, Menu $menu): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order' => 'required|integer',
            'items.*.parent_id' => 'nullable|integer',
        ]);

        foreach ($request->input('items') as $itemData) {
            $menuItem = MenuItem::where('mnit_iditem', $itemData['id'])
                ->where('mnit_idmenu', $menu->menu_idmenu)
                ->first();

            if ($menuItem) {
                $menuItem->update([
                    'mnit_nrorde' => $itemData['order'],
                    'mnit_idpare' => $itemData['parent_id'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Menu items reordered successfully',
        ]);
    }

    /**
     * Build menu tree structure.
     */
    protected function buildMenuTree($items): array
    {
        return $items->map(function ($item) {
            $formatted = $this->formatMenuItem($item);
            
            if ($item->children) {
                $formatted['children'] = $this->buildMenuTree($item->children);
            }

            return $formatted;
        })->toArray();
    }

    /**
     * Format menu item for API response.
     */
    protected function formatMenuItem(MenuItem $item): array
    {
        return [
            'id' => $item->mnit_iditem,
            'title' => $item->mnit_nmtitl,
            'type' => $item->mnit_cdtype,
            'link' => $item->mnit_dslink,
            'parent_id' => $item->mnit_idpare,
            'order' => $item->mnit_nrorde,
            'css_class' => $item->mnit_cdclss,
            'target' => $item->mnit_nmtarg,
            'enabled' => $item->mnit_cdenab,
        ];
    }

    /**
     * Delete menu item children recursively.
     */
    protected function deleteMenuItemChildren(MenuItem $item): void
    {
        $children = $item->children;
        
        foreach ($children as $child) {
            $this->deleteMenuItemChildren($child);
            $child->delete();
        }
    }
}
