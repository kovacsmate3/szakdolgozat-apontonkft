<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class FuelPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fuelPrices = FuelPrice::orderBy('period', 'desc')->get();
        return response()->json($fuelPrices, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => [
                'required',
                'date',
                Rule::unique('fuel_prices')->where(function ($query) use ($request) {
                    $period = \Carbon\Carbon::parse($request->period);
                    return $query
                        ->whereYear('period', $period->year)
                        ->whereMonth('period', $period->month);
                }),
            ],
            'petrol' => ['required', 'numeric', 'min:0'],
            'mixture' => ['required', 'numeric', 'min:0'],
            'diesel' => ['required', 'numeric', 'min:0'],
            'lp_gas' => ['required', 'numeric', 'min:0'],
        ], [
            'period.required' => 'Az időszak megadása kötelező.',
            'period.date' => 'Az időszak érvénytelen dátum formátumú.',
            'period.unique' => 'Erre az időszakra már létezik üzemanyagár rekord.',
            'petrol.required' => 'A benzin árának megadása kötelező.',
            'petrol.numeric' => 'A benzin ára csak szám lehet.',
            'petrol.min' => 'A benzin ára nem lehet negatív.',
            'mixture.required' => 'A keverék árának megadása kötelező.',
            'mixture.numeric' => 'A keverék ára csak szám lehet.',
            'mixture.min' => 'A keverék ára nem lehet negatív.',
            'diesel.required' => 'A dízel árának megadása kötelező.',
            'diesel.numeric' => 'A dízel ára csak szám lehet.',
            'diesel.min' => 'A dízel ára nem lehet negatív.',
            'lp_gas.required' => 'Az LPG gáz árának megadása kötelező.',
            'lp_gas.numeric' => 'Az LPG gáz ára csak szám lehet.',
            'lp_gas.min' => 'Az LPG gáz ára nem lehet negatív.',
        ]);

        $fuelPrice = FuelPrice::create($validated);

        return response()->json([
            'message' => 'Az üzemanyagár sikeresen létrehozva.',
            'fuel_price' => $fuelPrice
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fuelPrice = FuelPrice::find($id);

        if (!$fuelPrice) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') üzemanyagár nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($fuelPrice, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fuelPrice = FuelPrice::find($id);

        if (!$fuelPrice) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') üzemanyagár nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'period' => ['sometimes', 'date', Rule::unique('fuel_prices')->ignore($fuelPrice->id)],
            'petrol' => ['sometimes', 'numeric', 'min:0'],
            'mixture' => ['sometimes', 'numeric', 'min:0'],
            'diesel' => ['sometimes', 'numeric', 'min:0'],
            'lp_gas' => ['sometimes', 'numeric', 'min:0'],
        ], [
            'period.date' => 'Az időszak érvénytelen dátum formátumú.',
            'period.unique' => 'Erre az időszakra már létezik másik üzemanyagár rekord.',
            'petrol.numeric' => 'A benzin ára csak szám lehet.',
            'petrol.min' => 'A benzin ára nem lehet negatív.',
            'mixture.numeric' => 'A keverék ára csak szám lehet.',
            'mixture.min' => 'A keverék ára nem lehet negatív.',
            'diesel.numeric' => 'A dízel ára csak szám lehet.',
            'diesel.min' => 'A dízel ára nem lehet negatív.',
            'lp_gas.numeric' => 'Az LPG gáz ára csak szám lehet.',
            'lp_gas.min' => 'Az LPG gáz ára nem lehet negatív.',
        ]);

        $fuelPrice->update($validated);

        return response()->json([
            'message' => 'Az üzemanyagár adatai sikeresen frissítve lettek.',
            'fuel_price' => $fuelPrice
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fuelPrice = FuelPrice::find($id);

        if (!$fuelPrice) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') üzemanyagár nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $period = $fuelPrice->period->format('Y-m-d');

        $fuelPrice->delete();

        return response()->json([
            'message' => "A(z) {$period} időszak üzemanyagárai sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
