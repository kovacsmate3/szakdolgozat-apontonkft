<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\FuelExpense;
use App\Models\Location;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FuelExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FuelExpense::query()->with(['car', 'user', 'location', 'trip']);

        if ($request->has('car_id')) {
            $query->where('car_id', $request->input('car_id'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->has('trip_id')) {
            $query->where('trip_id', $request->input('trip_id'));
        }

        if ($request->has('from_date')) {
            $query->where('expense_date', '>=', $request->input('from_date') . ' 00:00:00');
        }

        if ($request->has('to_date')) {
            $query->where('expense_date', '<=', $request->input('to_date') . ' 23:59:59');
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'expense_date');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('expense_date', 'desc');
        }

        if ($request->has('per_page')) {
            $fuelExpenses = $query->paginate($request->input('per_page', 15));
        } else {
            $fuelExpenses = $query->get();
        }

        return response()->json($fuelExpenses, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'car_id' => ['required', 'exists:cars,id'],
            'location_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $location = Location::find($value);
                    if (!$location) {
                        $fail('A megadott helyszín nem létezik.');
                    } elseif ($location->location_type !== 'töltőállomás') {
                        $fail('A megadott helyszín nem töltőállomás típusú.');
                    }
                }
            ],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'fuel_quantity' => ['required', 'numeric', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
            'trip_id' => ['nullable', 'exists:trips,id'],
        ];

        $messages = [
            'car_id.required' => 'A jármű azonosító megadása kötelező.',
            'car_id.exists' => 'A megadott jármű nem létezik.',

            'location_id.required' => 'A helyszín azonosító megadása kötelező.',

            'expense_date.required' => 'A költség dátumának megadása kötelező.',
            'expense_date.date' => 'A költség dátuma érvénytelen formátumú.',

            'amount.required' => 'Az összeg megadása kötelező.',
            'amount.numeric' => 'Az összeg csak szám lehet.',
            'amount.min' => 'Az összeg nem lehet negatív érték.',

            'currency.required' => 'A pénznem megadása kötelező.',
            'currency.string' => 'A pénznem csak szöveg formátumú lehet.',
            'currency.max' => 'A pénznem maximum 10 karakter hosszú lehet.',

            'fuel_quantity.required' => 'Az üzemanyag mennyiség megadása kötelező.',
            'fuel_quantity.numeric' => 'Az üzemanyag mennyiség csak szám lehet.',
            'fuel_quantity.min' => 'Az üzemanyag mennyiség nem lehet negatív érték.',

            'odometer.required' => 'A kilométeróra állásának megadása kötelező.',
            'odometer.integer' => 'A kilométeróra állása csak egész szám lehet.',
            'odometer.min' => 'A kilométeróra állása nem lehet negatív érték.',
            'trip_id.exists' => 'A megadott út nem létezik.',
        ];

        $userRole = Auth::user()->role->slug ?? null;

        if (in_array($userRole, ['admin'])) {
            $rules['user_id'] = ['required', 'exists:users,id'];
            $messages['user_id.required'] = 'A felhasználó azonosító megadása kötelező.';
            $messages['user_id.exists'] = 'A megadott felhasználó nem létezik.';
        }

        $validated = $request->validate($rules, $messages);

        if (!in_array($userRole, ['admin'])) {
            $validated['user_id'] = Auth::id();
        }

        // Ha van trip_id, ellenőrizzük, hogy megegyezik-e a car_id
        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            if ($trip && $trip->car_id != $validated['car_id']) {
                return response()->json([
                    'message' => 'A megadott út és jármű nem egyezik.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $fuelExpense = FuelExpense::create($validated);
        $fuelExpense->load(['car', 'user', 'location']);

        return response()->json([
            'message' => 'A tankolási adat sikeresen létrehozva.',
            'fuel_expense' => $fuelExpense
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fuelExpense = FuelExpense::with(['car', 'user', 'location', 'trip'])->find($id);

        if (!$fuelExpense) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') tankolási adat nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($fuelExpense, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fuelExpense = FuelExpense::find($id);

        if (!$fuelExpense) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') tankolási adat nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $userRole = Auth::user()->role->slug ?? null;
        if (!in_array($userRole, ['admin']) && $fuelExpense->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani ezt a tankolási adatot.'
            ], Response::HTTP_FORBIDDEN);
        }

        $rules = [
            'car_id' => ['sometimes', 'exists:cars,id'],
            'location_id' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    $location = Location::find($value);
                    if (!$location) {
                        $fail('A megadott helyszín nem létezik.');
                    } elseif ($location->location_type !== 'töltőállomás') {
                        $fail('A megadott helyszín nem töltőállomás típusú.');
                    }
                }
            ],
            'expense_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'fuel_quantity' => ['sometimes', 'numeric', 'min:0'],
            'odometer' => ['sometimes', 'integer', 'min:0'],
            'trip_id' => ['sometimes', 'nullable', 'exists:trips,id'],
        ];

        $messages = [
            'car_id.exists' => 'A megadott jármű nem létezik.',

            'expense_date.date' => 'A költség dátuma érvénytelen formátumú.',

            'amount.numeric' => 'Az összeg csak szám lehet.',
            'amount.min' => 'Az összeg nem lehet negatív érték.',

            'currency.string' => 'A pénznem csak szöveg formátumú lehet.',
            'currency.max' => 'A pénznem maximum 10 karakter hosszú lehet.',

            'fuel_quantity.numeric' => 'Az üzemanyag mennyiség csak szám lehet.',
            'fuel_quantity.min' => 'Az üzemanyag mennyiség nem lehet negatív érték.',

            'odometer.integer' => 'A kilométeróra állása csak egész szám lehet.',
            'odometer.min' => 'A kilométeróra állása nem lehet negatív érték.',
            'trip_id.exists' => 'A megadott út nem létezik.',
        ];

        if (in_array($userRole, ['admin'])) {
            $rules['user_id'] = ['sometimes', 'exists:users,id'];
            $messages['user_id.exists'] = 'A megadott felhasználó nem létezik.';
        }

        $validated = $request->validate($rules, $messages);

        if (!in_array($userRole, ['admin']) && isset($validated['user_id'])) {
            unset($validated['user_id']);
        }

        // Ha van trip_id, ellenőrizzük, hogy megegyezik-e a car_id
        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            $carId = $validated['car_id'] ?? $fuelExpense->car_id;

            if ($trip && $trip->car_id != $carId) {
                return response()->json([
                    'message' => 'A megadott út és jármű nem egyezik.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $fuelExpense->update($validated);
        $fuelExpense->load(['car', 'user', 'location', 'trip']);

        return response()->json([
            'message' => 'A tankolási adat sikeresen frissítve.',
            'fuel_expense' => $fuelExpense
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fuelExpense = FuelExpense::find($id);

        if (!$fuelExpense) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') tankolási adat nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $userRole = Auth::user()->role->slug ?? null;
        if (!in_array($userRole, ['admin']) && $fuelExpense->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Nincs jogosultsága törölni ezt a tankolási adatot.'
            ], Response::HTTP_FORBIDDEN);
        }

        $expenseDate = $fuelExpense->expense_date->format('Y-m-d H:i');
        $fuelQuantity = $fuelExpense->fuel_quantity;

        $fuelExpense->delete();

        return response()->json([
            'message' => "A(z) {$expenseDate} időpontban rögzített {$fuelQuantity} liter üzemanyag tankolás sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
