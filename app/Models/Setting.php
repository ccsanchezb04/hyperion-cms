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
    ];

    protected $casts = [
        'sett_idsett' => 'integer',
        'sett_cdkeys' => 'string',
        'sett_dsvalu' => 'string',
        'sett_nmgrou' => 'string',
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
     * Obtiene el valor de una clave de configuración.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('sett_cdkeys', $key)->first();
        return $setting ? $setting->sett_dsvalu : $default;
    }

    /**
     * Guarda o actualiza una clave de configuración.
     */
    public static function setValue(string $key, mixed $value, string $group = self::GROUP_GENERAL): static
    {
        return static::updateOrCreate(
            ['sett_cdkeys' => $key],
            ['sett_dsvalu' => $value, 'sett_nmgrou' => $group]
        );
    }

    /**
     * Obtiene todos los settings de un grupo como array clave => valor.
     */
    public static function getGroup(string $group): array
    {
        return static::group($group)
            ->pluck('sett_dsvalu', 'sett_cdkeys')
            ->toArray();
    }
}
