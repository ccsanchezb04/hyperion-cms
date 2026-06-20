<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $table = 'hycms_contact_messages';
    protected $primaryKey = 'cmsg_idcmsg';

    const CREATED_AT = 'cmsg_dtcrea';
    const UPDATED_AT = 'cmsg_dtupda';
    const DELETED_AT = 'cmsg_dtdele';

    protected $fillable = [
        'cmsg_nmname',
        'cmsg_dsemai',
        'cmsg_cdsubj',
        'cmsg_dsmess',
        'cmsg_dsipad',
        'cmsg_dsuage',
        'cmsg_dtread',
    ];

    protected $casts = [
        'cmsg_idcmsg' => 'integer',
        'cmsg_nmname' => 'string',
        'cmsg_dsemai' => 'string',
        'cmsg_cdsubj' => 'string',
        'cmsg_dsmess' => 'string',
        'cmsg_dsipad' => 'string',
        'cmsg_dsuage' => 'string',
        'cmsg_dtread' => 'datetime',
        'cmsg_dtcrea' => 'datetime',
        'cmsg_dtupda' => 'datetime',
        'cmsg_dtdele' => 'datetime',
    ];

    const SUBJECT_QUOTE = 'cotizacion';
    const SUBJECT_SUPPORT = 'soporte';
    const SUBJECT_OTHER = 'otros';

    public function scopeUnread($query)
    {
        return $query->whereNull('cmsg_dtread');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('cmsg_dtread');
    }

    public function isRead(): bool
    {
        return $this->cmsg_dtread !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->cmsg_dtread = now();
            $this->save();
        }
    }
}
