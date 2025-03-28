<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Car::with('user')->get();

        return response()->json($cars, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'car_type' => ['required', 'string', 'max:30'],
            'license_plate' => ['required', 'string', 'max:20', 'unique:cars'],
            'manufacturer' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'standard_consumption' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'fuel_tank_capacity' => ['required', 'integer', 'min:1'],
        ], [
            'user_id.required' => 'A felhasználó azonosító megadása kötelező.',
            'user_id.exists' => 'A megadott felhasználó nem létezik.',

            'car_type.required' => 'A jármű típusának megadása kötelező.',
            'car_type.string' => 'A jármű típusa csak szöveg formátumú lehet.',
            'car_type.max' => 'A jármű típusa maximum 30 karakter hosszú lehet.',

            'license_plate.required' => 'A rendszám megadása kötelező.',
            'license_plate.string' => 'A rendszám csak szöveg formátumú lehet.',
            'license_plate.max' => 'A rendszám maximum 20 karakter hosszú lehet.',
            'license_plate.unique' => 'Ez a rendszám már regisztrálva van.',

            'manufacturer.required' => 'A gyártó megadása kötelező.',
            'manufacturer.string' => 'A gyártó csak szöveg formátumú lehet.',
            'manufacturer.max' => 'A gyártó neve maximum 100 karakter hosszú lehet.',

            'model.required' => 'A modell megadása kötelező.',
            'model.string' => 'A modell csak szöveg formátumú lehet.',
            'model.max' => 'A modell maximum 100 karakter hosszú lehet.',

            'fuel_type.required' => 'Az üzemanyag típus megadása kötelező.',
            'fuel_type.string' => 'Az üzemanyag típus csak szöveg formátumú lehet.',
            'fuel_type.max' => 'Az üzemanyag típus maximum 50 karakter hosszú lehet.',

            'standard_consumption.required' => 'A normál fogyasztás megadása kötelező.',
            'standard_consumption.numeric' => 'A normál fogyasztás csak szám lehet.',
            'standard_consumption.min' => 'A normál fogyasztás nem lehet negatív érték.',

            'capacity.required' => 'A hengerűrtartalom megadása kötelező.',
            'capacity.integer' => 'A hengerűrtartalom csak egész szám lehet.',
            'capacity.min' => 'A hengerűrtartalom pozitív értéknek kell lennie.',

            'fuel_tank_capacity.required' => 'Az üzemanyagtartály kapacitás megadása kötelező.',
            'fuel_tank_capacity.integer' => 'Az üzemanyagtartály kapacitás csak egész szám lehet.',
            'fuel_tank_capacity.min' => 'Az üzemanyagtartály kapacitás pozitív értéknek kell lennie.',
        ]);

        $car = Car::create($validated);
        $car->load('user');

        return response()->json([
            'message' => 'A jármű sikeresen létrehozva.',
            'car' => $car
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = Car::query();

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['user'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        $car = $query->find($id);

        if (!$car) {
            return response()->json([
                'message' => 'A megadott azonosítójú jármű nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($car, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'message' => 'A megadott azonosítójú jármű nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'user_id' => ['sometimes', 'exists:users,id'],
            'car_type' => ['sometimes', 'string', 'max:30'],
            'license_plate' => ['sometimes', 'string', 'max:20', Rule::unique('cars')->ignore($car->id)],
            'manufacturer' => ['sometimes', 'string', 'max:100'],
            'model' => ['sometimes', 'string', 'max:100'],
            'fuel_type' => ['sometimes', 'string', 'max:50'],
            'standard_consumption' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'fuel_tank_capacity' => ['sometimes', 'integer', 'min:1'],
        ], [
            'user_id.exists' => 'A megadott felhasználó nem létezik.',

            'car_type.string' => 'A jármű típusa kizárólag nem üres, szöveg formátumú lehet.',
            'car_type.max' => 'A jármű típusa maximum 30 karakter hosszú lehet.',

            'license_plate.string' => 'A rendszám kizárólag nem üres, szöveg formátumú lehet.',
            'license_plate.max' => 'A rendszám maximum 20 karakter hosszú lehet.',
            'license_plate.unique' => 'Ez a rendszám már regisztrálva van.',

            'manufacturer.string' => 'A gyártó kizárólag nem üres, szöveg formátumú lehet.',
            'manufacturer.max' => 'A gyártó neve maximum 100 karakter hosszú lehet.',

            'model.string' => 'A modell kizárólag nem üres, szöveg formátumú lehet.',
            'model.max' => 'A modell maximum 100 karakter hosszú lehet.',

            'fuel_type.string' => 'Az üzemanyag típus kizárólag nem üres, szöveg formátumú lehet.',
            'fuel_type.max' => 'Az üzemanyag típus maximum 50 karakter hosszú lehet.',

            'standard_consumption.numeric' => 'A normál fogyasztás csak szám lehet.',
            'standard_consumption.min' => 'A normál fogyasztás nem lehet negatív érték.',

            'capacity.integer' => 'A hengerűrtartalom csak egész szám lehet.',
            'capacity.min' => 'A hengerűrtartalom pozitív értéknek kell lennie.',

            'fuel_tank_capacity.integer' => 'Az üzemanyagtartály kapacitás csak egész szám lehet.',
            'fuel_tank_capacity.min' => 'Az üzemanyagtartály kapacitás pozitív értéknek kell lennie.',
        ]);

        $car->update($validated);
        $car->load('user');

        return response()->json([
            'message' => 'A jármű adatai sikeresen frissítve lettek.',
            'car' => $car
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'message' => 'A megadott azonosítójú jármű nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check for related fuel expenses and trips
        if ($car->fuelExpenses()->count() > 0 || $car->trips()->count() > 0) {
            return response()->json([
                'message' => 'Ez a jármű tankolásokhoz vagy utazásokhoz van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $carInfo = $car->manufacturer . ' ' . $car->model . ' (' . $car->license_plate . ')';

        $car->delete();

        return response()->json([
            'message' => "$carInfo jármű sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
