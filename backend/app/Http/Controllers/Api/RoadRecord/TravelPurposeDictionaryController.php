<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\TravelPurposeDictionary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TravelPurposeDictionaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TravelPurposeDictionary::query();

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('travel_purpose', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        $sortBy = $request->input('sort_by', 'travel_purpose');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 15);
        $travelPurposes = $request->has('per_page') ?
            $query->paginate($perPage) :
            $query->get();

        return response()->json($travelPurposes, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'travel_purpose' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', 'max:50'],
                'note' => ['nullable', 'string'],
                'is_system' => ['sometimes', 'boolean']
            ],
            [
                'travel_purpose.required' => 'Az utazás céljának megadása kötelező.',
                'travel_purpose.string' => 'Az utazás célja csak szöveg formátumú lehet.',
                'travel_purpose.max' => 'Az utazás célja maximum 100 karakter hosszú lehet.',

                'type.required' => 'A típus megadása kötelező.',
                'type.string' => 'A típus csak szöveg formátumú lehet.',
                'type.max' => 'A típus maximum 50 karakter hosszú lehet.',

                'note.string' => 'A megjegyzés csak szöveg formátumú lehet.',

                'is_system.boolean' => 'A rendszerszintű jelölés csak igaz vagy hamis értéket vehet fel.',
            ]
        );

        $travelPurpose = TravelPurposeDictionary::create($validated);

        return response()->json([
            'message' => 'Az utazási cél sikeresen létrehozva.',
            'travel_purpose' => $travelPurpose
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $travelPurpose = TravelPurposeDictionary::find($id);

        if (!$travelPurpose) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') utazási cél nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($travelPurpose, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $travelPurpose = TravelPurposeDictionary::find($id);

        if (!$travelPurpose) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') utazási cél nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($travelPurpose->is_system && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Rendszerszintű utazási cél nem módosítható.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate(
            [
                'travel_purpose' => ['sometimes', 'string', 'max:100'],
                'type' => ['sometimes', 'string', 'max:50'],
                'note' => ['sometimes', 'nullable', 'string'],
                'is_system' => ['sometimes', 'boolean']
            ],
            [
                'travel_purpose.string' => 'Az utazás célja csak szöveg formátumú lehet.',
                'travel_purpose.max' => 'Az utazás célja maximum 100 karakter hosszú lehet.',

                'type.string' => 'A típus csak szöveg formátumú lehet.',
                'type.max' => 'A típus maximum 50 karakter hosszú lehet.',

                'note.string' => 'A megjegyzés csak szöveg formátumú lehet.',

                'is_system.boolean' => 'A rendszerszintű jelölés csak igaz vagy hamis értéket vehet fel.',
            ]
        );

        $travelPurpose->update($validated);

        return response()->json([
            'message' => 'Az utazási cél adatai sikeresen frissítve lettek.',
            'travel_purpose' => $travelPurpose
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $travelPurpose = TravelPurposeDictionary::find($id);

        if (!$travelPurpose) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') utazási cél nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $isAdmin = Auth::user()->role && Auth::user()->role->slug === 'admin';
        if ($travelPurpose->is_system && !$isAdmin) {
            return response()->json([
                'message' => 'Rendszerszintű utazási cél nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $travelPurpose->delete();

        return response()->json([
            'message' => "'{$travelPurpose->travel_purpose}' utazási cél sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
