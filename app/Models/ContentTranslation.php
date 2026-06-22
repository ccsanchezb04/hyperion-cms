<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentTranslation extends Model
{
    protected $table      = 'hycms_content_translations';
    protected $primaryKey = 'cotr_idcotr';

    const CREATED_AT = 'cotr_dtcrea';
    const UPDATED_AT = 'cotr_dtupda';

    protected $fillable = [
        'cotr_idcont',
        'cotr_cdlang',
        'cotr_cdslug',
        'cotr_nmtitl',
        'cotr_dsbody',
    ];

    protected $casts = [
        'cotr_idcotr' => 'integer',
        'cotr_idcont' => 'integer',
        'cotr_cdlang' => 'string',
        'cotr_cdslug' => 'string',
        'cotr_nmtitl' => 'string',
        'cotr_dsbody' => 'string',
        'cotr_dtcrea' => 'datetime',
        'cotr_dtupda' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'cotr_idcont', 'cont_idcont');
    }
}
