<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $table      = 'hycms_menu_items';
    protected $primaryKey = 'mnit_idmnit';
    public $timestamps    = false;

    protected $fillable = [
        'mnit_idmenu',
        'mnit_nmlabe',
        'mnit_cdtype',
        'mnit_idrefi',
        'mnit_nmlkbl',
        'mnit_dsurll',
        'mnit_idpare',
        'mnit_nrorde',
    ];

    protected $casts = [
        'mnit_idmnit' => 'integer',
        'mnit_idmenu' => 'integer',
        'mnit_nmlabe' => 'string',
        'mnit_cdtype' => 'string',
        'mnit_idrefi' => 'integer',
        'mnit_nmlkbl' => 'string',
        'mnit_dsurll' => 'string',
        'mnit_idpare' => 'integer',
        'mnit_nrorde' => 'integer',
    ];

    // ─── Constantes de tipo ────────────────────────────────────────────────

    const TYPE_CONTENT  = 'content';
    const TYPE_URL      = 'url';
    const TYPE_CATEGORY = 'category';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Menú al que pertenece el ítem.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'mnit_idmenu', 'menu_idmenu');
    }

    /**
     * Ítem padre (auto-referencia).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'mnit_idpare', 'mnit_idmnit');
    }

    /**
     * Ítems hijos directos (auto-referencia).
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'mnit_idpare', 'mnit_idmnit')
                    ->orderBy('mnit_nrorde');
    }

    /**
     * Ítems hijos con sus hijos cargados recursivamente.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Traducciones del label (1:N — una por idioma soportado).
     */
    public function translations(): HasMany
    {
        return $this->hasMany(MenuItemTranslation::class, 'mitr_idmnit', 'mnit_idmnit');
    }

    /**
     * Devuelve la traducción al idioma dado, o null si no existe.
     */
    public function translation(string $lang): ?MenuItemTranslation
    {
        return $this->translations->firstWhere('mitr_cdlang', $lang);
    }

    /**
     * Label en el locale dado, con fallback al label default.
     */
    public function labelFor(string $lang): string
    {
        $t = $this->translation($lang);
        return $t?->mitr_nmlabe ?: $this->mnit_nmlabe;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeRoot($query)
    {
        return $query->whereNull('mnit_idpare');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('mnit_nrorde');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isRoot(): bool
    {
        return is_null($this->mnit_idpare);
    }

    public function isContentLink(): bool
    {
        return $this->mnit_cdtype === self::TYPE_CONTENT;
    }

    public function isExternalUrl(): bool
    {
        return $this->mnit_cdtype === self::TYPE_URL;
    }

    public function isCategoryLink(): bool
    {
        return $this->mnit_cdtype === self::TYPE_CATEGORY;
    }

    /**
     * Resuelve la URL real del ítem según su tipo.
     */
    public function resolveUrl(): string
    {
        return match ($this->mnit_cdtype) {
            self::TYPE_URL      => $this->mnit_dsurll ?? '#',
            self::TYPE_CONTENT  => Content::find($this->mnit_idrefi)?->cont_cdslug
                                    ? '/'. Content::find($this->mnit_idrefi)->cont_cdslug
                                    : '#',
            self::TYPE_CATEGORY => Category::find($this->mnit_idrefi)?->cate_cdslug
                                    ? '/category/'. Category::find($this->mnit_idrefi)->cate_cdslug
                                    : '#',
            default             => '#',
        };
    }
}
