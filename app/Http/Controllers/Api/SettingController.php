<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display all settings grouped by category.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        // Filtrar por grupo si se especifica
        if ($request->has('group')) {
            $query->group($request->input('group'));
        }

        $settings = $query->get();

        // Agrupar por grupo
        $grouped = $settings->groupBy('sett_nmgrou');

        $formatted = $grouped->mapWithKeys(function ($items, $group) {
            return [$group => $items->pluck('sett_dsvalu', 'sett_cdkeys')];
        });

        return response()->json([
            'data' => $formatted,
        ]);
    }

    /**
     * Display settings for a specific group.
     */
    public function showGroup(string $group): JsonResponse
    {
        $settings = Setting::group($group)->get();

        return response()->json([
            'data' => $settings->pluck('sett_dsvalu', 'sett_cdkeys'),
            'group' => $group,
        ]);
    }

    /**
     * Get a specific setting value.
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('sett_cdkeys', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'key' => $setting->sett_cdkeys,
                'value' => $setting->sett_dsvalu,
                'group' => $setting->sett_nmgrou,
                'updated_at' => $setting->sett_dtupda,
            ],
        ]);
    }

    /**
     * Update multiple settings.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $settings = $request->input('settings', []);
        $group = $request->input('group', Setting::GROUP_GENERAL);

        $updated = [];

        foreach ($settings as $key => $value) {
            $setting = Setting::updateOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => $value,
                    'sett_nmgrou' => $group,
                ]
            );
            $updated[$key] = $setting->sett_dsvalu;
        }

        return response()->json([
            'data' => $updated,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Update a specific setting.
     */
    public function updateSetting(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => 'required',
            'group' => 'nullable|string|in:general,seo,media,mail',
        ]);

        $setting = Setting::updateOrCreate(
            ['sett_cdkeys' => $key],
            [
                'sett_dsvalu' => $request->input('value'),
                'sett_nmgrou' => $request->input('group', Setting::GROUP_GENERAL),
            ]
        );

        return response()->json([
            'data' => [
                'key' => $setting->sett_cdkeys,
                'value' => $setting->sett_dsvalu,
                'group' => $setting->sett_nmgrou,
                'updated_at' => $setting->sett_dtupda,
            ],
            'message' => 'Setting updated successfully',
        ]);
    }

    /**
     * Delete a specific setting.
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::where('sett_cdkeys', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Reset settings to default values for a group.
     */
    public function resetGroup(string $group): JsonResponse
    {
        // Eliminar todos los settings del grupo
        Setting::group($group)->delete();

        // Recrear settings por defecto según el grupo
        $defaultSettings = $this->getDefaultSettingsForGroup($group);

        foreach ($defaultSettings as $key => $data) {
            Setting::create([
                'sett_cdkeys' => $key,
                'sett_dsvalu' => $data['value'],
                'sett_nmgrou' => $group,
            ]);
        }

        return response()->json([
            'message' => 'Settings reset to defaults successfully',
        ]);
    }

    /**
     * Get default settings for a specific group.
     */
    protected function getDefaultSettingsForGroup(string $group): array
    {
        $defaults = [
            Setting::GROUP_GENERAL => [
                'site_name' => ['value' => 'Hyperion CMS'],
                'site_description' => ['value' => 'A powerful content management system'],
                'site_timezone' => ['value' => 'UTC'],
                'site_locale' => ['value' => 'en'],
                'site_date_format' => ['value' => 'Y-m-d'],
                'site_time_format' => ['value' => 'H:i'],
            ],
            Setting::GROUP_SEO => [
                'seo_title_separator' => ['value' => ' - '],
                'seo_meta_description_length' => ['value' => 160],
                'seo_auto_generate' => ['value' => true],
                'seo_og_tags' => ['value' => true],
                'seo_twitter_cards' => ['value' => true],
            ],
            Setting::GROUP_MEDIA => [
                'media_max_upload_size' => ['value' => 10240], // 10MB in KB
                'media_allowed_image_types' => ['value' => 'jpeg,png,webp,gif,svg'],
                'media_allowed_video_types' => ['value' => 'mp4,webm'],
                'media_allowed_document_types' => ['value' => 'pdf'],
                'media_image_quality' => ['value' => 85],
                'media_thumbnail_size' => ['value' => '300x300'],
            ],
            Setting::GROUP_MAIL => [
                'mail_from_address' => ['value' => 'noreply@example.com'],
                'mail_from_name' => ['value' => 'Hyperion CMS'],
                'mail_queue_enabled' => ['value' => true],
            ],
        ];

        return $defaults[$group] ?? [];
    }
}
