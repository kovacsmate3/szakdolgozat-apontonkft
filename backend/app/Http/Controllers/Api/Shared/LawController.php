<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Models\Law;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LawController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Law::query();

        $query->with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('official_ref', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'date_of_enactment');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('date_of_enactment', 'desc');
        }

        if ($request->has('per_page')) {
            $laws = $query->paginate($request->input('per_page', 15));
        } else {
            $laws = $query->get();
        }

        return response()->json($laws, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:law_categories,id'],
            'title' => ['required', 'string', 'max:255', 'unique:laws'],
            'official_ref' => ['required', 'string', 'max:255', 'unique:laws'],
            'date_of_enactment' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'link' => ['nullable', 'string', 'max:424', 'url'],
        ], [
            'category_id.exists' => 'A megadott jogszabály kategória nem létezik.',
            'title.required' => 'A jogszabály nevének megadása kötelező.',
            'title.string' => 'A jogszabály neve csak szöveg formátumú lehet.',
            'title.max' => 'A jogszabály neve maximum 255 karakter hosszú lehet.',
            'title.unique' => 'Ez a jogszabály név már létezik.',
            'official_ref.required' => 'A jogszabály azonosítószámának megadása kötelező.',
            'official_ref.string' => 'A jogszabály azonosítószáma csak szöveg formátumú lehet.',
            'official_ref.max' => 'A jogszabály azonosítószáma maximum 255 karakter hosszú lehet.',
            'official_ref.unique' => 'Ez az azonosítószám másik jogszabályhoz tartozik.',
            'date_of_enactment.date' => 'A hatálybalépés dátuma érvénytelen formátumú.',
            'is_active.boolean' => 'Az aktív állapot csak igaz vagy hamis érték lehet.',
            'link.string' => 'A link csak szöveg formátumú lehet.',
            'link.max' => 'A link maximum 424 karakter hosszú lehet.',
            'link.url' => 'A megadott link érvénytelen formátumú.',
        ]);

        $law = Law::create($validated);
        $law->load('category');

        return response()->json([
            'message' => 'A jogszabály sikeresen létrehozva.',
            'law' => $law
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Law::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['category'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $law = $query->find($id);

        if (!$law) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($law, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $law = Law::find($id);

        if (!$law) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'nullable', 'exists:law_categories,id'],
            'title' => ['sometimes', 'string', 'max:255', Rule::unique('laws')->ignore($law->id)],
            'official_ref' => ['sometimes', 'string', 'max:255', Rule::unique('laws')->ignore($law->id)],
            'date_of_enactment' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'link' => ['sometimes', 'nullable', 'string', 'max:424', 'url'],
        ],
        [
            'category_id.exists' => 'A megadott kategória nem létezik.',
            'title.string' => 'A jogszabály címe kizárólag nem üres, szöveg formátumú lehet.',
            'title.max' => 'A jogszabály címe maximum 255 karakter hosszú lehet.',
            'title.unique' => 'Ez a jogszabály cím már létezik.',
            'official_ref.string' => 'A jogszabály hivatalos hivatkozása kizárólag nem üres, szöveg formátumú lehet.',
            'official_ref.max' => 'A jogszabály hivatalos hivatkozása maximum 255 karakter hosszú lehet.',
            'official_ref.unique' => 'Ez a hivatalos hivatkozás már létezik.',
            'date_of_enactment.date' => 'A hatálybalépés dátuma érvénytelen formátumú.',
            'is_active.boolean' => 'Az aktív állapot csak igaz vagy hamis érték lehet.',
            'link.string' => 'A link kizárólag nem üres, szöveg formátumú lehet.',
            'link.max' => 'A link maximum 424 karakter hosszú lehet.',
            'link.url' => 'A megadott link érvénytelen formátumú.',
        ]);

        $law->update($validated);
        $law->load('category');

        return response()->json([
            'message' => 'A jogszabály adatai sikeresen frissítve lettek.',
            'law' => $law
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $law = Law::find($id);

        if (!$law) {
            return response()->json([
                'message' => 'A megadott azonosítójú jogszabály nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $title = $law->title;
        $officialRef = $law->official_ref;

        $law->delete();

        return response()->json([
            'message' => "{$officialRef} ({$title}) jogszabály sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
