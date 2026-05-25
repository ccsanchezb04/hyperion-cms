<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentVersion extends Model
{
    protected $table      = 'hycms_content_versions';
    protected $primaryKey = 'cove_idcove';
    public $timestamps    = false;

    protected $fillable = [
        'cove_idcont',
        'cove_dsbody',
        'cove_nrvers',
    ];

    protected $casts = [
        'cove_idcove' => 'integer',
        'cove_idcont' => 'integer',
        'cove_dsbody' => 'string',
        'cove_nrvers' => 'integer',
        'cove_dtcrea' => 'datetime',
    ];

    const CREATED_AT = 'cove_dtcrea';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Contenido al que pertenece esta versión.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'cove_idcont', 'cont_idcont');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    /**
     * Genera el siguiente número de versión para un contenido.
     */
    public static function nextVersionNumber(int $contentId): int
    {
        $last = static::where('cove_idcont', $contentId)->max('cove_nrvers');
        return ($last ?? 0) + 1;
    }
}
