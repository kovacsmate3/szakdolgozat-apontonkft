<?php

namespace App\Http\Controllers\Api\Shared\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use libphonenumber\PhoneNumberFormat;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role->slug !== 'admin') {
            return response()->json(['message' => 'Nincs jogosultságod a felhasználók lekérdezésére.'], 403);
        }

        $users = User::with('role')->get();
        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role->slug !== 'admin') {
            return response()->json(['message' => 'Kizárólag admin szerepkörrel lehet felhasználót létrehozni.'], 403);
        }

        $minimumBirthdate = Carbon::now()->subYears(18)->format('Y-m-d');

        $validated = $request->validate(
            [
                'username' => ['required', 'string', 'max:25', 'unique:users'],
                'firstname' => ['required', 'string', 'max:50'],
                'lastname' => ['required', 'string', 'max:50'],
                'birthdate' => [
                    'required',
                    'date',
                    'before_or_equal:' . $minimumBirthdate
                ],
                'phonenumber' => ['required', 'phone:HU', 'max:30', 'unique:users'],
                'email' => ['required', 'email', 'max:255', 'unique:users'],
                'password' => [
                    'required',
                    Rules\Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(3)
                ],
                'role_id' => ['required', 'exists:roles,id'],
            ],
            [
                'username.required' => 'A felhasználónév megadása kötelező.',
                'username.string' => 'A felhasználónév csak szöveg formátumú lehet.',
                'username.max' => 'A felhasználónév maximum 25 karakter hosszú lehet.',
                'username.unique' => 'Ez a felhasználónév már foglalt.',

                'firstname.required' => 'A keresztnév megadása kötelező.',
                'firstname.string' => 'A keresztnév csak szöveg formátumú lehet.',
                'firstname.max' => 'A keresztnév maximum 50 karakter hosszú lehet.',

                'lastname.required' => 'A vezetéknév megadása kötelező.',
                'lastname.string' => 'A vezetéknév csak szöveg formátumú lehet.',
                'lastname.max' => 'A vezetéknév maximum 50 karakter hosszú lehet.',

                'birthdate.required' => 'A születési dátum megadása kötelező.',
                'birthdate.date' => 'A születési dátum érvénytelen formátumú.',
                'birthdate.before_or_equal' => 'A felhasználónak legalább 18 évesnek kell lennie.',

                'phonenumber.required' => 'A telefonszám megadása kötelező.',
                'phonenumber.phone' => 'Érvénytelen magyar telefonszám formátum.',
                'phonenumber.max' => 'A telefonszám maximum 30 karakter hosszú lehet.',
                'phonenumber.unique' => 'Ez a telefonszám már regisztrálva van.',

                'email.required' => 'Az email cím megadása kötelező.',
                'email.email' => 'Érvénytelen email cím formátum.',
                'email.max' => 'Az email cím maximum 255 karakter hosszú lehet.',
                'email.unique' => 'Ez az email cím már regisztrálva van.',

                'password.required' => 'A jelszó megadása kötelező.',
                'password.min' => 'A jelszónak legalább :min karakter hosszúnak kell lennie.',
                'password.letters' => 'A jelszónak tartalmaznia kell legalább egy betűt.',
                'password.mixed' => 'A jelszónak tartalmaznia kell kis- és nagybetűt is.',
                'password.numbers' => 'A jelszónak tartalmaznia kell legalább egy számot.',
                'password.symbols' => 'A jelszónak tartalmaznia kell legalább egy speciális karaktert.',
                'password.uncompromised' => 'A megadott jelszó kiszivárgott. Kérlek, válassz másikat.',

                'role_id.required' => 'A szerepkör kiválasztása kötelező.',
                'role_id.exists' => 'A kiválasztott szerepkör nem létezik.',
            ]
        );

        if (isset($validated['phonenumber'])) {
            $validated['phonenumber'] = phone($validated['phonenumber'], 'HU', PhoneNumberFormat::E164);
        }

        $userData = [
            'username' => $validated['username'],
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'birthdate' => $validated['birthdate'],
            'phonenumber' => $validated['phonenumber'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'role_id' => $validated['role_id'],
        ];

        $user = User::create($userData);
        $user->load('role');

        return response()->json([
            'message' => 'A felhasználó sikeresen létrehozva.',
            'user' => $user
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $query = User::query();

        $with = ['role'];

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = [
                'cars',
                'fuelExpenses',
                'trips',
                'leaveRequests',
                'overtimeRequests',
                'approvedLeaveRequests',
                'approvedOvertimeRequests',
                'journalEntries'
            ];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $with[] = $include;
                }
            }
        }

        $user = $query->with($with)->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') felhasználó nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($user, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') felhasználó nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $minimumBirthdate = Carbon::now()->subYears(18)->format('Y-m-d');

        $validated = $request->validate(
            [
                'username' => ['sometimes', 'string', 'max:25', Rule::unique('users', 'username')->ignore($user->id)],
                'firstname' => ['sometimes', 'string', 'max:50'],
                'lastname' => ['sometimes', 'string', 'max:50'],
                'birthdate' => [
                    'sometimes',
                    'date',
                    'before_or_equal:' . $minimumBirthdate
                ],
                'phonenumber' => ['sometimes', 'phone:HU', 'max:30', Rule::unique('users', 'phonenumber')->ignore($user->id)],
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => [
                    'sometimes',
                    Rules\Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(3)
                ],
                'role_id' => ['sometimes', 'exists:roles,id'],
            ],
            [
                'username.string' => 'A felhasználónév kizárólag nem üres, szöveg formátumú lehet.',
                'username.max' => 'A felhasználónév maximum 25 karakter hosszú lehet.',
                'username.unique' => 'Ez a felhasználónév már foglalt.',

                'firstname.string' => 'A keresztnév kizárólag nem üres, szöveg formátumú lehet.',
                'firstname.max' => 'A keresztnév maximum 50 karakter hosszú lehet.',

                'lastname.string' => 'A vezetéknév kizárólag nem üres, szöveg formátumú lehet.',
                'lastname.max' => 'A vezetéknév maximum 50 karakter hosszú lehet.',

                'birthdate.date' => 'A születési dátum érvénytelen formátumú.',
                'birthdate.before_or_equal' => 'A felhasználónak legalább 18 évesnek kell lennie.',

                'phonenumber.phone' => 'Érvénytelen magyar telefonszám formátum.',
                'phonenumber.max' => 'A telefonszám maximum 30 karakter hosszú lehet.',
                'phonenumber.unique' => 'Ez a telefonszám már regisztrálva van.',

                'email.email' => 'Érvénytelen email cím formátum.',
                'email.max' => 'Az email cím maximum 255 karakter hosszú lehet.',
                'email.unique' => 'Ez az email cím már regisztrálva van.',

                'password.min' => 'A jelszónak legalább :min karakter hosszúnak kell lennie.',
                'password.letters' => 'A jelszónak tartalmaznia kell legalább egy betűt.',
                'password.mixed' => 'A jelszónak tartalmaznia kell kis- és nagybetűt is.',
                'password.numbers' => 'A jelszónak tartalmaznia kell legalább egy számot.',
                'password.symbols' => 'A jelszónak tartalmaznia kell legalább egy speciális karaktert.',
                'password.uncompromised' => 'A megadott jelszó kiszivárgott. Kérlek, válassz másikat.',

                'role_id.exists' => 'A kiválasztott szerepkör nem létezik.',
            ]
        );

        if (isset($validated['phonenumber'])) {
            $validated['phonenumber'] = phone($validated['phonenumber'], 'HU', PhoneNumberFormat::E164);
        }

        if (isset($validated['password']) && Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Az új jelszó nem lehet azonos a régivel.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->fill(collect($validated)->except('password')->toArray());

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->email_verified_at = now();
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->password_changed_at = now();
        }

        $user->save();

        $user->load('role');

        return response()->json([
            'message' => 'A felhasználó adatai sikeresen frissítve lettek.',
            'user' => $user
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') felhasználó nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($user->role && $user->role->slug === 'admin') {
            $adminCount = User::whereHas('role', function ($q) {
                $q->where('slug', 'admin');
            })->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Az utolsó admin felhasználót nem lehet törölni.'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $userName = $user->firstname . ' ' . $user->lastname;

        $user->delete();
        return response()->json([
            'message' => "$userName felhasználó sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
