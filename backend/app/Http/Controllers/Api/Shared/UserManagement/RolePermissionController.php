<?php

namespace App\Http\Controllers\Api\Shared\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the permissions for a specific role.
     */
    public function index(string $roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $permissions = $role->permissions()->get();

        return response()->json(['role' => $role, 'permissions' => $permissions], Response::HTTP_OK);
    }

    /**
     * Assign permissions to a role.
     */
    public function store(Request $request, string $roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'exists:permissions,id'],
        ], [
            'permissions.required' => 'Legalább egy jogosultságot meg kell adni.',
            'permissions.array' => 'A jogosultságok formátuma érvénytelen.',
            'permissions.*.required' => 'A jogosultság azonosító nem lehet üres.',
            'permissions.*.exists' => 'Egy vagy több megadott jogosultság nem létezik.',
        ]);

        $syncData = [];
        foreach ($validated['permissions'] as $permissionId) {
            $syncData[$permissionId] = ['is_active' => true, 'revoked_at' => null];
        }

        $role->permissions()->syncWithoutDetaching($syncData);

        $updatedPermissions = $role->permissions()->get();

        return response()->json([
            'message' => 'Jogosultságok sikeresen hozzárendelve a szerepkörhöz.',
            'permissions' => $updatedPermissions
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified permission for a role.
     */
    public function show(string $roleId, string $permissionId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $permission = $role->permissions()->where('permissions.id', $permissionId)->first();

        if (!$permission) {
            return response()->json([
                'message' => 'A jogosultság nem tartozik ehhez a szerepkörhöz vagy nem létezik.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($permission, Response::HTTP_OK);
    }

    /**
     * Update the specified role-permission relationship.
     */
    public function update(Request $request, string $roleId, string $permissionId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $permission = Permission::find($permissionId);

        if (!$permission) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogosultság nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $rolePermission = $role->permissions()->where('permissions.id', $permissionId)->first();

        if (!$rolePermission) {
            return response()->json([
                'message' => 'A jogosultság nem tartozik ehhez a szerepkörhöz.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ], [
            'is_active.required' => 'Az aktív állapot megadása kötelező.',
            'is_active.boolean' => 'Az aktív állapot csak igaz vagy hamis lehet.',
        ]);

        $updateData = [
            'is_active' => $validated['is_active'],
            'revoked_at' => $validated['is_active'] ? null : now()
        ];
        $role->permissions()->updateExistingPivot($permissionId, $updateData);

        $updatedPermission = $role->permissions()->where('permissions.id', $permissionId)->first();

        return response()->json([
            'message' => 'A jogosultság állapota sikeresen frissítve.',
            'permission' => $updatedPermission
        ], Response::HTTP_OK);
    }

    /**
     * Remove a permission from a role.
     */
    public function destroy(string $roleId, string $permissionId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $permission = Permission::find($permissionId);

        if (!$permission) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogosultság nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $rolePermission = $role->permissions()->where('permissions.id', $permissionId)->first();

        if (!$rolePermission) {
            return response()->json([
                'message' => 'A jogosultság nem tartozik ehhez a szerepkörhöz.'
            ], Response::HTTP_NOT_FOUND);
        }

        $role->permissions()->detach($permissionId);

        return response()->json([
            'message' => "'{$permission->key}' jogosultság sikeresen eltávolítva a '{$role->title}' szerepkörtől."
        ], Response::HTTP_OK);
    }

    /**
     * Sync all permissions for a role.
     */
    public function sync(Request $request, string $roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'exists:permissions,id'],
        ], [
            'permissions.required' => 'A jogosultságok listája kötelező.',
            'permissions.array' => 'A jogosultságok formátuma érvénytelen.',
            'permissions.*.required' => 'A jogosultság azonosító nem lehet üres.',
            'permissions.*.exists' => 'Egy vagy több megadott jogosultság nem létezik.',
        ]);

        $syncData = [];
        foreach ($validated['permissions'] as $permissionId) {
            $syncData[$permissionId] = ['is_active' => true, 'revoked_at' => null];
        }

        $role->permissions()->sync($syncData);

        $updatedPermissions = $role->permissions()->get();

        return response()->json([
            'message' => 'Jogosultságok sikeresen szinkronizálva.',
            'permissions' => $updatedPermissions
        ], Response::HTTP_OK);
    }
}
