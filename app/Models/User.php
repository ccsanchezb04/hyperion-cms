<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    protected $table      = 'hycms_users';
    protected $primaryKey = 'user_iduser';

    // SoftDeletes usa deleted_at por defecto; mapeamos a nuestra columna
    const DELETED_AT = 'user_dtdele';

    protected $fillable = [
        'user_nmname',
        'user_dsemai',
        'user_cdpass',
        'user_cdstat',
        'email_verified_at',
        // Canonical aliases — mutators forward these to the hycms_ columns.
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'user_cdpass',
        'password',
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

    // ─── Accessors canónicos ───────────────────────────────────────────────
    //
    // Exponen los nombres estándar (id, name, email, avatar) que ya usan los
    // Resources de la API y el frontend, mapeando a las columnas hycms_.
    // Los setters escriben en la columna real para que `$user->name = '...'`
    // y `fill()` funcionen igual que en un modelo Laravel convencional.

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_iduser,
            set: fn ($value) => ['user_iduser' => $value],
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_nmname,
            set: fn ($value) => ['user_nmname' => $value],
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_dsemai,
            set: fn ($value) => ['user_dsemai' => $value],
        );
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['avatar'] ?? null,
        );
    }

    // Laravel's `current_password` validation rule and Hash::check helpers
    // read `$user->password` directly; expose the hashed value here.
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_cdpass,
            set: fn ($value) => ['user_cdpass' => $value],
        );
    }

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

    /**
     * Notificaciones del usuario.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'noti_iduser', 'user_iduser');
    }

    // ─── Helpers de roles ──────────────────────────────────────────────────

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('role_cdslug', $slug);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isEditor(): bool
    {
        return $this->hasRole('editor');
    }

    public function isActive(): bool
    {
        return $this->user_cdstat === self::STATUS_ACTIVE;
    }

    // ─── Helpers de permisos ─────────────────────────────────────────────────

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super admin tiene todos los permisos
        if ($this->isAdmin()) {
            return true;
        }

        // Verificar permisos a través de roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados.
     */
    public function hasAllPermissions(array $permissionSlugs): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $permissionSlug) {
            if (!$this->hasPermission($permissionSlug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lista plana de slugs de permisos del usuario (resolviendo roles).
     * Devuelve ['*'] para super-admin como marcador de "todo permitido".
     * Se expone vía Inertia (HandleInertiaRequests) para que el frontend
     * pueda filtrar elementos de menú sin disparar 403.
     */
    public function getPermissionSlugs(): array
    {
        if ($this->isAdmin()) {
            return ['*'];
        }

        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->pluck('perm_cdslug')
            ->unique()
            ->values()
            ->all();
    }

    // ─── Helpers de notificaciones ───────────────────────────────────────────

    /**
     * Obtiene notificaciones no leídas.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    /**
     * Marcar todas las notificaciones como leídas.
     */
    public function markAllNotificationsAsRead(): bool
    {
        return $this->unreadNotifications()->update(['noti_boread' => true]) > 0;
    }

    /**
     * Contar notificaciones no leídas.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
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

    /**
     * Nombre de la columna usada para el email en autenticación.
     */
    public function getAuthIdentifierName(): string
    {
        return 'user_dsemai';
    }

    /**
     * Valor del identificador de autenticación (email).
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->user_dsemai;
    }
}
