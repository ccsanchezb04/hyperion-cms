<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida el payload del /admin/seo update. El FE envía:
 *   { tab: 'general'|..., values: { 'site.seo.description': '...', ... } }
 *
 * - tab indica el grupo lógico a actualizar
 * - values es un mapa flat clave-valor (las claves contienen puntos, son
 *   identificadores del setting, NO notación nested)
 *
 * La whitelist por tab está en allowedKeysForTab(); el controller filtra los
 * values al subconjunto permitido y persiste con Setting::setValue.
 */
class UpdateSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tab'    => ['required', Rule::in(['general', 'og', 'schema', 'integrations', 'robots'])],
            'values' => ['required', 'array'],
        ];
    }

    /**
     * Claves permitidas por tab. Sólo estas se persisten; el resto se ignora.
     *
     * @return array<int, string>
     */
    public function allowedKeysForTab(): array
    {
        return match ($this->input('tab')) {
            'general' => [
                'site.seo.title_template',
                'site.seo.description',
                'site.seo.keywords',
                'site.seo.canonical_host',
                'site.seo.locale',
                'site.seo.robots',
                'site.seo.schema_type',
            ],
            'og' => [
                'site.seo.og_image',
                'site.seo.og_description',
                'site.seo.twitter_handle',
            ],
            'schema' => [
                'site.organization.name',
                'site.organization.legal_code',
                'site.organization.phone',
                'site.organization.email',
                'site.organization.opening_hours',
                'site.organization.address.street',
                'site.organization.address.locality',
                'site.organization.address.region',
                'site.organization.address.country',
                'site.organization.address.postal_code',
                'site.organization.social.facebook',
                'site.organization.social.instagram',
                'site.organization.social.linkedin',
                'site.organization.social.tiktok',
            ],
            'integrations' => [
                'site.integrations.gsc_verification',
                'site.integrations.gtm_id',
                'site.integrations.ga4_measurement_id',
                'site.integrations.fb_pixel_id',
            ],
            'robots' => [
                'site.seo.robots',
            ],
            default => [],
        };
    }

    /**
     * Grupo de Setting donde caen las claves de este tab.
     */
    public function settingGroup(): string
    {
        return match ($this->input('tab')) {
            'general', 'og', 'robots' => 'seo',
            'schema'                  => 'organization',
            'integrations'            => 'integrations',
            default                   => 'seo',
        };
    }
}
