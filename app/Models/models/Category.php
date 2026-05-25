<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table      = 'hycms_categories';
    protected $primaryKey = 'cate_idcate';
    public $timestamps    = false;

    protected $fillable = [
        'cate_nmname',
        'cate_cdslug',
        'cate_idpare',
    ];

    protected $casts = [
        'cate_idcate' => 'integer',
        'cate_nmname' => 'string',
        'cate_cdslug' => 'string',
        'cate_idpare' => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Categoría padre (auto-referencia).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cate_idpare', 'cate_idcate');
    }

    /**
     * Subcategorías hijas (auto-referencia).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'cate_idpare', 'cate_idcate');
    }

    /**
     * Subcategorías con sus hijos cargados recursivamente.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Contenidos asociados (N:M).
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(
            Content::class,
            'hycms_content_category',
            'coca_idcate',
            'coca_idcont',
            'cate_idcate',
            'cont_idcont'
        );
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    /**
     * Solo categorías raíz (sin padre).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('cate_idpare');
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('cate_cdslug', $slug);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isRoot(): bool
    {
        return is_null($this->cate_idpare);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }
}
