<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table      = 'hycms_menus';
    protected $primaryKey = 'menu_idmenu';
    public $timestamps    = false;

    protected $fillable = [
        'menu_nmname',
        'menu_cdslug',
    ];

    protected $casts = [
        'menu_idmenu' => 'integer',
        'menu_nmname' => 'string',
        'menu_cdslug' => 'string',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Todos los ítems del menú (planos).
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'mnit_idmenu', 'menu_idmenu')
                    ->orderBy('mnit_nrorde');
    }

    /**
     * Solo ítems raíz (sin padre), con sus hijos cargados.
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'mnit_idmenu', 'menu_idmenu')
                    ->whereNull('mnit_idpare')
                    ->orderBy('mnit_nrorde')
                    ->with('childrenRecursive');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('menu_cdslug', $slug);
    }
}
