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
            'tab'    => ['required', Rule::in(['general', 'og', 'schema', 'integrations', 'robots', 'chrome', 'categories'])],
            'values' => ['required', 'array'],
            'lang'   => ['nullable', 'string', 'in:es,en'],
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
            'chrome' => [
                'site.heading',
                'site.tagline',
                'site.about.heading',
                'site.about.intro',
                'site.about.vision_title',
                'site.about.vision_lead',
                'site.about.vision_body',
                'site.about.mission_title',
                'site.about.mission_body',
                'site.solutions.heading',
                'site.solutions.cta_all',
                'site.testimonials.heading',
                'site.contact.heading',
                'site.footer.copy',
                'site.footer.address',
            ],
            'categories' => $this->categoryKeysWhitelist(),
            default => [],
        };
    }

    /**
     * Keys permitidas para el tab Categories: site.seo.category.{slug}.description
     * + site.seo.category.{slug}.og_image por cada categoría hija de
     * 'soluciones'. Computa la lista en runtime desde la BD para que no
     * haya que hardcodear los slugs.
     *
     * @return array<int, string>
     */
    protected function categoryKeysWhitelist(): array
    {
        $slugs = \App\Models\Category::whereHas(
            'parent',
            fn ($q) => $q->where('cate_cdslug', 'soluciones')
        )->pluck('cate_cdslug')->all();

        $keys = [];
        foreach ($slugs as $slug) {
            $keys[] = "site.seo.category.{$slug}.description";
            $keys[] = "site.seo.category.{$slug}.og_image";
        }
        return $keys;
    }

    /**
     * Grupo de Setting donde caen las claves de este tab.
     */
    public function settingGroup(): string
    {
        return match ($this->input('tab')) {
            'general', 'og', 'robots', 'categories' => 'seo',
            'schema'                                => 'organization',
            'integrations'                          => 'integrations',
            'chrome'                                => 'site',
            default                                 => 'seo',
        };
    }

    /**
     * Idioma de las settings a persistir. null = default (Spanish).
     */
    public function lang(): ?string
    {
        $lang = $this->input('lang');
        return $lang === 'es' || $lang === null ? null : $lang;
    }
}
