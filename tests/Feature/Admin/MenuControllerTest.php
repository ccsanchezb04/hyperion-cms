<?php

namespace Tests\Feature\Admin;

use App\Models\Content;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->menu = Menu::where('menu_cdslug', 'site-main')->first();
    }

    public function test_index_includes_contents_and_categories(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/menus');

        $response->assertStatus(200);
    }

    public function test_can_update_existing_item(): void
    {
        $item = $this->menu->items()->first();

        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->put("/admin/menus/{$this->menu->menu_idmenu}/items/{$item->mnit_idmnit}", [
                'title' => 'Inicio renombrado',
                'type' => 'url',
                'link' => '/',
                'parent_id' => null,
            ]);

        $response->assertRedirect('/admin/menus');
        $this->assertSame('Inicio renombrado', $item->fresh()->mnit_nmlabe);
    }

    public function test_can_create_item_pointing_to_content_via_ref_id(): void
    {
        $solution = Content::published()
            ->whereHas('categories.parent', fn ($q) => $q->where('cate_cdslug', 'soluciones'))
            ->first();

        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/items", [
                'title' => 'Seguro destacado',
                'type' => 'content',
                'ref_id' => $solution->cont_idcont,
            ]);

        $response->assertRedirect('/admin/menus');
        $this->assertDatabaseHas('hycms_menu_items', [
            'mnit_nmlabe' => 'Seguro destacado',
            'mnit_cdtype' => 'content',
            'mnit_idrefi' => $solution->cont_idcont,
        ]);
    }

    public function test_content_type_requires_ref_id(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/items", [
                'title' => 'Sin contenido',
                'type' => 'content',
            ]);

        $response->assertRedirect('/admin/menus');
        $response->assertSessionHasErrors('ref_id');
    }

    public function test_url_type_requires_link(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/items", [
                'title' => 'Sin URL',
                'type' => 'url',
            ]);

        $response->assertRedirect('/admin/menus');
        $response->assertSessionHasErrors('link');
    }

    public function test_reorder_updates_order_and_parent(): void
    {
        $items = $this->menu->items()->orderBy('mnit_nrorde')->get();
        $first = $items[0];
        $second = $items[1];

        // Anidar el segundo bajo el primero, swap del orden de los demás
        $payload = [];
        $payload[] = ['id' => $first->mnit_idmnit, 'order' => 1, 'parent_id' => null];
        $payload[] = ['id' => $second->mnit_idmnit, 'order' => 1, 'parent_id' => $first->mnit_idmnit];
        foreach ($items->slice(2) as $idx => $item) {
            $payload[] = ['id' => $item->mnit_idmnit, 'order' => $idx + 2, 'parent_id' => null];
        }

        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/reorder", ['items' => $payload]);

        $response->assertRedirect('/admin/menus');
        $this->assertSame($first->mnit_idmnit, $second->fresh()->mnit_idpare);
    }

    public function test_reorder_prevents_loops(): void
    {
        $items = $this->menu->items()->orderBy('mnit_nrorde')->get();
        $a = $items[0];
        $b = $items[1];

        // Primero hacer A padre de B
        $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/reorder", [
                'items' => [
                    ['id' => $a->mnit_idmnit, 'order' => 1, 'parent_id' => null],
                    ['id' => $b->mnit_idmnit, 'order' => 1, 'parent_id' => $a->mnit_idmnit],
                ],
            ]);

        // Ahora intentar hacer B padre de A (crearía un loop)
        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->menu->menu_idmenu}/reorder", [
                'items' => [
                    ['id' => $a->mnit_idmnit, 'order' => 1, 'parent_id' => $b->mnit_idmnit],
                    ['id' => $b->mnit_idmnit, 'order' => 2, 'parent_id' => $a->mnit_idmnit],
                ],
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_update_rejects_self_as_parent(): void
    {
        $item = $this->menu->items()->first();

        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->put("/admin/menus/{$this->menu->menu_idmenu}/items/{$item->mnit_idmnit}", [
                'title' => $item->mnit_nmlabe,
                'type' => 'url',
                'link' => '/',
                'parent_id' => $item->mnit_idmnit,
            ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_update_rejects_descendant_as_parent(): void
    {
        $items = $this->menu->items()->orderBy('mnit_nrorde')->get();
        $a = $items[0];
        $b = $items[1];

        // Hacer B hijo de A
        $b->update(['mnit_idpare' => $a->mnit_idmnit]);

        // Intentar editar A con parent=B (B es descendiente de A)
        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->put("/admin/menus/{$this->menu->menu_idmenu}/items/{$a->mnit_idmnit}", [
                'title' => $a->mnit_nmlabe,
                'type' => 'url',
                'link' => '/',
                'parent_id' => $b->mnit_idmnit,
            ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_destroy_item_reparents_children(): void
    {
        $items = $this->menu->items()->orderBy('mnit_nrorde')->get();
        $parent = $items[0];
        $child = $items[1];

        $child->update(['mnit_idpare' => $parent->mnit_idmnit]);

        $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->delete("/admin/menus/items/{$parent->mnit_idmnit}");

        $this->assertDatabaseMissing('hycms_menu_items', ['mnit_idmnit' => $parent->mnit_idmnit]);
        $this->assertNull($child->fresh()->mnit_idpare);
    }
}
