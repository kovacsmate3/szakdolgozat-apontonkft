<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Trip::with(['car', 'user', 'startLocation', 'destinationLocation']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('car_id')) {
            $query->where('car_id', $request->input('car_id'));
        }

        if ($request->has('start_location_id')) {
            $query->where('start_location_id', $request->input('start_location_id'));
        }

        if ($request->has('destination_location_id')) {
            $query->where('destination_location_id', $request->input('destination_location_id'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_time', [$request->input('start_date'), $request->input('end_date')]);
        } else if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->input('start_date'));
        } else if ($request->has('end_date')) {
            $query->where('start_time', '<=', $request->input('end_date'));
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'start_time');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('start_time', 'desc');
        }

        if ($request->has('per_page')) {
            $trips = $query->paginate($request->input('per_page', 15));
        } else {
            $trips = $query->get();
        }

        return response()->json($trips, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'car_id' => ['required', 'exists:cars,id'],
            'start_location_id' => [
                'required',
                'exists:locations,id',
                'different:destination_location_id'
            ],
            'destination_location_id' => [
                'required',
                'exists:locations,id'
            ],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'planned_distance' => ['nullable', 'numeric', 'min:0'],
            'actual_distance' => ['nullable', 'numeric', 'min:0'],
            'start_odometer' => ['nullable', 'integer', 'min:0'],
            'end_odometer' => ['nullable', 'integer', 'min:0', 'gte:start_odometer'],
            'planned_duration' => ['nullable', 'date_format:H:i:s'],
            'actual_duration' => ['nullable', 'date_format:H:i:s'],
        ];

        $messages = [
            'car_id.required' => 'A jármű azonosító megadása kötelező.',
            'car_id.exists' => 'A megadott jármű nem létezik.',

            'start_location_id.required' => 'Az indulási helyszín megadása kötelező.',
            'start_location_id.exists' => 'A megadott indulási helyszín nem létezik.',
            'start_location_id.different' => 'Az indulási és érkezési helyszín nem lehet azonos.',

            'destination_location_id.required' => 'Az érkezési helyszín megadása kötelező.',
            'destination_location_id.exists' => 'A megadott érkezési helyszín nem létezik.',

            'start_time.required' => 'Az indulási idő megadása kötelező.',
            'start_time.date' => 'Az indulási idő érvénytelen dátum formátumú.',

            'end_time.date' => 'Az érkezési idő érvénytelen dátum formátumú.',
            'end_time.after_or_equal' => 'Az érkezési idő nem lehet korábbi, mint az indulási idő.',

            'planned_distance.numeric' => 'A tervezett távolság csak szám lehet.',
            'planned_distance.min' => 'A tervezett távolság nem lehet negatív érték.',

            'actual_distance.numeric' => 'A tényleges távolság csak szám lehet.',
            'actual_distance.min' => 'A tényleges távolság nem lehet negatív érték.',

            'start_odometer.integer' => 'A kilométeróra kezdő állása csak egész szám lehet.',
            'start_odometer.min' => 'A kilométeróra kezdő állása nem lehet negatív érték.',

            'end_odometer.integer' => 'A kilométeróra záró állása csak egész szám lehet.',
            'end_odometer.min' => 'A kilométeróra záró állása nem lehet negatív érték.',
            'end_odometer.gte' => 'A kilométeróra záró állása nem lehet kisebb, mint a kezdő állása.',

            'planned_duration.date_format' => 'A tervezett időtartam érvénytelen formátumú (óra:perc:másodperc).',
            'actual_duration.date_format' => 'A tényleges időtartam érvénytelen formátumú (óra:perc:másodperc).',
        ];

        $userRole = Auth::user()->role->slug ?? null;

        if (in_array($userRole, ['admin', 'webdev'])) {
            $rules['user_id'] = ['required', 'exists:users,id'];
            $messages['user_id.required'] = 'A felhasználó azonosító megadása kötelező.';
            $messages['user_id.exists'] = 'A megadott felhasználó nem létezik.';
        }

        $validated = $request->validate($rules, $messages);

        if (!in_array($userRole, ['admin', 'webdev'])) {
            $validated['user_id'] = Auth::id();
        }

        $trip = Trip::create($validated);
        $trip->load(['car', 'user', 'startLocation', 'destinationLocation']);

        return response()->json([
            'message' => 'Az út sikeresen létrehozva.',
            'trip' => $trip
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Trip::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['car', 'user', 'startLocation', 'destinationLocation'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $trip = $query->find($id);

        if (!$trip) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') út nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($trip, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') út nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $userRole = Auth::user()->role->slug ?? null;
        if (!in_array($userRole, ['admin', 'webdev']) && $trip->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani ezt az utat.'
            ], Response::HTTP_FORBIDDEN);
        }

        $rules = [
            'car_id' => ['sometimes', 'exists:cars,id'],
            'start_location_id' => [
                'sometimes',
                'exists:locations,id',
                function ($attribute, $value, $fail) use ($request, $trip) {
                    $destLocationId = $request->input('destination_location_id', $trip->destination_location_id);
                    if ($value == $destLocationId) {
                        $fail('Az indulási és érkezési helyszín nem lehet azonos.');
                    }
                }
            ],
            'destination_location_id' => [
                'sometimes',
                'exists:locations,id',
                function ($attribute, $value, $fail) use ($request, $trip) {
                    $startLocationId = $request->input('start_location_id', $trip->start_location_id);
                    if ($value == $startLocationId) {
                        $fail('Az indulási és érkezési helyszín nem lehet azonos.');
                    }
                }
            ],
            'start_time' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) use ($request, $trip) {
                    $endTime = $request->input('end_time', $trip->end_time);
                    if ($value && ($value > $endTime)) {
                        $fail('Az indulási idő nem lehet későbbi, mint az érkezési idő.');
                    }
                }
            ],
            'end_time' => [
                'sometimes',
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request, $trip) {
                    $startTime = $request->input('start_time', $trip->start_time);
                    if ($value && ($value < $startTime)) {
                        $fail('Az érkezési idő nem lehet korábbi, mint az indulási idő.');
                    }
                }
            ],
            'planned_distance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'actual_distance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'start_odometer' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'end_odometer' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $trip) {
                    $startOdometer = $request->input('start_odometer', $trip->start_odometer);
                    if ($value !== null && $startOdometer !== null && $value < $startOdometer) {
                        $fail('A kilométeróra záró állása nem lehet kisebb, mint a kezdő állása.');
                    }
                }
            ],
            'planned_duration' => ['sometimes', 'nullable', 'date_format:H:i:s'],
            'actual_duration' => ['sometimes', 'nullable', 'date_format:H:i:s'],
        ];

        $messages = [
            'car_id.exists' => 'A megadott jármű nem létezik.',

            'start_location_id.exists' => 'A megadott indulási helyszín nem létezik.',

            'destination_location_id.exists' => 'A megadott érkezési helyszín nem létezik.',

            'start_time.date' => 'Az indulási idő érvénytelen dátum formátumú.',

            'end_time.date' => 'Az érkezési idő érvénytelen dátum formátumú.',

            'planned_distance.numeric' => 'A tervezett távolság csak szám lehet.',
            'planned_distance.min' => 'A tervezett távolság nem lehet negatív érték.',

            'actual_distance.numeric' => 'A tényleges távolság csak szám lehet.',
            'actual_distance.min' => 'A tényleges távolság nem lehet negatív érték.',

            'start_odometer.integer' => 'A kilométeróra kezdő állása csak egész szám lehet.',
            'start_odometer.min' => 'A kilométeróra kezdő állása nem lehet negatív érték.',

            'end_odometer.integer' => 'A kilométeróra záró állása csak egész szám lehet.',
            'end_odometer.min' => 'A kilométeróra záró állása nem lehet negatív érték.',
            'end_odometer.gte' => 'A kilométeróra záró állása nem lehet kisebb, mint a kezdő állása.',

            'planned_duration.date_format' => 'A tervezett időtartam érvénytelen formátumú (óra:perc:másodperc).',
            'actual_duration.date_format' => 'A tényleges időtartam érvénytelen formátumú (óra:perc:másodperc).',
        ];

        if (in_array($userRole, ['admin', 'webdev'])) {
            $rules['user_id'] = ['sometimes', 'exists:users,id'];
            $messages['user_id.exists'] = 'A megadott felhasználó nem létezik.';
        }

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['start_time'])) {
            $validated['start_time'] = now();
        }

        if (!in_array($userRole, ['admin', 'webdev']) && isset($validated['user_id'])) {
            unset($validated['user_id']);
        }

        $trip->update($validated);
        $trip->load(['car', 'user', 'startLocation', 'destinationLocation']);

        return response()->json([
            'message' => 'Az út adatai sikeresen frissítve lettek.',
            'trip' => $trip
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') út nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $userRole = Auth::user()->role->slug ?? null;
        if (!in_array($userRole, ['admin', 'webdev']) && $trip->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Nincs jogosultsága törölni ezt az utat.'
            ], Response::HTTP_FORBIDDEN);
        }

        $tripInfo = 'Út (' . date('Y-m-d H:i', strtotime($trip->start_time)) . '): ' .
            $trip->startLocation->name . ' -> ' .
            $trip->destinationLocation->name;

        $trip->delete();

        return response()->json([
            'message' => "$tripInfo sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
