<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::with('roles:role_idrole,role_nmname,role_cdslug')
            ->orderBy('perm_cdslug')
            ->get()
            ->groupBy(fn (Permission $p) => $this->domainOf($p->perm_cdslug))
            ->map(fn ($group) => $group->map(fn (Permission $p) => [
                'slug' => $p->perm_cdslug,
                'name' => $p->perm_nmname,
                'description' => $p->perm_dsdesc,
                'roles' => $p->roles->map(fn ($r) => [
                    'name' => $r->role_nmname,
                    'slug' => $r->role_cdslug,
                ])->values()->all(),
            ])->values()->all())
            ->toArray();

        return Inertia::render('Permissions/Index', [
            'groups' => $permissions,
        ]);
    }

    protected function domainOf(string $slug): string
    {
        $parts = explode('-', $slug, 2);
        return $parts[1] ?? $parts[0];
    }
}
