<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // Login API
    public function login(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'identifier.required' => 'Az email cím vagy felhasználónév megadása kötelező.',
            'identifier.string' => 'Az azonosító csak szöveg formátumú lehet.',
            'password.required' => 'A jelszó megadása kötelező.',
            'password.string' => 'A jelszó csak szöveg formátumú lehet.',
        ]);

        $user = null;
        $identifier = $request->identifier;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
        } else {
            $user = User::where('username', $identifier)->first();
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) && !$user) {
            return response()->json([
                "status" => false,
                "errors" => [
                    "identifier" => ["A megadott email címmel nem található felhasználó."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL) && !$user) {
            return response()->json([
                "status" => false,
                "errors" => [
                    "identifier" => ["A megadott felhasználónévvel nem található felhasználó."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Érvénytelen azonosító adatok."
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "status" => false,
                "message" => "Érvénytelen jelszó."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->tokens()->delete();

        $token = $user->createToken("access_token")->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Sikeres bejelentkezés.",
            "user" => [
                "id" => $user->id,
                "name" => $user->lastname . ' ' . $user->firstname,
                "email" => $user->email,
                "username" => $user->username,
                "role" => $user->role ? $user->role->slug : null,
            ],
            "token" => $token
        ], Response::HTTP_OK);
    }

    // Profile API
    public function profile()
    {
        $userdata = Auth::user();

        if (!$userdata) {
            return response()->json([
                "status" => false,
                "message" => "A token érvénytelen vagy lejárt. Kérjük, jelentkezz be újra."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userdata->load('role');

        return response()->json([
            "status" => true,
            "message" => "Bejelentkezett felhasználó adatai.",
            "data" => $userdata
        ], Response::HTTP_OK);
    }

    // Logout API
    public function logout()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "A token érvénytelen vagy lejárt. Kérjük, jelentkezz be újra."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->tokens()->delete();

        return response()->json([
            "status" => true,
            "message" => "Sikeres kijelentkezés."
        ], Response::HTTP_OK);
    }
}
