<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'hycms_permissions';
    protected $primaryKey = 'perm_idperm';

    protected $fillable = [
        'perm_nmname',
        'perm_cdslug',
        'perm_dsdesc',
    ];

    protected $casts = [
        'perm_idperm' => 'integer',
        'perm_nmname' => 'string',
        'perm_cdslug' => 'string',
        'perm_dsdesc' => 'string',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Roles que tienen este permiso.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'hycms_role_permissions',
            'rope_idperm',
            'rope_idrole',
            'perm_idperm',
            'role_idrole'
        )->withPivot('rope_dtcrea');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('perm_cdslug', $slug);
    }
}
