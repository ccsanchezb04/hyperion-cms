<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles']);

        // Filtros
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->inactive();
            }
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('role_cdslug', $request->input('role'));
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('user_nmname', 'like', "%{$search}%")
                  ->orWhere('user_dsemai', 'like', "%{$search}%");
        }

        // Ordenamiento
        $orderBy = $request->input('order_by', 'user_dtcrea');
        $orderDir = $request->input('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'user_nmname' => $request->input('name'),
            'user_dsemai' => $request->input('email'),
            'user_cdpass' => Hash::make($request->input('password')),
            'user_cdstat' => $request->input('status', User::STATUS_ACTIVE),
        ]);

        // Asignar roles
        if ($request->has('roles')) {
            $roles = Role::whereIn('role_cdslug', $request->input('roles'))->get();
            $user->roles()->sync($roles->pluck('role_idrole'));
        }

        return response()->json([
            'data' => UserResource::make($user->load('roles')),
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => UserResource::make($user->load(['roles', 'contents', 'media'])),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update([
            'user_nmname' => $request->input('name', $user->user_nmname),
            'user_dsemai' => $request->input('email', $user->user_dsemai),
            'user_cdstat' => $request->input('status', $user->user_cdstat),
        ]);

        // Actualizar contraseña si se proporciona
        if ($request->has('password')) {
            $user->update([
                'user_cdpass' => Hash::make($request->input('password')),
            ]);
        }

        // Actualizar roles
        if ($request->has('roles')) {
            $roles = Role::whereIn('role_cdslug', $request->input('roles'))->get();
            $user->roles()->sync($roles->pluck('role_idrole'));
        }

        return response()->json([
            'data' => UserResource::make($user->load('roles')),
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // No permitir eliminar al usuario autenticado
        if ($user->user_iduser === auth()->id()) {
            return response()->json([
                'message' => 'Cannot delete your own account',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Assign roles to a user.
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:hycms_roles,role_cdslug',
        ]);

        $roles = Role::whereIn('role_cdslug', $request->input('roles'))->get();
        $user->roles()->sync($roles->pluck('role_idrole'));

        return response()->json([
            'data' => UserResource::make($user->load('roles')),
            'message' => 'Roles assigned successfully',
        ]);
    }

    /**
     * Activate a user.
     */
    public function activate(User $user): JsonResponse
    {
        $user->update(['user_cdstat' => User::STATUS_ACTIVE]);

        return response()->json([
            'data' => UserResource::make($user->load('roles')),
            'message' => 'User activated successfully',
        ]);
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user): JsonResponse
    {
        // No permitir desactivar al usuario autenticado
        if ($user->user_iduser === auth()->id()) {
            return response()->json([
                'message' => 'Cannot deactivate your own account',
            ], 400);
        }

        $user->update(['user_cdstat' => User::STATUS_INACTIVE]);

        return response()->json([
            'data' => UserResource::make($user->load('roles')),
            'message' => 'User deactivated successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics(): JsonResponse
    {
        $totalUsers = User::count();
        $activeUsers = User::active()->count();
        $inactiveUsers = User::inactive()->count();
        $admins = User::whereHas('roles', function ($q) {
            $q->where('role_cdslug', Role::ADMIN);
        })->count();

        return response()->json([
            'data' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'admins' => $admins,
            ],
        ]);
    }
}
