<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Trip::with(['car', 'user', 'startLocation', 'destinationLocation', 'travelPurpose']);

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
            'dict_id' => ['nullable', 'exists:travel_purpose_dictionaries,id'],
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
            'dict_id.exists' => 'A megadott utazási cél nem létezik.',
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

        $trip = Trip::create($validated);
        $trip->load(['car', 'user', 'startLocation', 'destinationLocation', 'travelPurpose']);

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
            $allowedIncludes = ['car', 'user', 'startLocation', 'destinationLocation', 'travelPurpose'];

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
        if (!in_array($userRole, ['admin']) && $trip->user_id !== Auth::id()) {
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
            'dict_id' => ['sometimes', 'nullable', 'exists:travel_purpose_dictionaries,id'],
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

            'dict_id.exists' => 'A megadott utazási cél nem létezik.',
        ];

        if (in_array($userRole, ['admin'])) {
            $rules['user_id'] = ['sometimes', 'exists:users,id'];
            $messages['user_id.exists'] = 'A megadott felhasználó nem létezik.';
        }

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['start_time'])) {
            $validated['start_time'] = now();
        }

        if (!in_array($userRole, ['admin']) && isset($validated['user_id'])) {
            unset($validated['user_id']);
        }

        $trip->update($validated);
        $trip->load(['car', 'user', 'startLocation', 'destinationLocation', 'travelPurpose']);

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
        if (!in_array($userRole, ['admin']) && $trip->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Nincs jogosultsága törölni ezt az utat.'
            ], Response::HTTP_FORBIDDEN);
        }

        $tripInfo = 'Út (' . date('Y-m-d H:i', strtotime($trip->start_time)) . '): ' .
            $trip->startLocation->name . ' -> ' .
            $trip->destinationLocation->name . ' : ' . $trip->travelPurpose;

        $trip->delete();

        return response()->json([
            'message' => "$tripInfo sikeresen törölve."
        ], Response::HTTP_OK);
    }

    public function exportToDoc(Request $request)
    {
        $user = $request->user();

        if ($user->role->slug !== 'admin') {
            return response()->json([
                'message' => 'Nincs jogosultsága az útinyilvántartás exportálásához.'
            ], Response::HTTP_FORBIDDEN);
        }

        // ------ 1. Validáció car_id, year, month és egyedi hibaüzenetek ------
        $messages = [
            'car_id.required' => 'A jármű kiválasztása kötelező.',
            'car_id.exists'   => 'A kiválasztott jármű nem létezik az adatbázisban.',
            'year.required'   => 'Az év megadása kötelező.',
            'year.integer'    => 'Az év csak szám lehet.',
            'year.min'        => 'Az évnek legalább 2000-nek kell lennie.',
            'year.max'        => 'Az év legfeljebb 2100 lehet.',
            'month.required'  => 'A hónap megadása kötelező.',
            'month.integer'   => 'A hónap csak szám lehet.',
            'month.min'       => 'A hónap értéke 1 és 12 között lehet.',
            'month.max'       => 'A hónap értéke 1 és 12 között lehet.',
        ];

        $data = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'year'   => 'required|integer|min:2000|max:2100',
            'month'  => 'required|integer|min:1|max:12',
        ], $messages);

        // 2. Carbon‐számítás a hónapra
        $start = \Carbon\Carbon::create($data['year'], $data['month'], 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $fuelPriceRecord = FuelPrice::where('period', $start->format('Y-m-01'))->first();
        if (!$fuelPriceRecord) {
            return response()->json([
                'message' => "Nincs üzemanyagár rekord a {$start->locale('hu')->isoFormat('YYYY. MMMMi')} időszakra. Kérjük, adjon meg üzemanyagárat erre a hónapra az exportálás előtt."
            ], Response::HTTP_NOT_FOUND);
        }

        // 3. Adatok lekérése csak erre a hónapra
        $trips = Trip::with([
            'startLocation.address',
            'destinationLocation.address',
            'travelPurpose',
            'car'
        ])
            ->where('car_id', $data['car_id'])
            ->whereBetween('start_time', [$start, $end])
            ->orderBy('start_time', 'asc')
            ->get();


        // Csak azokat az utazásokat tartjuk meg, amelyekhez "Üzleti" típusú cél tartozik
        $businessTrips = $trips->filter(function ($trip) {
            // 1. Prioritás: Közvetlen travel_purpose_id alapján
            if ($trip->travelPurpose) {
                return $trip->travelPurpose->type === 'Üzleti';
            }

            return false;
        });

        if ($businessTrips->isEmpty()) {
            return response()->json([
                'message' => 'Ebben a hónapban nincs üzleti célú utazási adat az adott járműhöz.'
            ], Response::HTTP_NOT_FOUND);
        }

        $businessTrips = $businessTrips->sortBy('start_time')->values();

        // ------ 4. Sablon meglétének ellenőrzése ------
        $templatePath = storage_path('templates/utnyilvantartas.docx');
        if (!file_exists($templatePath)) {
            return response()->json([
                'message' => "Sablon nem található: {$templatePath}"
            ], Response::HTTP_NOT_FOUND);
        }


        // ------ 5. Sablon betöltése try/catch-ben ------
        try {
            $tpl = new TemplateProcessor($templatePath);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hiba történt a sablon betöltésekor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ------ 6. Egyszeri mezők kitöltése ------
        $tpl->setValue('year',  $data['year']);
        $tpl->setValue('month', $start->locale('hu')->isoFormat('MMMM')); // magyar hónapnév
        $firstCar = $businessTrips->first()->car;
        $license_plate = $firstCar->license_plate;
        $tpl->setValue('license_plate',  $license_plate);
        $tpl->setValue('manufacturer',  $firstCar->manufacturer);
        $tpl->setValue('model',  $firstCar->model);
        $tpl->setValue('standard_consumption',  $firstCar->standard_consumption);
        $tpl->setValue('fuel_type',  $firstCar->fuel_type);

        // ------ Összesítő értékek inicializálása ------
        $totalDistance = 0;
        $totalConsumption = 0;
        $totalFuelCost = 0;

        // Üzemanyagtípus leképezése az adatbázis mezőire
        $fuelTypeMap = [
            'dízel' => 'diesel',
            'benzin' => 'petrol',
            'LPG gáz' => 'lp_gas',
            'keverék' => 'mixture'
        ];

        $fuelType = strtolower($firstCar->fuel_type);
        $fuelTypeField = $fuelTypeMap[$fuelType] ?? $fuelType;

        // Üzemanyagár elérése
        $unitPrice = 0;
        if ($fuelPriceRecord && isset($fuelPriceRecord->$fuelTypeField)) {
            $unitPrice = $fuelPriceRecord->$fuelTypeField;
        }

        // ------ 7. Dinamikus sorok ------
        $tpl->cloneRow('date', $businessTrips->count());
        foreach ($businessTrips as $i => $trip) {
            $index = $i + 1;
            $tpl->setValue("date#{$index}", $trip->end_time->format('Y‑m‑d H:i'));
            $tpl->setValue(
                "start_location#{$index}",
                $trip->startLocation->address->fullAddress()
            );
            $tpl->setValue(
                "end_location#{$index}",
                $trip->destinationLocation->address->fullAddress()
            );

            // Utazási cél meghatározása - elsősorban a közvetlen kapcsolatból
            if ($trip->travelPurpose) {
                $travelPurposeName = $trip->travelPurpose->travel_purpose;
            } else {
                // Másodsorban a helyszín céljai közül az első üzleti típusú
                $travelPurposeName = '';
                $travelPurposes = $trip->destinationLocation->travelPurposes;

                foreach ($travelPurposes as $purpose) {
                    if ($purpose->type === 'Üzleti') {
                        $travelPurposeName = $purpose->travel_purpose;
                        break;
                    }
                }
            }

            $tpl->setValue("travel_purpose#{$index}", $travelPurposeName);

            $tpl->setValue(
                "location_name#{$index}",
                $trip->destinationLocation->name
            );

            // Távolság és költség számítása a jelenlegi utazáshoz
            $distance = !is_null($trip->actual_distance)
                ? $trip->actual_distance
                : (
                    (!is_null($trip->start_odometer) && !is_null($trip->end_odometer))
                    ? $trip->end_odometer - $trip->start_odometer
                    : 0
                );

            $tpl->setValue(
                "distance#{$index}",
                $distance
            );

            // Kilométeróra állás cellába az end_odometer beszúrása
            $tpl->setValue("odometer#{$index}", $trip->end_odometer);


            // Fogyasztás számítása (liter)
            $consumption = $distance * ($firstCar->standard_consumption / 100);
            // Becsült költség számítása (forint)
            $estimated =  round($consumption * $unitPrice);

            $tpl->setValue("estimated_fuel_cost#{$index}", number_format($estimated, 0, '', ' '));

            // Összesítő adatok gyűjtése
            $totalDistance += $distance;
            $totalConsumption += $consumption;
            $totalFuelCost += $estimated;
        }

        // ------ 8. Összesítő adatok beállítása ------
        $tpl->setValue('total_distance', number_format($totalDistance, 1, ',', ''));
        $tpl->setValue('total_consumption', number_format($totalConsumption, 2, ',', ''));
        $tpl->setValue('total_fuel_cost', number_format($totalFuelCost, 0, ',', ' '));

        // ------ Havi teljes megtett távolság számítása (km óra alapján) ------
        $monthlyTotal = 0;
        if ($trips->count() >= 1) {
            $sortedTrips = $trips->sortBy('start_time')->values();
            $firstOdometer = $sortedTrips->first()->start_odometer;
            $lastOdometer = $sortedTrips->last()->end_odometer;

            if (!is_null($firstOdometer) && !is_null($lastOdometer)) {
                $monthlyTotal = $lastOdometer - $firstOdometer;
            }
        }

        // Ha nem volt elég adat, akkor 0-t írunk be
        $tpl->setValue('monthly_total', $monthlyTotal !== 0 ? number_format($monthlyTotal, 0, ',', ' ') : '—');

        $tmpFile = tempnam(sys_get_temp_dir(), 'utn_') . '.docx';
        $tpl->saveAs($tmpFile);

        return response()->download($tmpFile, "utnyilvantartas_{$license_plate}_{$data['year']}_{$data['month']}.docx")
            ->deleteFileAfterSend(true);
    }

    public function exportToExcel(Request $request)
    {
        Log::debug('exportToExcel belépés');

        $user = $request->user();
        Log::debug('User role:', ['role' => $user->role->slug]);

        if ($user->role->slug !== 'admin') {
            return response()->json([
                'message' => 'Nincs jogosultsága az útinyilvántartás exportálásához.'
            ], Response::HTTP_FORBIDDEN);
        }

        // ------ Validáció és alapadatok ------
        $messages = [
            'car_id.required' => 'A jármű kiválasztása kötelező.',
            'year.required'   => 'Az év megadása kötelező.',
            'month.integer'   => 'A hónap csak szám lehet 1 és 12 között.',
            'month.min'       => 'A hónap értéke minimum 1 lehet.',
            'month.max'       => 'A hónap értéke maximum 12 lehet.',
        ];

        // A hónap most már opcionális
        $rules = [
            'car_id' => 'required|exists:cars,id',
            'year'   => 'required|integer|min:2000|max:2100',
        ];

        // Ha a hónap meg van adva, akkor validáljuk
        if ($request->has('month')) {
            $rules['month'] = 'integer|min:1|max:12';
        }

        Log::debug('Validáció szabályok:', ['rules' => $rules]);
        $data = $request->validate($rules, $messages);
        Log::debug('Validáció után, $data:', $data);

        $carId = $data['car_id'];
        Log::debug('Kiválasztott car_id:', ['carId' => $carId]);

        // Időszak meghatározása
        $year = $data['year'];
        $isSingleMonth = $request->has('month');
        $targetMonth = $isSingleMonth ? $data['month'] : null;

        $monthNames = [
            1 => 'január',
            2 => 'február',
            3 => 'március',
            4 => 'április',
            5 => 'május',
            6 => 'június',
            7 => 'július',
            8 => 'augusztus',
            9 => 'szeptember',
            10 => 'október',
            11 => 'november',
            12 => 'december'
        ];

        $monthIndexes = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        // Gépjármű adatok lekérése
        $car = \App\Models\Car::findOrFail($carId);

        $navConsumption = $this->calculateNavConsumption($car->fuel_type, $car->capacity);

        // Üzemanyagárak lekérése mind a 12 hónapra
        $fuelPrices = [];
        $missingPriceMonths = [];

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($year, $m, 1);
            $fuelPrice = FuelPrice::where('period', $date->format('Y-m-01'))->first();

            if ($fuelPrice) {
                // Üzemanyagtípus leképezése az adatbázis mezőire
                $fuelTypeMap = [
                    'dízel' => 'diesel',
                    'benzin' => 'petrol',
                    'LPG gáz' => 'lp_gas',
                    'keverék' => 'mixture'
                ];
                $fuelType = strtolower($car->fuel_type);
                $fuelTypeField = $fuelTypeMap[$fuelType] ?? $fuelType;

                $fuelPrices[$m] = $fuelPrice->$fuelTypeField ?? 0;
            } else {
                $fuelPrices[$m] = 0;
                $missingPriceMonths[] = $monthNames[$m];
            }
        }


        // Figyelmeztetés hiányzó üzemanyagárakról
        if ($isSingleMonth) {
            // Ha csak egy hónapra exportálunk, elég ha csak ahhoz van meg az üzemanyagár
            if ($fuelPrices[$targetMonth] == 0) {
                return response()->json([
                    'message' => "Hiányzó üzemanyagár adat a következő hónaphoz: " . $monthNames[$targetMonth]
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            // Ha egész évre exportálunk, mindegyik hónaphoz kell üzemanyagár
            if (!empty($missingPriceMonths)) {
                return response()->json([
                    'message' => "Hiányzó üzemanyagár adatok: " . implode(', ', $missingPriceMonths)
                ], Response::HTTP_NOT_FOUND);
            }
        }

        // Excel sablon betöltése
        $templatePath = storage_path('templates/utnyilvantartas.xlsx');
        Log::debug('Template útvonala:', ['template' => $templatePath]);
        if (!file_exists($templatePath)) {
            Log::error('Sablon nem található');
            return response()->json([
                'message' => "Excel sablon nem található: {$templatePath}"
            ], Response::HTTP_NOT_FOUND);
        }

        // A helység munkalap kitöltése dinamikusan az utazási adatokból
        // Székhely azonosítója
        $headquarterId = 1;

        // Utazások lekérdezése, ahol a székhely szerepel
        $tripData = \App\Models\Trip::with(['startLocation.address', 'destinationLocation.address'])
            ->where(function ($query) use ($headquarterId) {
                $query->where('start_location_id', $headquarterId)
                    ->orWhere('destination_location_id', $headquarterId);
            })
            ->get();

        // Helyszínek és távolságok gyűjtése
        $locationDistances = [];

        foreach ($tripData as $trip) {
            if ($trip->start_location_id == $headquarterId) {
                // A székhelyről indulunk - a célállomás távolsága fontos
                $locationId = $trip->destination_location_id;
                $distance = $trip->actual_distance;
            } else {
                // A székhelyre érkeztünk - a kiindulópont távolsága fontos
                $locationId = $trip->start_location_id;
                $distance = $trip->actual_distance;
            }

            // Ha még nincs ilyen helyszín, vagy nagyobb a távolság, mint az eddig ismert
            if (!isset($locationDistances[$locationId]) || count($locationDistances[$locationId]['distances']) < 3) {
                if (!isset($locationDistances[$locationId])) {
                    // Új helyszín inicializálása
                    $locationDistances[$locationId] = [
                        'name' => ($trip->start_location_id == $headquarterId) ?
                            $trip->destinationLocation->name : $trip->startLocation->name,
                        'address' => ($trip->start_location_id == $headquarterId) ?
                            ($trip->destinationLocation->address ? $trip->destinationLocation->address->fullAddress() : '') : ($trip->startLocation->address ? $trip->startLocation->address->fullAddress() : ''),
                        'distances' => []
                    ];
                }

                // Távolság hozzáadása a listához (maximum 3-at tárolunk)
                $locationDistances[$locationId]['distances'][] = $distance;
            }
        }

        // Végleges adatok előkészítése (átlagos távolság számítása)
        $validLocations = [];
        foreach ($locationDistances as $locationId => $data) {
            // Átlagos távolság számítása
            $avgDistance = array_sum($data['distances']) / count($data['distances']);

            $validLocations[] = [
                'id' => $locationId,
                'name' => $data['name'],
                'address' => $data['address'],
                'distance' => round($avgDistance, 1)
            ];
        }

        // Helység táblázat feltöltése
        $row = 4;
        $column = 0;  // 0 = bal oldal (A,B,C), 1 = jobb oldal (E,F,G)

        try {
            // Excel sablon betöltése
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($templatePath);
            Log::debug('Spreadsheet objektum létrejött');

            // Debugolás: Ellenőrizzük, hogy a sablon betöltődött-e
            if (!$spreadsheet) {
                return response()->json([
                    'message' => "A munkafüzet betöltése sikertelen"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // ---- 1. HELYSÉG MUNKALAP KITÖLTÉSE ----
            $helysegSheet = $spreadsheet->getSheetByName('helység');
            if (!$helysegSheet) {
                // Ha nincs ilyen nevű, akkor a 2. lapot vesszük
                $helysegSheet = $spreadsheet->getSheet(0);
            }

            foreach ($validLocations as $location) {
                if ($column == 0) {
                    // Bal oszlopcsoport (A,B,C)
                    $helysegSheet->setCellValue('A' . $row, $location['address']);
                    $helysegSheet->setCellValue('B' . $row, $location['name']);
                    $helysegSheet->setCellValue('C' . $row, $location['distance']);
                    $column = 1;  // Következő a jobb oldali oszlopcsoport
                } else {
                    // Jobb oszlopcsoport (E,F,G)
                    $helysegSheet->setCellValue('E' . $row, $location['address']);
                    $helysegSheet->setCellValue('F' . $row, $location['name']);
                    $helysegSheet->setCellValue('G' . $row, $location['distance']);
                    $column = 0;  // Következő a bal oldali oszlopcsoport
                    $row++;       // És új sor
                }
            }

            // Számformátum beállítása a távolság oszlopokra
            $rowCount = ceil(count($validLocations) / 2) + 3;  // +3 a fejléc sorok miatt
            $helysegSheet->getStyle('C4:C' . $rowCount)->getNumberFormat()->setFormatCode('0.0');
            $helysegSheet->getStyle('G4:G' . $rowCount)->getNumberFormat()->setFormatCode('0.0');

            // ---- 1. FOGY-ELSZ MUNKALAP KITÖLTÉSE ----
            $fogyElszSheet = $spreadsheet->getSheetByName('Fogy-elsz.');
            if (!$fogyElszSheet) {
                // Ha nincs ilyen nevű, akkor a 2. lapot vesszük
                $fogyElszSheet = $spreadsheet->getSheet(1);
            }

            // Az év beállítása
            $fogyElszSheet->setCellValue('B5', $year . '. év-ben');

            // Üzemanyagárak kitöltése (C6-C17)
            for ($m = 1; $m <= 12; $m++) {
                $fogyElszSheet->setCellValue('C' . (5 + $m), $fuelPrices[$m]);
            }

            // Gépjármű adatainak kitöltése
            $fogyElszSheet->setCellValue('C19', $car->capacity);               // Hengerűrtartalom
            $fogyElszSheet->setCellValue('E22', $navConsumption);   // NAV szerinti fogyasztási norma
            $fogyElszSheet->setCellValue('D24', $car->license_plate);          // Rendszám
            $fogyElszSheet->setCellValue('D25', $car->manufacturer . ' ' . $car->model); // Típus

            // ---- 2. HAVI MUNKALAPOK KITÖLTÉSE ----
            // Meghatározni, mely hónapokat kell feldolgozni
            $monthsToProcess = $isSingleMonth ? [$targetMonth] : range(1, 12);
            Log::debug('Months to process:', ['months' => $monthsToProcess]);

            foreach ($monthsToProcess as $currentMonth) {
                Log::debug("Feldolgozás: hónap {$currentMonth}");
                // Az aktuális hónap utazásainak lekérése
                $start = Carbon::create($year, $currentMonth, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $trips = Trip::with([
                    'startLocation.address',
                    'destinationLocation.address',
                    'car',
                    'travelPurpose'
                ])
                    ->where('car_id', $carId)
                    ->whereBetween('start_time', [$start, $end])
                    ->orderBy('start_time')
                    ->get();

                if (!empty($trips)) {
                    $trips = $trips->filter(function ($trip) {
                        return $trip->travelPurpose && $trip->travelPurpose->type === 'Üzleti';
                    })->values();
                } else {
                    $trips = collect(); // Üres kollekció
                }
                if ($trips->isEmpty()) {
                    if ($isSingleMonth) {
                        return response()->json([
                            'message' => 'Az adott hónapban nincs egyetlen üzleti célú utazás sem ehhez a járműhöz.'
                        ], Response::HTTP_NOT_FOUND);
                    } else {
                        continue; // egész éves export esetén csak átugorjuk
                    }
                }

                try {
                    $monthlySheet = $spreadsheet->getSheetByName($monthIndexes[$currentMonth - 1]);

                    if (!$monthlySheet) {
                        // Ha nem találtuk név alapján, próbáljuk meg index alapján
                        try {
                            $monthlySheet = $spreadsheet->getSheet($currentMonth + 1); // +1 a helység és fogy-elsz miatt

                            if ($monthlySheet) {
                                // Állítsuk be a munkalap nevét
                                $monthlySheet->setTitle($monthIndexes[$currentMonth - 1]);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Nem sikerült a {$currentMonth}. havi munkalapot elérni: " . $e->getMessage());
                            continue; // Átugorjuk ezt a hónapot
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Hiba a havi munkalap elérésekor: " . $e->getMessage());
                    continue; // Átugorjuk ezt a hónapot
                }

                if (!$monthlySheet) {
                    \Log::warning("A(z) {$monthNames[$currentMonth]} havi munkalap nem található a sablonban, átugorjuk.");
                    continue;
                }

                $originalF2 = $monthlySheet->getCell('F2')->getValue();
                $monthlySheet->setCellValue('F2', $year . '. ' . $originalF2);

                // Adatok feltöltése a táblázatba - a 8. sortól kezdődően
                // Előző adatok törlése
                for ($i = 0; $i < 40; $i++) {
                    $row = 8 + $i;
                    $monthlySheet->setCellValue('B' . $row, '');      // Dátum
                    $monthlySheet->setCellValue('C' . $row, '');      // Honnan
                    $monthlySheet->setCellValue('D' . $row, '');      // Hová
                    $monthlySheet->setCellValue('E' . $row, '');      // Partner
                    $monthlySheet->setCellValue('F' . $row, '');      // Távolság
                    $monthlySheet->setCellValue('G' . $row, '');      // Megjegyzés
                }

                // Utazások feltöltése
                foreach ($trips as $i => $trip) {
                    if ($i >= 40) break; // Maximum 40 utazás

                    $row = 8 + $i;

                    // Dátum
                    $monthlySheet->setCellValue('B' . $row, $trip->end_time ? $trip->end_time->format('Y-m-d H:i') : $trip->start_time->format('Y-m-d H:i'));

                    $fromAddress = '';
                    if ($trip->startLocation && $trip->startLocation->address) {
                        $fromAddress = $trip->startLocation->address->fullAddress();
                    }
                    $monthlySheet->setCellValue('C' . $row, $fromAddress);

                    // Hová
                    $toAddress = '';
                    if ($trip->destinationLocation && $trip->destinationLocation->address) {
                        $toAddress = $trip->destinationLocation->address->fullAddress();
                    }
                    $monthlySheet->setCellValue('D' . $row, $toAddress);

                    // Partner neve
                    $partnerName = '';
                    if ($trip->destinationLocation) {
                        $partnerName = $trip->destinationLocation->name;
                    }
                    $monthlySheet->setCellValue('E' . $row, $partnerName);

                    // Megtett távolság
                    $distance = (!is_null($trip->start_odometer) && !is_null($trip->end_odometer))
                        ? $trip->end_odometer - $trip->start_odometer
                        : $trip->actual_distance;

                    $monthlySheet->setCellValue('F' . $row, $distance);
                }
            }

            // Fájl mentése
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);

            // Fájlnév meghatározása
            $filename = "utnyilvantartas_{$car->license_plate}_{$year}";
            if ($isSingleMonth) {
                $filename .= "_{$monthNames[$targetMonth]}";
            }
            $filename .= ".xlsx";

            Log::debug('Minden kész, fájl visszaküldése');
            return response()->download($tempFile, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('exportToExcel EXCEPTION', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Hiba történt az Excel exportálása során: ' . $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function calculateNavConsumption(string $rawFuelType, int $engineCapacity): float
    {
        $fuelType = strtolower(trim($rawFuelType));

        // Benzin alapú normák (NAV szerint)
        $petrolNorms = [
            1000 => 7.6,
            1500 => 8.6,
            2000 => 9.5,
            3000 => 11.4,
            PHP_INT_MAX => 13.3,
        ];

        // Dízel normák (külön skálázás)
        $dieselNorms = [
            1500 => 5.7,
            2000 => 6.7,
            3000 => 7.6,
            PHP_INT_MAX => 9.5,
        ];

        // Szorzók a benzin alaphoz képest
        $multipliers = [
            'benzin' => 1.0,
            'dízel' => 1.0,
            'lpg gáz' => 1.2,
            'cng' => 0.8,
            'lng' => 0.8,
        ];

        // Norma lekérő segédfüggvény
        $lookupNorm = function (array $norms, int $capacity): float {
            foreach ($norms as $maxCapacity => $norm) {
                if ($capacity <= $maxCapacity) {
                    return $norm;
                }
            }
            return 0;
        };

        if ($fuelType === 'dízel') {
            return $lookupNorm($dieselNorms, $engineCapacity);
        }

        $baseNorm = $lookupNorm($petrolNorms, $engineCapacity);
        $multiplier = $multipliers[$fuelType] ?? 1.0;

        return round($baseNorm * $multiplier, 1);
    }
}
