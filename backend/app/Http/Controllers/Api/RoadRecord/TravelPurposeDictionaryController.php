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

        $user = Auth::user();

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

        // Ha a felhasználó sajátjait kérjük csak
        if ($request->has('my_records') && $request->boolean('my_records')) {
            $query->where('user_id', $user->id);
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

        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        // Ellenőrizzük, hogy nem admin felhasználó próbál-e rendszerszintű rekordot létrehozni
        if (!$isAdmin && $request->has('is_system') && $request->boolean('is_system')) {
            return response()->json([
                'message' => 'Csak adminisztrátor hozhat létre rendszerszintű utazási célt.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate(
            [
                'travel_purpose' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', 'max:50'],
                'note' => ['nullable', 'string'],
                'is_system' => ['sometimes', 'boolean'],
                'user_id' => ['sometimes', 'exists:users,id'],
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
                'user_id.exists' => 'A megadott felhasználó nem létezik.',
            ]
        );

        if (!$isAdmin || !isset($validated['user_id'])) {
            $validated['user_id'] = $user->id;
        }

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

        if (!$this->canViewRecord($travelPurpose)) {
            return response()->json([
                'message' => 'Nincs jogosultsága megtekinteni ezt az utazási célt.'
            ], Response::HTTP_FORBIDDEN);
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

        // Ellenőrizzük a módosítási jogosultságot
        if (!$this->canEditRecord($travelPurpose)) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani ezt az utazási célt.'
            ], Response::HTTP_FORBIDDEN);
        }

        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        $validated = $request->validate(
            [
                'travel_purpose' => ['sometimes', 'string', 'max:100'],
                'type' => ['sometimes', 'string', 'max:50'],
                'note' => ['sometimes', 'nullable', 'string'],
                'is_system' => ['sometimes', 'boolean'],
                'user_id' => ['sometimes', 'exists:users,id'],
            ],
            [
                'travel_purpose.string' => 'Az utazás célja csak szöveg formátumú lehet.',
                'travel_purpose.max' => 'Az utazás célja maximum 100 karakter hosszú lehet.',

                'type.string' => 'A típus csak szöveg formátumú lehet.',
                'type.max' => 'A típus maximum 50 karakter hosszú lehet.',

                'note.string' => 'A megjegyzés csak szöveg formátumú lehet.',

                'is_system.boolean' => 'A rendszerszintű jelölés csak igaz vagy hamis értéket vehet fel.',
                'user_id.exists' => 'A megadott felhasználó nem létezik.',
            ]
        );

        // Csak admin módosíthatja a user_id-t
        if (!$isAdmin && isset($validated['user_id'])) {
            unset($validated['user_id']);
        }

        // Csak admin tehet egy rekordot rendszerszintűvé
        if (!$isAdmin && isset($validated['is_system']) && $validated['is_system']) {
            unset($validated['is_system']);
        }

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

        // Ellenőrizzük a törlési jogosultságot
        if (!$this->canDeleteRecord($travelPurpose)) {
            return response()->json([
                'message' => 'Nincs jogosultsága törölni ezt az utazási célt.'
            ], Response::HTTP_FORBIDDEN);
        }

        $travelPurpose->delete();

        return response()->json([
            'message' => "'{$travelPurpose->travel_purpose}' utazási cél sikeresen törölve."
        ], Response::HTTP_OK);
    }

    /**
     * Ellenőrzi, hogy a felhasználó megtekintheti-e a rekordot
     */
    private function canViewRecord(TravelPurposeDictionary $travelPurpose): bool
    {
        return true;
    }

    /**
     * Ellenőrzi, hogy a felhasználó szerkesztheti-e a rekordot
     */
    private function canEditRecord(TravelPurposeDictionary $travelPurpose): bool
    {
        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        // Admin bármit szerkeszthet
        if ($isAdmin) {
            return true;
        }

        // Rendszerszintű rekordokat csak admin szerkeszthet
        if ($travelPurpose->is_system) {
            return false;
        }

        // Nem admin csak a saját nem rendszerszintű rekordjait szerkesztheti
        return $travelPurpose->user_id === $user->id;
    }

    /**
     * Ellenőrzi, hogy a felhasználó törölheti-e a rekordot
     */
    private function canDeleteRecord(TravelPurposeDictionary $travelPurpose): bool
    {
        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        // Admin bármit törölhet
        if ($isAdmin) {
            return true;
        }

        // Rendszerszintű rekordokat csak admin törölhet
        if ($travelPurpose->is_system) {
            return false;
        }

        // Nem admin csak a saját nem rendszerszintű rekordjait törölheti
        return $travelPurpose->user_id === $user->id;
    }
}
