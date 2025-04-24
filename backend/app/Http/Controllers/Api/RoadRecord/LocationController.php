<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Location::with('address');
        if ($request->filled('location_type')) {
            // ha paraméterként tömböt kapunk, használjuk azt, egyébként bontsuk fel vesszőknél
            $types = $request->input('location_type');
            if (!is_array($types)) {
                $types = explode(',', $types);
            }
            // trimeljük meg a whitespace‑eket, és töröljük ki az üres elemeket
            $types = array_filter(array_map('trim', $types));

            $query->whereIn('location_type', $types);
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
    public function store(LocationRequest $request, AddressRequest $addressRequest)
    {
        $locationData = $request->validated();

        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        if ($locationData['location_type'] === 'telephely' && !$isAdmin) {
            return response()->json([
                'message' => 'Telephely létrehozására nincs jogosultsága.',
            ], Response::HTTP_FORBIDDEN);
        }

        $locationData['user_id'] = $user->id;

        // Címadatok kinyerése a validált adatokból
        $addressData = $addressRequest->validated();

        // Helyszín létrehozása címmel együtt
        $location = $this->addressService->createLocationWithAddress($locationData, $addressData);

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
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($location, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocationRequest $request, AddressRequest $addressRequest, string $id)
    {

        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        // Jogosultság ellenőrzése: csak a létrehozó vagy admin módosíthat
        if ($location->user_id != $user->id && !$isAdmin) {
            return response()->json([
                'message' => 'A helyszín módosítására nincs jogosultsága.'
            ], Response::HTTP_FORBIDDEN);
        }

        $locationData = $request->validated();

        // Jogosultság ellenőrzése: telephely típust csak admin állíthat be
        if (isset($locationData['location_type']) && $locationData['location_type'] === 'telephely' && !$isAdmin) {
            return response()->json([
                'message' => 'Telephely típus beállítására nincs jogosultsága.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Címadatok kinyerése ha vannak
        $addressData = $addressRequest->safe()->all();

        // Helyszín frissítése címmel együtt
        $location = $this->addressService->updateLocationWithAddress($location, $locationData, $addressData);

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
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') helyszín nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();
        $isAdmin = $user->role && $user->role->slug === 'admin';

        if ($location->user_id != $user->id && !$isAdmin) {
            return response()->json([
                'message' => 'A helyszín törlésére nincs jogosultsága.'
            ], Response::HTTP_FORBIDDEN);
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
