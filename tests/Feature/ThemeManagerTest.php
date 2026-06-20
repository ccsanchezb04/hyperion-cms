<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_discover_returns_installed_themes(): void
    {
        $themes = app(ThemeManager::class)->discover();

        $this->assertArrayHasKey('juanfer', $themes);
        $this->assertArrayHasKey('default', $themes);
        $this->assertSame('JuanFer Seguros', $themes['juanfer']['name']);
        $this->assertSame('Default', $themes['default']['name']);
    }

    public function test_active_slug_falls_back_to_config_when_no_setting(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertNull(Setting::getValue(ThemeManager::SETTING_KEY));
        $this->assertSame(config('hyperion.theme', 'juanfer'), $manager->activeSlug());
    }

    public function test_set_active_persists_in_settings(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertTrue($manager->setActive('default'));
        $manager->flushDiscoveryCache();

        $this->assertSame('default', Setting::getValue(ThemeManager::SETTING_KEY));
        $this->assertSame('default', $manager->activeSlug());
    }

    public function test_set_active_rejects_unknown_slug(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertFalse($manager->setActive('ghost'));
        $this->assertNull(Setting::getValue(ThemeManager::SETTING_KEY));
    }

    public function test_active_slug_falls_back_when_stored_slug_is_orphan(): void
    {
        // Settings contiene un slug que ya no está en disco; debe caer al config/primero.
        Setting::setValue(ThemeManager::SETTING_KEY, 'removed-theme', ThemeManager::SETTING_GROUP);

        $manager = app(ThemeManager::class);
        $manager->flushDiscoveryCache();

        $this->assertContains($manager->activeSlug(), ['juanfer', 'default']);
        $this->assertNotSame('removed-theme', $manager->activeSlug());
    }

    public function test_vite_entry_reflects_active_theme(): void
    {
        $manager = app(ThemeManager::class);

        $manager->setActive('juanfer');
        $this->assertSame('resources/themes/juanfer/site.entry.ts', $manager->viteEntry());

        $manager->setActive('default');
        $this->assertSame('resources/themes/default/site.entry.ts', $manager->viteEntry());
    }
}
