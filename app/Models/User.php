<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table      = 'hycms_users';
    protected $primaryKey = 'user_iduser';

    // SoftDeletes usa deleted_at por defecto; mapeamos a nuestra columna
    const DELETED_AT = 'user_dtdele';

    protected $fillable = [
        'user_nmname',
        'user_dsemai',
        'user_cdpass',
        'user_cdstat',
    ];

    protected $hidden = [
        'user_cdpass',
    ];

    protected $casts = [
        'user_iduser' => 'integer',
        'user_nmname' => 'string',
        'user_dsemai' => 'string',
        'user_cdpass' => 'hashed',          // Laravel 10+ hashea automáticamente
        'user_cdstat' => 'string',
        'user_dtcrea' => 'datetime',
        'user_dtupda' => 'datetime',
        'user_dtdele' => 'datetime',
    ];

    // Mapeo de timestamps al estándar hycms_
    const CREATED_AT = 'user_dtcrea';
    const UPDATED_AT = 'user_dtupda';

    // ─── Constantes de estado ──────────────────────────────────────────────

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    // ─── Relaciones ────────────────────────────────────────────────────────

    /**
     * Roles asignados al usuario.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'hycms_user_roles',
            'usro_iduser',
            'usro_idrole',
            'user_iduser',
            'role_idrole'
        )->withPivot('usro_dtcrea');
    }

    /**
     * Contenidos creados por el usuario.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'cont_idauth', 'user_iduser');
    }

    /**
     * Archivos subidos por el usuario.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'medi_idusby', 'user_iduser');
    }

    // ─── Helpers de roles ──────────────────────────────────────────────────

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('role_cdslug', $slug);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isEditor(): bool
    {
        return $this->hasRole(Role::EDITOR);
    }

    public function isActive(): bool
    {
        return $this->user_cdstat === self::STATUS_ACTIVE;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('user_cdstat', self::STATUS_ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('user_cdstat', self::STATUS_INACTIVE);
    }

    // ─── Autenticación (Sanctum) ───────────────────────────────────────────

    /**
     * Columna usada por Sanctum / Auth para el email.
     */
    public function getAuthPassword(): string
    {
        return $this->user_cdpass;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->user_dsemai;
    }
}
