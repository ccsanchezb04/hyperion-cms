<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemTranslation extends Model
{
    protected $table      = 'hycms_menu_item_translations';
    protected $primaryKey = 'mitr_idmitr';

    const CREATED_AT = 'mitr_dtcrea';
    const UPDATED_AT = 'mitr_dtupda';

    protected $fillable = [
        'mitr_idmnit',
        'mitr_cdlang',
        'mitr_nmlabe',
    ];

    protected $casts = [
        'mitr_idmitr' => 'integer',
        'mitr_idmnit' => 'integer',
        'mitr_cdlang' => 'string',
        'mitr_nmlabe' => 'string',
        'mitr_dtcrea' => 'datetime',
        'mitr_dtupda' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'mitr_idmnit', 'mnit_idmnit');
    }
}
