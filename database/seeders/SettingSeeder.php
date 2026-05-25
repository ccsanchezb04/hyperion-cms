<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Settings generales
        $generalSettings = [
            'site_name' => 'Hyperion CMS',
            'site_description' => 'A powerful content management system built with Laravel and Vue.js',
            'site_timezone' => 'UTC',
            'site_locale' => 'en',
            'site_date_format' => 'Y-m-d',
            'site_time_format' => 'H:i',
            'site_active' => true,
        ];

        foreach ($generalSettings as $key => $value) {
            Setting::firstOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'sett_nmgrou' => Setting::GROUP_GENERAL,
                ]
            );
        }

        // Settings SEO
        $seoSettings = [
            'seo_title_separator' => ' - ',
            'seo_meta_description_length' => 160,
            'seo_auto_generate' => true,
            'seo_og_tags' => true,
            'seo_twitter_cards' => true,
            'seo_structured_data' => true,
        ];

        foreach ($seoSettings as $key => $value) {
            Setting::firstOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'sett_nmgrou' => Setting::GROUP_SEO,
                ]
            );
        }

        // Settings Media
        $mediaSettings = [
            'media_max_upload_size' => 10240, // 10MB in KB
            'media_allowed_image_types' => 'jpeg,png,webp,gif,svg',
            'media_allowed_video_types' => 'mp4,webm',
            'media_allowed_document_types' => 'pdf',
            'media_image_quality' => 85,
            'media_thumbnail_size' => '300x300',
            'media_enable_watermark' => false,
        ];

        foreach ($mediaSettings as $key => $value) {
            Setting::firstOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'sett_nmgrou' => Setting::GROUP_MEDIA,
                ]
            );
        }

        // Settings Mail
        $mailSettings = [
            'mail_from_address' => 'noreply@hyperion.local',
            'mail_from_name' => 'Hyperion CMS',
            'mail_queue_enabled' => true,
            'mail_notifications_enabled' => true,
        ];

        foreach ($mailSettings as $key => $value) {
            Setting::firstOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'sett_nmgrou' => Setting::GROUP_MAIL,
                ]
            );
        }

        $this->command->info('✅ Settings seeded successfully');
    }
}
