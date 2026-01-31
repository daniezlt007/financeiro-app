<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

class PermissaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permissions:admin');
    }

    public function index()
    {
        $roles = Role::with('permissions')->where('guard_name', 'web')->orderBy('name')->get();
        $permissions = SpatiePermission::where('guard_name', 'web')->orderBy('name')->get();
        $users = User::with(['roles', 'empresa'])->orderBy('name')->get();

        return Inertia::render('Permissoes/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
            'permissionList' => PermissionSeeder::PERMISSIONS,
        ]);
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);
        return back()->with('success', 'Permissões do papel atualizadas.');
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);
        $user->syncRoles($validated['roles'] ?? []);
        return back()->with('success', 'Papéis do usuário atualizados.');
    }
}
