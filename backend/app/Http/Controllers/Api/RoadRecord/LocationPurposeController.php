<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\TravelPurposeDictionary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationPurposeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $locationId)
    {
        $location = Location::find($locationId);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $locationId . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $travelPurposes = $location->travelPurposes()->get();

        return response()->json(['location' => $location, 'travel_purposes' => $travelPurposes], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $locationId)
    {
        $location = Location::find($locationId);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $locationId . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'travel_purposes' => ['required', 'array'],
            'travel_purposes.*' => ['required', 'exists:travel_purpose_dictionaries,id'],
        ], [
            'travel_purposes.required' => 'Legalább egy utazási célt meg kell adni.',
            'travel_purposes.array' => 'Az utazási célok formátuma helytelen. Az utazási célokat listaként (tömbként) kell megadni.',
            'travel_purposes.*.required' => 'Az utazási cél azonosítója nem lehet üres.',
            'travel_purposes.*.exists' => 'A megadott utazási célok közül egy vagy több nem szerepel az adatbázisban.',
        ]);

        $location->travelPurposes()->syncWithoutDetaching($validated['travel_purposes']);

        $updatedTravelPurposes = $location->travelPurposes()->get();

        return response()->json([
            'message' => 'Utazási célok sikeresen hozzárendelve a(z) ' . $location->name . ' helyszínhez.',
            'travel_purposes' => $updatedTravelPurposes
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $locationId, string $travelPurposeId)
    {
        $location = Location::find($locationId);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $locationId . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $travelPurpose = $location->travelPurposes()->where('travel_purpose_dictionaries.id', $travelPurposeId)->first();

        if (!$travelPurpose) {
            return response()->json([
                'message' => 'Az utazási cél (ID: ' . $travelPurposeId . ') nem tartozik a(z) ' . $location->name . ' helyszínhez, vagy nem létezik.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($travelPurpose, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $locationId, string $travelPurposeId)
    {
        $location = Location::find($locationId);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $locationId . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $travelPurpose = TravelPurposeDictionary::find($travelPurposeId);

        if (!$travelPurpose) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $travelPurposeId . ') utazási cél nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $locationTravelPurpose = $location->travelPurposes()->where('travel_purpose_dictionaries.id', $travelPurposeId)->first();

        if (!$locationTravelPurpose) {
            return response()->json([
                'message' => 'Az utazási cél (' . $travelPurpose->travel_purpose . ') nincs hozzárendelve a(z) ' . $location->name . ' helyszínhez.'
            ], Response::HTTP_NOT_FOUND);
        }

        $location->travelPurposes()->detach($travelPurposeId);

        return response()->json([
            'message' => 'Az utazási cél (' . $travelPurpose->travel_purpose . ') sikeresen eltávolítva a(z) ' . $location->name . ' helyszíntől.'
        ], Response::HTTP_OK);
    }

    /**
     * Sync all travel purposes for a location.
     */
    public function sync(Request $request, string $locationId)
    {
        $location = Location::find($locationId);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $locationId . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'travel_purposes' => ['required', 'array'],
            'travel_purposes.*' => ['required', 'exists:travel_purpose_dictionaries,id'],
        ], [
            'travel_purposes.required' => 'Legalább egy utazási célt meg kell adni.',
            'travel_purposes.array' => 'Az utazási célok formátuma helytelen. Az utazási célokat listaként (tömbként) kell megadni.',
            'travel_purposes.*.required' => 'Az utazási cél azonosítója nem lehet üres.',
            'travel_purposes.*.exists' => 'A megadott utazási célok közül egy vagy több nem szerepel az adatbázisban.',
        ]);

        $location->travelPurposes()->sync($validated['travel_purposes']);

        $updatedTravelPurposes = $location->travelPurposes()->get();

        return response()->json([
            'message' => 'Utazási célok sikeresen szinkronizálva a(z) ' . $location->name . ' helyszínhez.',
            'travel_purposes' => $updatedTravelPurposes
        ], Response::HTTP_OK);
    }
}
