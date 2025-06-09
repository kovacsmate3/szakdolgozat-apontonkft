<?php

namespace App\Http\Controllers\Api\Shared\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'slug' => ['required', 'string', 'max:50', 'unique:roles'],
                'title' => ['required', 'string', 'max:100', 'unique:roles'],
                'description' => ['nullable', 'string'],
            ],
            [
                'slug.required' => 'A szerepkör azonosítójának megadása kötelező.',
                'slug.string' => 'A szerepkör azonosítója csak szöveg formátumú lehet.',
                'slug.max' => 'A szerepkör azonosítója maximum 50 karakter hosszú lehet.',
                'slug.unique' => 'Ez a szerepkör azonosító már létezik.',

                'title.required' => 'A szerepkör nevének megadása kötelező.',
                'title.string' => 'A szerepkör neve csak szöveg formátumú lehet.',
                'title.max' => 'A szerepkör neve maximum 100 karakter hosszú lehet.',
                'title.unique' => 'Ez a szerepkör név már létezik.',

                'description.string' => 'A szerepkör leírása csak szöveg formátumú lehet.',
            ]
        );

        $role = Role::create($validated);

        return response()->json([
            'message' => 'A szerepkör sikeresen létrehozva.',
            'role' => $role
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Role::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['users'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $role = $query->find($id);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($role, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'slug' => ['sometimes', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
            'title' => ['sometimes', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)],
            'description' => ['sometimes', 'nullable', 'string'],
        ], [
            'slug.string' => 'A szerepkör azonosító kizárólag nem üres, szöveges formátumú lehet.',
            'slug.max' => 'A szerepkör azonosító maximum 50 karakter hosszú lehet.',
            'slug.unique' => 'Ez a szerepkör azonosító már létezik.',

            'title.string' => 'A szerepkör neve kizárólag nem üres, szöveges formátumú lehet.',
            'title.max' => 'A szerepkör neve maximum 100 karakter hosszú lehet.',
            'title.unique' => 'Ez a szerepkör név már létezik.',

            'description.string' => 'A szerepkör leírása csak szöveges formátumú lehet.',
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'A szerepkör adatai sikeresen frissítve lettek.',
            'role' => $role
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szerepkör nem található.'
            ], Response::HTTP_NOT_FOUND);
        }
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Ez a szerepkör felhasználókhoz van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $slug = $role->slug;

        $role->delete();
        return response()->json([
            'message' => "$slug szerepkör sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
