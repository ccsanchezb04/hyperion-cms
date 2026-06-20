<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()->with('roles');

        if ($status = $request->query('status')) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->inactive();
            }
        }

        if ($role = $request->query('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('role_cdslug', $role));
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_nmname', 'like', "%{$search}%")
                  ->orWhere('user_dsemai', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('user_dtcrea')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $u) => $this->serializeUser($u));

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'status' => $request->query('status', ''),
                'role'   => $request->query('role', ''),
                'search' => $request->query('search', ''),
            ],
            'roles' => $this->rolesForSelect(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => $this->rolesForSelect(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'user_nmname' => $request->input('name'),
            'user_dsemai' => $request->input('email'),
            'user_cdpass' => Hash::make($request->input('password')),
            'user_cdstat' => $request->input('status', User::STATUS_ACTIVE),
        ]);

        $this->syncRoles($user, (array) $request->input('roles', []));

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $this->serializeUser($user->load('roles')),
            'roles' => $this->rolesForSelect(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'user_nmname' => $request->input('name', $user->user_nmname),
            'user_dsemai' => $request->input('email', $user->user_dsemai),
            'user_cdstat' => $request->input('status', $user->user_cdstat),
        ];

        if ($request->filled('password')) {
            $data['user_cdpass'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        if ($request->has('roles')) {
            $this->syncRoles($user, (array) $request->input('roles', []));
        }

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->user_iduser === $request->user()->user_iduser) {
            return back()->withErrors(['user' => 'No puedes borrar tu propia cuenta.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['user_cdstat' => User::STATUS_ACTIVE]);

        return back()->with('status', 'Usuario activado.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($user->user_iduser === $request->user()->user_iduser) {
            return back()->withErrors(['user' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->update(['user_cdstat' => User::STATUS_INACTIVE]);

        return back()->with('status', 'Usuario desactivado.');
    }

    /**
     * @param array<int, string> $roleSlugs
     */
    protected function syncRoles(User $user, array $roleSlugs): void
    {
        $ids = Role::whereIn('role_cdslug', $roleSlugs)->pluck('role_idrole');
        $user->roles()->sync($ids);
    }

    /**
     * @return array<int, array{slug:string,name:string}>
     */
    protected function rolesForSelect(): array
    {
        return Role::orderBy('role_nmname')
            ->get(['role_idrole', 'role_nmname', 'role_cdslug'])
            ->map(fn (Role $r) => ['slug' => $r->role_cdslug, 'name' => $r->role_nmname])
            ->all();
    }

    /**
     * @return array{id:int,name:string,email:string,status:string,roles:array<int,string>,created_at:?string}
     */
    protected function serializeUser(User $user): array
    {
        return [
            'id' => $user->user_iduser,
            'name' => $user->user_nmname,
            'email' => $user->user_dsemai,
            'status' => $user->user_cdstat,
            'roles' => $user->roles->pluck('role_cdslug')->all(),
            'created_at' => $user->user_dtcrea?->format('Y-m-d H:i'),
        ];
    }
}
