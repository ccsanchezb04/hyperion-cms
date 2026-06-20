<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table      = 'hycms_settings';
    protected $primaryKey = 'sett_idsett';
    public $timestamps    = false;

    protected $fillable = [
        'sett_cdkeys',
        'sett_dsvalu',
        'sett_nmgrou',
        'sett_cdlang',
    ];

    protected $casts = [
        'sett_idsett' => 'integer',
        'sett_cdkeys' => 'string',
        'sett_dsvalu' => 'string',
        'sett_nmgrou' => 'string',
        'sett_cdlang' => 'string',
        'sett_dtupda' => 'datetime',
    ];

    const UPDATED_AT = 'sett_dtupda';

    // ─── Grupos estándar ───────────────────────────────────────────────────

    const GROUP_GENERAL      = 'general';
    const GROUP_SEO          = 'seo';
    const GROUP_MEDIA        = 'media';
    const GROUP_MAIL         = 'mail';
    const GROUP_SITE         = 'site';
    const GROUP_ORGANIZATION = 'organization';
    const GROUP_INTEGRATIONS = 'integrations';

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeGroup($query, string $group)
    {
        return $query->where('sett_nmgrou', $group);
    }

    // ─── Helpers estáticos ─────────────────────────────────────────────────

    /**
     * Obtiene el valor de una clave de configuración. Si $lang viene dado,
     * busca primero la fila con ese idioma; si no existe, cae al valor de
     * idioma por defecto (sett_cdlang IS NULL).
     */
    public static function getValue(string $key, mixed $default = null, ?string $lang = null): mixed
    {
        if ($lang !== null) {
            $localized = static::where('sett_cdkeys', $key)
                ->where('sett_cdlang', $lang)
                ->first();
            if ($localized) {
                return $localized->sett_dsvalu;
            }
        }

        $setting = static::where('sett_cdkeys', $key)
            ->whereNull('sett_cdlang')
            ->first();
        return $setting ? $setting->sett_dsvalu : $default;
    }

    /**
     * Guarda o actualiza una clave de configuración. El parámetro $lang
     * permite mantener traducciones; null = idioma por defecto (Spanish).
     */
    public static function setValue(
        string $key,
        mixed $value,
        string $group = self::GROUP_GENERAL,
        ?string $lang = null,
    ): static {
        return static::updateOrCreate(
            ['sett_cdkeys' => $key, 'sett_cdlang' => $lang],
            ['sett_dsvalu' => $value, 'sett_nmgrou' => $group]
        );
    }

    /**
     * Obtiene todos los settings de un grupo como array clave => valor.
     * Si $lang viene dado, mergea las traducciones sobre los defaults
     * (las traducciones disponibles pisan al default).
     *
     * @return array<string, string>
     */
    public static function getGroup(string $group, ?string $lang = null): array
    {
        $defaults = static::group($group)
            ->whereNull('sett_cdlang')
            ->pluck('sett_dsvalu', 'sett_cdkeys')
            ->toArray();

        if ($lang === null) {
            return $defaults;
        }

        $translations = static::group($group)
            ->where('sett_cdlang', $lang)
            ->pluck('sett_dsvalu', 'sett_cdkeys')
            ->toArray();

        return array_merge($defaults, $translations);
    }
}
