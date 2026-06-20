<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Roles que no se pueden borrar desde la UI (forman parte del sistema).
     */
    protected const PROTECTED_SLUGS = ['super-admin', 'admin'];

    public function index(): Response
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('role_nmname')
            ->get()
            ->map(fn (Role $r) => [
                'id'                => $r->role_idrole,
                'name'              => $r->role_nmname,
                'slug'              => $r->role_cdslug,
                'is_protected'      => in_array($r->role_cdslug, self::PROTECTED_SLUGS, true),
                'users_count'       => $r->users_count,
                'permissions_count' => $r->permissions_count,
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        $role = Role::create([
            'role_nmname' => $data['name'],
            'role_cdslug' => $data['slug'],
        ]);

        $role->syncPermissions((array) ($data['permissions'] ?? []));

        return redirect()->route('admin.roles.index')->with('status', 'Rol creado.');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('Roles/Edit', [
            'role' => [
                'id'           => $role->role_idrole,
                'name'         => $role->role_nmname,
                'slug'         => $role->role_cdslug,
                'is_protected' => in_array($role->role_cdslug, self::PROTECTED_SLUGS, true),
                'permissions'  => $role->permissions->pluck('perm_cdslug')->all(),
            ],
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validateRole($request, $role);

        $role->update([
            'role_nmname' => $data['name'],
            'role_cdslug' => $data['slug'],
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions((array) $data['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Rol actualizado.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->role_cdslug, self::PROTECTED_SLUGS, true)) {
            return back()->withErrors(['role' => 'Este rol del sistema no se puede borrar.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'No se puede borrar un rol con usuarios asignados.']);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Rol eliminado.');
    }

    /**
     * @return array{name:string,slug:string,permissions?:array<int,string>}
     */
    protected function validateRole(Request $request, ?Role $editing = null): array
    {
        $slugRule = ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'];
        $slugRule[] = $editing
            ? Rule::unique('hycms_roles', 'role_cdslug')->ignore($editing->role_idrole, 'role_idrole')
            : Rule::unique('hycms_roles', 'role_cdslug');

        return $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'slug'          => $slugRule,
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:hycms_permissions,perm_cdslug'],
        ]);
    }

    /**
     * Permisos agrupados por dominio (lo que va antes del primer guion).
     *
     * @return array<string, array<int, array{slug:string,name:string,description:?string}>>
     */
    protected function groupedPermissions(): array
    {
        return Permission::orderBy('perm_cdslug')
            ->get(['perm_cdslug', 'perm_nmname', 'perm_dsdesc'])
            ->groupBy(fn (Permission $p) => $this->domainOf($p->perm_cdslug))
            ->map(fn ($group) => $group
                ->map(fn (Permission $p) => [
                    'slug' => $p->perm_cdslug,
                    'name' => $p->perm_nmname,
                    'description' => $p->perm_dsdesc,
                ])
                ->values()
                ->all())
            ->toArray();
    }

    protected function domainOf(string $slug): string
    {
        // 'edit-content' -> 'content'; 'manage-roles' -> 'roles'; 'use-ai' -> 'ai'
        $parts = explode('-', $slug, 2);
        return $parts[1] ?? $parts[0];
    }
}
