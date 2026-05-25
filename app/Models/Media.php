<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table      = 'hycms_media';
    protected $primaryKey = 'medi_idmedi';
    public $timestamps    = false;

    protected $fillable = [
        'medi_dspath',
        'medi_cdtype',
        'medi_idmdbl',
        'medi_nmmdbl',
        'medi_idusby',
    ];

    protected $casts = [
        'medi_idmedi' => 'integer',
        'medi_dspath' => 'string',
        'medi_cdtype' => 'string',
        'medi_idmdbl' => 'integer',
        'medi_nmmdbl' => 'string',
        'medi_idusby' => 'integer',
        'medi_dtcrea' => 'datetime',
    ];

    const CREATED_AT = 'medi_dtcrea';

    // ─── Tipos MIME permitidos ─────────────────────────────────────────────

    const ALLOWED_TYPES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
        'application/pdf',
        'video/mp4', 'video/webm',
        'audio/mpeg', 'audio/wav',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Usuario que subió el archivo.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medi_idusby', 'user_iduser');
    }

    /**
     * Entidad polimórfica a la que está asociado el archivo.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo('mediable', 'medi_nmmdbl', 'medi_idmdbl');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeImages($query)
    {
        return $query->where('medi_cdtype', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('medi_cdtype', 'like', 'video/%');
    }

    public function scopeDocuments($query)
    {
        return $query->where('medi_cdtype', 'application/pdf');
    }

    public function scopeByUploader($query, int $userId)
    {
        return $query->where('medi_idusby', $userId);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isImage(): bool
    {
        return str_starts_with($this->medi_cdtype, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->medi_cdtype, 'video/');
    }

    public function getUrl(): string
    {
        return \Storage::url($this->medi_dspath);
    }

    public function getFilename(): string
    {
        return basename($this->medi_dspath);
    }
}
