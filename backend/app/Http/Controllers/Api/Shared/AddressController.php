<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'exists:locations,id'],
            'country' => ['required', 'string', 'max:100'],
            'postalcode' => ['required', 'integer'],
            'city' => ['required', 'string', 'max:100'],
            'road_name' => ['required', 'string', 'max:100'],
            'public_space_type' => ['required', 'string', 'max:50'],
            'building_number' => ['required', 'string', 'max:50'],
        ],
        [
            'location_id.exists' => 'A megadott helyszín nem létezik.',
            'country.required' => 'Az ország megadása kötelező.',
            'country.string' => 'Az ország csak szöveg formátumú lehet.',
            'country.max' => 'Az ország neve maximum 100 karakter hosszú lehet.',
            'postalcode.required' => 'Az irányítószám megadása kötelező.',
            'postalcode.integer' => 'Az irányítószám csak szám lehet.',
            'city.required' => 'A város megadása kötelező.',
            'city.string' => 'A város csak szöveg formátumú lehet.',
            'city.max' => 'A város neve maximum 100 karakter hosszú lehet.',
            'road_name.required' => 'A közterület nevének megadása kötelező.',
            'road_name.string' => 'A közterület neve csak szöveg formátumú lehet.',
            'road_name.max' => 'A közterület neve maximum 100 karakter hosszú lehet.',
            'public_space_type.required' => 'A közterület jellegének megadása kötelező.',
            'public_space_type.string' => 'A közterület jellege csak szöveg formátumú lehet.',
            'public_space_type.max' => 'A közterület jellege maximum 50 karakter hosszú lehet.',
            'building_number.required' => 'A házszám megadása kötelező.',
            'building_number.string' => 'A házszám csak szöveg formátumú lehet.',
            'building_number.max' => 'A házszám maximum 50 karakter hosszú lehet.',
        ]);

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
                'message' => 'A megadott azonosítójú cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($address, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json([
                'message' => 'A megadott azonosítójú cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'location_id' => ['sometimes', 'nullable', 'exists:locations,id'],
            'country' => ['sometimes', 'string', 'max:100'],
            'postalcode' => ['sometimes', 'integer'],
            'city' => ['sometimes', 'string', 'max:100'],
            'road_name' => ['sometimes', 'string', 'max:100'],
            'public_space_type' => ['sometimes', 'string', 'max:50'],
            'building_number' => ['sometimes', 'string', 'max:50'],
        ], [
            'location_id.exists' => 'A megadott helyszín nem létezik.',
            'country.string' => 'Az ország kizárólag nem üres, szöveg formátumú lehet.',
            'country.max' => 'Az ország neve maximum 100 karakter hosszú lehet.',
            'postalcode.integer' => 'Az irányítószám csak szám lehet.',
            'city.string' => 'A város kizárólag nem üres, szöveg formátumú lehet.',
            'city.max' => 'A város neve maximum 100 karakter hosszú lehet.',
            'road_name.string' => 'A közterület neve kizárólag nem üres, szöveg formátumú lehet.',
            'road_name.max' => 'A közterület neve maximum 100 karakter hosszú lehet.',
            'public_space_type.string' => 'A közterület jellege kizárólag nem üres, szöveg formátumú lehet.',
            'public_space_type.max' => 'A közterület jellege maximum 50 karakter hosszú lehet.',
            'building_number.string' => 'A házszám kizárólag nem üres, szöveg formátumú lehet.',
            'building_number.max' => 'A házszám maximum 50 karakter hosszú lehet.',
        ]);

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
                'message' => 'A megadott azonosítójú cím nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($address->projects()->count() > 0) {
            return response()->json([
                'message' => 'Ez a cím projektekhez van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $addressInfo = $address->postalcode . ' ' .$address->city . ', ' . $address->road_name . ' ' . $address->public_space_type . ' ' . $address->building_number;

        $address->delete();
        return response()->json([
            'message' => "$addressInfo cím sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
