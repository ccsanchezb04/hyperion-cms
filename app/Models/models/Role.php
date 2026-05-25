<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'hycms_roles';
    protected $primaryKey = 'role_idrole';
    public $timestamps = false;

    protected $fillable = [
        'role_nmname',
        'role_cdslug',
    ];

    protected $casts = [
        'role_idrole' => 'integer',
        'role_nmname' => 'string',
        'role_cdslug' => 'string',
    ];

    // ─── Constantes de roles ───────────────────────────────────────────────

    const ADMIN  = 'admin';
    const EDITOR = 'editor';
    const VIEWER = 'viewer';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Usuarios que tienen este rol.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'hycms_user_roles',
            'usro_idrole',
            'usro_iduser',
            'role_idrole',
            'user_iduser'
        )->withPivot('usro_dtcrea');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('role_cdslug', $slug);
    }
}
