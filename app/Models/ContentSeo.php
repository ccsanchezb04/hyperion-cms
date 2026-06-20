<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSeo extends Model
{
    protected $table      = 'hycms_content_seo';
    protected $primaryKey = 'cose_idcose';

    const CREATED_AT = 'cose_dtcrea';
    const UPDATED_AT = 'cose_dtupda';

    protected $fillable = [
        'cose_idcont',
        'cose_nmtitl',
        'cose_dsdesc',
        'cose_dsogim',
        'cose_cdcano',
        'cose_bonoix',
    ];

    protected $casts = [
        'cose_idcose' => 'integer',
        'cose_idcont' => 'integer',
        'cose_nmtitl' => 'string',
        'cose_dsdesc' => 'string',
        'cose_dsogim' => 'string',
        'cose_cdcano' => 'string',
        'cose_bonoix' => 'boolean',
        'cose_dtcrea' => 'datetime',
        'cose_dtupda' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'cose_idcont', 'cont_idcont');
    }
}
