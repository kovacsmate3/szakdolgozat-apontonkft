<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Address::with('location');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('country', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('road_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'id');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortBy, $sortDir);
        }

        if ($request->has('per_page')) {
            $addresses = $query->paginate($request->input('per_page', 15));
        } else {
            $addresses = $query->get();
        }

        return response()->json($addresses, Response::HTTP_OK);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(AddressRequest $request)
    {
        $validated = $request->validated();

        $address = Address::create($validated);
        $address->load('location');

        return response()->json([
            'message' => 'A cím sikeresen létrehozva.',
            'address' => $address
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Address::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['location'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $address = $query->find($id);

        if (!$address) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($address, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AddressRequest $request, string $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();

        $address->update($validated);
        $address->load('location');

        return response()->json([
            'message' => 'A cím adatai sikeresen frissítve lettek.',
            'address' => $address
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($address->projects()->count() > 0) {
            return response()->json([
                'message' => 'Ez a cím projektekhez van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $addressInfo = $address->postalcode . ' ' . $address->city . ', ' . $address->road_name . ' ' . $address->public_space_type . ' ' . $address->building_number;

        $address->delete();
        return response()->json([
            'message' => "$addressInfo cím sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
