<?php

namespace App\Http\Controllers\Api\Shared\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lekérdezés alap létrehozása
        $query = Permission::query();

        // Szűrés modulra ha meg van adva
        if ($request->has('module')) {
            $query->where('module', $request->input('module'));
        }

        // Keresés kulcs vagy leírás alapján
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'key');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('module')->orderBy('key');
        }

        if ($request->has('per_page')) {
            $permissions = $query->paginate($request->input('per_page', 15));
        } else {
            $permissions = $query->get();
        }

        return response()->json($permissions, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:permissions'],
            'module' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ],
        [
            'key.required' => 'A jogosultság kulcs mezőjének megadása kötelező.',
            'key.string' => 'A jogosultság kulcs mezője csak szöveg formátumú lehet.',
            'key.max' => 'A jogosultság kulcs mezője maximum 100 karakter hosszú lehet.',
            'key.unique' => 'Ez a jogosultsági kulcs már létezik.',
            'module.required' => 'A jogosultság modul mezőjének megadása kötelező.',
            'module.string' => 'A jogosultsági modul neve csak szöveg formátumú lehet.',
            'module.max' => 'A jogosultsági modul neve maximum 50 karakter hosszú lehet.',
            'description.string' => 'A jogosultság leírása csak szöveg formátumú lehet.'
        ]
        );

        $permission = Permission::create($validated);

        return response()->json([
            'message' => 'Az új engedély sikeresen létrehozva.',
            'permission' => $permission
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Permission::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['roles'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $permission = $query->find($id);

        if (!$permission) {
            return response()->json([
                'message' => 'A megadott azonosítójú engedély nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($permission, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'message' => 'A megadott azonosítójú engedély nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'key' => ['sometimes', 'string', 'max:100', Rule::unique('permissions', 'key')->ignore($permission->id)],
            'module' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string'],
        ], [
            'key.string' => 'A jogosultság kulcs mezője kizárólag nem üres, szöveges formátumú lehet.',
            'key.max' => 'A jogosultság kulcs mezője maximum 100 karakter hosszú lehet.',
            'key.unique' => 'Ez a jogosultsági kulcs már létezik.',
            'module.string' => 'A jogosultsági modul neve kizárólag nem üres, szöveges formátumú lehet.',
            'module.max' => 'A jogosultsági modul neve maximum 50 karakter hosszú lehet.',
            'description.string' => 'A jogosultság leírása csak szöveg formátumú lehet.'
        ]);

        $permission->update($validated);

        return response()->json([
            'message' => 'A jogosultság adatai sikeresen frissítve lettek.',
            'permission' => $permission
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogosultság nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'Ez a jogosultság szerepkörökhöz van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $key = $permission->key;

        $permission->delete();
        return response()->json([
            'message' => "$key jogosultság sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
