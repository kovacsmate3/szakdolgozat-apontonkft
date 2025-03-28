<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Location::query();
        if ($request->has('location_type')) {
            $query->where('location_type', $request->input('location_type'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('per_page')) {
            $locations = $query->paginate($request->input('per_page', 15));
        } else {
            $locations = $query->get();
        }

        return response()->json($locations, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_type' => ['required', 'string', 'in:partner,telephely,töltőállomás,bolt,egyéb'],
            'is_headquarter' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'A helyszín nevének megadása kötelező.',
            'name.string' => 'A helyszín neve csak szöveg formátumú lehet.',
            'name.max' => 'A helyszín neve maximum 255 karakter hosszú lehet.',

            'location_type.required' => 'A helyszín típusának megadása kötelező.',
            'location_type.string' => 'A helyszín típusa csak szöveg formátumú lehet.',
            'location_type.in' => 'A helyszín típusa csak a következők egyike lehet: partner, telephely, töltőállomás, bolt, egyéb.',

            'is_headquarter.boolean' => 'A központi telephely megjelölés csak igaz vagy hamis értéket vehet fel.',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'A helyszín sikeresen létrehozva.',
            'location' => $location
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Location::query();

        $with = ['address'];

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['travelPurposes', 'startTrips', 'destinationTrips', 'fuelExpenses'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $with[] = $include;
                }
            }
        }

        $location = $query->with($with)->find($id);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($location, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location_type' => ['sometimes', 'string', 'in:partner,telephely,töltőállomás,bolt,egyéb'],
            'is_headquarter' => ['sometimes', 'boolean'],
        ], [
            'name.string' => 'A helyszín neve kizárólag nem üres, szöveg formátumú lehet.',
            'name.max' => 'A helyszín neve maximum 255 karakter hosszú lehet.',

            'location_type.string' => 'A helyszín típusa kizárólag nem üres, szöveg formátumú lehet.',
            'location_type.in' => 'A helyszín típusa csak a következők egyike lehet: partner, telephely, töltőállomás, bolt, egyéb.',

            'is_headquarter.boolean' => 'A központi telephely megjelölés csak igaz vagy hamis értéket vehet fel.',
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'A helyszín adatai sikeresen frissítve lettek.',
            'location' => $location
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($location->startTrips()->count() > 0 || $location->destinationTrips()->count() > 0 || $location->fuelExpenses()->count() > 0) {
            return response()->json([
                'message' => 'Ez a helyszín utazásokhoz vagy üzemanyag költségekhez van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $locationName = $location->name;

        if ($location->address) {
            $location->address->delete();
        }

        $location->delete();

        return response()->json([
            'message' => "{$locationName} helyszín sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
