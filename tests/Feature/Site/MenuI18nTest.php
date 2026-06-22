<?php

namespace Tests\Feature\Site;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use App\Models\User;
use App\Services\LocaleManager;
use App\Services\SiteContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Multi-idioma del menú principal (navbar).
 *   - Migration: hycms_menu_item_translations existe
 *   - SiteContentService::mainMenu devuelve labels traducidos cuando hay
 *     translation para el locale activo; cae al default si no
 *   - Admin: storeItem/updateItem persisten translations y empty borra
 */
class MenuI18nTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Menu $siteMain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $this->siteMain = Menu::where('menu_cdslug', 'site-main')->first();
        Cache::flush();
    }

    public function test_migration_creates_menu_item_translations_table(): void
    {
        $this->assertTrue(
            \Schema::hasTable('hycms_menu_item_translations'),
            'hycms_menu_item_translations debe existir'
        );
    }

    public function test_main_menu_returns_default_labels_in_es(): void
    {
        $menu = app(SiteContentService::class)->mainMenu();

        $labels = array_column($menu, 'label');
        $this->assertContains('Inicio', $labels);
        $this->assertContains('Soluciones', $labels);
    }

    public function test_main_menu_returns_translated_labels_when_locale_en(): void
    {
        $inicio = $this->siteMain->items()->where('mnit_nmlabe', 'Inicio')->first();
        $soluciones = $this->siteMain->items()->where('mnit_nmlabe', 'Soluciones')->first();

        MenuItemTranslation::create([
            'mitr_idmnit' => $inicio->mnit_idmnit,
            'mitr_cdlang' => 'en',
            'mitr_nmlabe' => 'Home',
        ]);
        MenuItemTranslation::create([
            'mitr_idmnit' => $soluciones->mnit_idmnit,
            'mitr_cdlang' => 'en',
            'mitr_nmlabe' => 'Solutions',
        ]);
        SiteContentService::flush();

        app(LocaleManager::class)->setCurrent('en');
        $menu = app(SiteContentService::class)->mainMenu();
        $labels = array_column($menu, 'label');

        $this->assertContains('Home', $labels);
        $this->assertContains('Solutions', $labels);
        // El ES default no debería aparecer en el listado EN
        $this->assertNotContains('Inicio', $labels);
    }

    public function test_main_menu_falls_back_to_default_when_translation_missing(): void
    {
        // Solo traducir uno; el resto debe caer al default Spanish
        $inicio = $this->siteMain->items()->where('mnit_nmlabe', 'Inicio')->first();
        MenuItemTranslation::create([
            'mitr_idmnit' => $inicio->mnit_idmnit,
            'mitr_cdlang' => 'en',
            'mitr_nmlabe' => 'Home',
        ]);
        SiteContentService::flush();

        app(LocaleManager::class)->setCurrent('en');
        $menu = app(SiteContentService::class)->mainMenu();
        $labels = array_column($menu, 'label');

        $this->assertContains('Home', $labels);
        // Otros items sin translation: cae al Spanish
        $this->assertContains('Nosotros', $labels);
    }

    public function test_admin_can_save_item_translations_on_create(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->post("/admin/menus/{$this->siteMain->menu_idmenu}/items", [
                'title' => 'Servicios',
                'type' => 'url',
                'link' => '/servicios',
                'translations' => ['en' => 'Services'],
            ]);

        $response->assertRedirect('/admin/menus');
        $item = $this->siteMain->items()->where('mnit_nmlabe', 'Servicios')->first();
        $this->assertNotNull($item);
        $this->assertDatabaseHas('hycms_menu_item_translations', [
            'mitr_idmnit' => $item->mnit_idmnit,
            'mitr_cdlang' => 'en',
            'mitr_nmlabe' => 'Services',
        ]);
    }

    public function test_admin_update_with_empty_translation_deletes_row(): void
    {
        $item = $this->siteMain->items()->first();
        MenuItemTranslation::create([
            'mitr_idmnit' => $item->mnit_idmnit,
            'mitr_cdlang' => 'en',
            'mitr_nmlabe' => 'Stale',
        ]);

        $this->actingAs($this->admin)
            ->from('/admin/menus')
            ->put("/admin/menus/{$this->siteMain->menu_idmenu}/items/{$item->mnit_idmnit}", [
                'title' => $item->mnit_nmlabe,
                'type' => 'url',
                'link' => '/',
                'translations' => ['en' => ''],
            ]);

        $this->assertDatabaseMissing('hycms_menu_item_translations', [
            'mitr_idmnit' => $item->mnit_idmnit,
            'mitr_cdlang' => 'en',
        ]);
    }

    public function test_index_exposes_translatable_locales(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/menus')
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p
                ->where('translatableLocales.0', 'en')
                ->etc()
            );
    }
}
