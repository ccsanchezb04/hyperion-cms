<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'hycms_notifications';
    protected $primaryKey = 'noti_idnoti';

    protected $fillable = [
        'noti_nmtype',
        'noti_dsdata',
        'noti_iduser',
        'noti_boread',
    ];

    protected $casts = [
        'noti_idnoti' => 'integer',
        'noti_iduser' => 'integer',
        'noti_boread' => 'boolean',
        'noti_dsdata' => 'array',
        'noti_dtcrea' => 'datetime',
        'noti_dtreau' => 'datetime',
    ];

    const CREATED_AT = 'noti_dtcrea';
    const UPDATED_AT = 'noti_dtreau';

    // Notification types
    const TYPE_CONTENT_PUBLISHED = 'content_published';
    const TYPE_CONTENT_UPDATED = 'content_updated';
    const TYPE_USER_ASSIGNED = 'user_assigned';
    const TYPE_ROLE_CHANGED = 'role_changed';
    const TYPE_SYSTEM = 'system';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Usuario que recibe la notificación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'noti_iduser', 'user_iduser');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    /**
     * Notificaciones no leídas.
     */
    public function scopeUnread($query)
    {
        return $query->where('noti_boread', false);
    }

    /**
     * Notificaciones leídas.
     */
    public function scopeRead($query)
    {
        return $query->where('noti_boread', true);
    }

    /**
     * Filtrar por tipo.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('noti_nmtype', $type);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    /**
     * Marcar como leída.
     */
    public function markAsRead(): bool
    {
        $this->noti_boread = true;
        return $this->save();
    }

    /**
     * Marcar como no leída.
     */
    public function markAsUnread(): bool
    {
        $this->noti_boread = false;
        return $this->save();
    }

    /**
     * Verificar si está leída.
     */
    public function isRead(): bool
    {
        return $this->noti_boread;
    }

    /**
     * Obtener mensaje de notificación basado en tipo.
     */
    public function getMessage(): string
    {
        return match($this->noti_nmtype) {
            self::TYPE_CONTENT_PUBLISHED => 'Content has been published',
            self::TYPE_CONTENT_UPDATED => 'Content has been updated',
            self::TYPE_USER_ASSIGNED => 'You have been assigned to a new role',
            self::TYPE_ROLE_CHANGED => 'Your role has been changed',
            self::TYPE_SYSTEM => 'System notification',
            default => 'New notification',
        };
    }
}
