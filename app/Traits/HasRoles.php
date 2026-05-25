<?php

namespace App\Traits;

use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'hycms_user_roles',
            'usro_iduser',
            'usro_idrole'
        );
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('role_cdslug', $role)
            ->exists();
    }
}
