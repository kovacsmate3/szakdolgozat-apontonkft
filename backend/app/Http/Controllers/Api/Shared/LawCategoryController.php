<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Models\LawCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LawCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LawCategory::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'name');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('name', 'asc');
        }

        if ($request->has('per_page')) {
            $lawCategories = $query->paginate($request->input('per_page', 15));
        } else {
            $lawCategories = $query->get();
        }

        return response()->json($lawCategories, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:law_categories'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'A kategória nevének megadása kötelező.',
            'name.string' => 'A kategória neve csak szöveg formátumú lehet.',
            'name.max' => 'A kategória neve maximum 100 karakter hosszú lehet.',
            'name.unique' => 'Ez a kategória név már létezik.',
            'description.string' => 'A kategória leírása csak szöveg formátumú lehet.'
        ]);

        $lawCategory = LawCategory::create($validated);

        return response()->json([
            'message' => 'A jogszabály kategória sikeresen létrehozva.',
            'category' => $lawCategory
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = LawCategory::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['laws'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $lawCategory = $query->find($id);

        if (!$lawCategory) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály kategória nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($lawCategory, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lawCategory = LawCategory::find($id);

        if (!$lawCategory) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály kategória nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('law_categories')->ignore($lawCategory->id)],
            'description' => ['sometimes', 'nullable', 'string'],
        ], [
            'name.string' => 'A kategória neve kizárólag nem üres, szöveg formátumú lehet.',
            'name.max' => 'A kategória neve maximum 100 karakter hosszú lehet.',
            'name.unique' => 'Ez a kategória név már létezik.',
            'description.string' => 'A kategória leírása csak szöveg formátumú lehet.'
        ]);

        $lawCategory->update($validated);

        return response()->json([
            'message' => 'A jogszabály kategória adatai sikeresen frissítve lettek.',
            'category' => $lawCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lawCategory = LawCategory::find($id);

        if (!$lawCategory) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály kategória nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($lawCategory->laws()->count() > 0) {
            return response()->json([
                'message' => 'Ez a kategória jogszabályokhoz van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $categoryName = $lawCategory->name;

        $lawCategory->delete();
        return response()->json([
            'message' => "{$categoryName} jogszabály kategória sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
