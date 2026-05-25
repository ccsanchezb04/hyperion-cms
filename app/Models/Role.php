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

    /**
     * Permisos asignados a este rol.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'hycms_role_permissions',
            'rope_idrole',
            'rope_idperm',
            'role_idrole',
            'perm_idperm'
        )->withPivot('rope_dtcrea');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('role_cdslug', $slug);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    /**
     * Verifica si el rol tiene un permiso específico.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('perm_cdslug', $permissionSlug)->exists();
    }

    /**
     * Asigna un permiso al rol.
     */
    public function givePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('perm_cdslug', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([$permission->perm_idperm]);
        }
    }

    /**
     * Revoca un permiso del rol.
     */
    public function revokePermission(string $permissionSlug): void
    {
        $permission = Permission::where('perm_cdslug', $permissionSlug)->first();
        if ($permission) {
            $this->permissions()->detach($permission->perm_idperm);
        }
    }

    /**
     * Sincroniza los permisos del rol.
     */
    public function syncPermissions(array $permissionSlugs): void
    {
        $permissionIds = Permission::whereIn('perm_cdslug', $permissionSlugs)->pluck('perm_idperm');
        $this->permissions()->sync($permissionIds);
    }
}
