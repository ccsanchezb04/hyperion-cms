<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Content extends Model
{
    use SoftDeletes;

    protected $table      = 'hycms_contents';
    protected $primaryKey = 'cont_idcont';

    const DELETED_AT = 'cont_dtdele';
    const CREATED_AT = 'cont_dtcrea';
    const UPDATED_AT = 'cont_dtupda';

    protected $fillable = [
        'cont_nmtitl',
        'cont_cdslug',
        'cont_cdtype',
        'cont_cdstat',
        'cont_idauth',
        'cont_dtpubl',
    ];

    protected $casts = [
        'cont_idcont' => 'integer',
        'cont_nmtitl' => 'string',
        'cont_cdslug' => 'string',
        'cont_cdtype' => 'string',
        'cont_cdstat' => 'string',
        'cont_idauth' => 'integer',
        'cont_dtpubl' => 'datetime',
        'cont_dtcrea' => 'datetime',
        'cont_dtupda' => 'datetime',
        'cont_dtdele' => 'datetime',
    ];

    // ─── Constantes de tipo ────────────────────────────────────────────────

    const TYPE_POST   = 'post';
    const TYPE_PAGE   = 'page';
    const TYPE_CUSTOM = 'custom';

    // ─── Constantes de estado ──────────────────────────────────────────────

    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED  = 'archived';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Autor del contenido.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cont_idauth', 'user_iduser');
    }

    /**
     * Versiones históricas del contenido.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class, 'cove_idcont', 'cont_idcont')
                    ->orderByDesc('cove_nrvers');
    }

    /**
     * Última versión activa (singular). Usa hasOne+latestOfMany para que el
     * Resource pueda acceder a $content->latestVersion->cove_dsbody en lugar
     * de recibir una Collection.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class, 'cove_idcont', 'cont_idcont')
                    ->latestOfMany('cove_nrvers');
    }

    /**
     * Categorías asociadas (N:M).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'hycms_content_category',
            'coca_idcont',
            'coca_idcate',
            'cont_idcont',
            'cate_idcate'
        );
    }

    /**
     * Archivos media polimórficos asociados a este contenido.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'medi_nmmdbl', 'medi_idmdbl');
    }

    /**
     * Overrides SEO per-página (opcionales). 1:1.
     */
    public function seo(): HasOne
    {
        return $this->hasOne(ContentSeo::class, 'cose_idcont', 'cont_idcont');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('cont_cdstat', self::STATUS_PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('cont_cdstat', self::STATUS_DRAFT);
    }

    public function scopeArchived($query)
    {
        return $query->where('cont_cdstat', self::STATUS_ARCHIVED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('cont_cdtype', $type);
    }

    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('cont_idauth', $userId);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->cont_cdstat === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->cont_cdstat === self::STATUS_DRAFT;
    }

    public function publish(): void
    {
        $this->cont_cdstat = self::STATUS_PUBLISHED;
        $this->cont_dtpubl = now();
        $this->save();
    }

    public function archive(): void
    {
        $this->cont_cdstat = self::STATUS_ARCHIVED;
        $this->save();
    }
}
