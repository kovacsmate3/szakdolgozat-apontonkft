<?php

namespace App\Http\Controllers\Api\RoadRecord;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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

    public function export(Request $request)
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

        $businessTrips = $businessTrips->values();

        // ------ 4. Sablon meglétének ellenőrzése ------
        $templatePath = storage_path('templates/utnyilvantartas.docx');
        if (!file_exists($templatePath)) {
            return response()->json([
                'error' => "Sablon nem található: {$templatePath}"
            ], Response::HTTP_NOT_FOUND);
        }


        // ------ 5. Sablon betöltése try/catch-ben ------
        try {
            $tpl = new TemplateProcessor($templatePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hiba történt a sablon betöltésekor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ------ 6. Egyszeri mezők kitöltése ------
        $tpl->setValue('year',  $data['year']);
        $tpl->setValue('month', $start->locale('hu')->isoFormat('MMMM')); // magyar hónapnév
        $firstCar = $businessTrips->first()->car;
        $model = strtolower($firstCar->model);
        $tpl->setValue('license_plate',  $firstCar->license_plate);
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
            $tpl->setValue("date#{$index}", $trip->start_time->format('Y‑m‑d H:i:s'));
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
            $distance = $trip->actual_distance;

            $tpl->setValue(
                "distance#{$index}",
                $distance
            );

            // Kilométeróra állás cellába az end_odometer beszúrása
            $tpl->setValue("odometer#{$index}", $trip->end_odometer);


            // Fogyasztás számítása (liter)
            $consumption = $distance * ($firstCar->standard_consumption / 100);
            // Becsült költség számítása (forint)
            $estimated = $consumption * $unitPrice;

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

        $tmpFile = tempnam(sys_get_temp_dir(), 'utn_') . '.docx';
        $tpl->saveAs($tmpFile);

        return response()->download($tmpFile, "utnyilvantartas_{$model}_{$data['year']}_{$data['month']}.docx")
            ->deleteFileAfterSend(true);
    }

    /*
    public function exportToExistingExcel(Request $request)
    {
        $user = $request->user();

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

        $data = $request->validate($rules, $messages);

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
        $car = \App\Models\Car::findOrFail($data['car_id']);

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
        if (!empty($missingPriceMonths) && ($isSingleMonth && in_array($targetMonth, array_keys($missingPriceMonths)))) {
            return response()->json([
                'message' => "Hiányzó üzemanyagár adatok: " . implode(', ', $missingPriceMonths)
            ], Response::HTTP_NOT_FOUND);
        }

        // Excel sablon betöltése
        $templatePath = storage_path('templates/utnyilvantartas.xlsx');
        if (!file_exists($templatePath)) {
            return response()->json([
                'error' => "Excel sablon nem található: {$templatePath}"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // Excel sablon betöltése
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($templatePath);

            // ---- 1. FOGY-ELSZ MUNKALAP KITÖLTÉSE ----
            $fogyElszSheet = $spreadsheet->getSheetByName('Fogy-elsz.');
            if (!$fogyElszSheet) {
                // Ha nincs ilyen nevű, akkor a 2. lapot vesszük
                $fogyElszSheet = $spreadsheet->getSheet(1);
            }

            // Az év beállítása
            $fogyElszSheet->setCellValue('C5', $year . ' év-ben');

            // Üzemanyagárak kitöltése (C6-C17)
            for ($m = 1; $m <= 12; $m++) {
                $fogyElszSheet->setCellValue('C' . (5 + $m), $fuelPrices[$m]);
            }

            // Gépjármű adatainak kitöltése
            $fogyElszSheet->setCellValue('C19', $car->capacity);               // Hengerűrtartalom
            $fogyElszSheet->setCellValue('E22', $car->standard_consumption);   // Fogyasztási norma
            $fogyElszSheet->setCellValue('D24', $car->license_plate);          // Rendszám
            $fogyElszSheet->setCellValue('D25', $car->car_type);               // Típus (manufacturer/model helyett car_type)

            // ---- 2. HAVI MUNKALAPOK KITÖLTÉSE ----
            // Meghatározni, mely hónapokat kell feldolgozni
            $monthsToProcess = $isSingleMonth ? [$targetMonth] : range(1, 12);
            $yearlyTotal = 0;

            foreach ($monthsToProcess as $currentMonth) {
                // Az aktuális hónap utazásainak lekérése
                $start = Carbon::create($year, $currentMonth, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $trips = Trip::with([
                    'startLocation.address',
                    'destinationLocation.address',
                    'car'
                ])
                    ->where('car_id', $data['car_id'])
                    ->whereBetween('start_time', [$start, $end])
                    ->orderBy('start_time')
                    ->get();

                // Keressük a megfelelő munkalapot
                $monthlySheet = $spreadsheet->getSheetByName($monthIndexes[$currentMonth - 1]);
                if (!$monthlySheet) {
                    // Ha nem találtuk, próbáljuk a szám szerinti indexekkel
                    $monthlySheet = $spreadsheet->getSheetByIndex($currentMonth + 1); // 0: helység, 1: Fogy-elsz, 2-13: havi lapok
                }

                if (!$monthlySheet) {
                    // Ha még mindig nem találtuk, átugorjuk ezt a hónapot
                    continue;
                }

                // A munkalap nevének beállítása
                $monthlySheet->setTitle($monthIndexes[$currentMonth - 1]);

                // A hónap nevének beállítása
                $monthlySheet->setCellValue('G3', $monthNames[$currentMonth] . ' hónap');

                // Adatok feltöltése a táblázatba - a 8. sortól kezdődően
                // Előző adatok törlése
                for ($i = 0; $i < 40; $i++) {
                    $row = 8 + $i;
                    $monthlySheet->setCellValue('A' . $row, $i + 1);  // Sorszám
                    $monthlySheet->setCellValue('B' . $row, '');      // Dátum
                    $monthlySheet->setCellValue('C' . $row, '');      // Honnan
                    $monthlySheet->setCellValue('D' . $row, '');      // Hová
                    $monthlySheet->setCellValue('E' . $row, '');      // Partner
                    $monthlySheet->setCellValue('F' . $row, '');      // Távolság
                    $monthlySheet->setCellValue('G' . $row, '');      // Megjegyzés
                }

                // Utazások feltöltése
                $totalDistance = 0;
                foreach ($trips as $i => $trip) {
                    if ($i >= 40) break; // Maximum 40 utazás

                    $row = 8 + $i;

                    // Dátum
                    $monthlySheet->setCellValue('B' . $row, $trip->start_time->format('Y-m-d'));

                    // Honnan
                    $fromAddress = $trip->startLocation->address;
                    $fromAddressString = $fromAddress->city . ', ' .
                        $fromAddress->road_name . ' ' .
                        $fromAddress->public_space_type . ' ' .
                        $fromAddress->building_number;
                    $monthlySheet->setCellValue('C' . $row, $fromAddressString);

                    // Hová
                    $toAddress = $trip->destinationLocation->address;
                    $toAddressString = $toAddress->city . ', ' .
                        $toAddress->road_name . ' ' .
                        $toAddress->public_space_type . ' ' .
                        $toAddress->building_number;
                    $monthlySheet->setCellValue('D' . $row, $toAddressString);

                    // Partner neve
                    $monthlySheet->setCellValue('E' . $row, $trip->destinationLocation->name);

                    // Megtett távolság
                    $distance = $trip->actual_distance;
                    $monthlySheet->setCellValue('F' . $row, $distance);

                    $totalDistance += $distance;
                }

                // A havi összegek számítása
                $fuelCostPerKm = $car->standard_consumption / 100 * $fuelPrices[$currentMonth];
                $kmCost = 9; // 9 Ft/km az alapérték
                $monthlyCost = $totalDistance * $fuelCostPerKm + $totalDistance * $kmCost;
                $yearlyTotal += $monthlyCost;

                // Beállítjuk az értéket az összesítő cellában az éves összesítőben
                $fogyElszSheet->setCellValue('H' . (4 + $currentMonth), $monthlyCost);
            }

            // Éves összesítő beállítása
            $fogyElszSheet->setCellValue('H17', $yearlyTotal);

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

            return response()->download($tempFile, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hiba történt az Excel exportálása során: ' . $e->getMessage() . ' - ' . $e->getLine()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    */
}
