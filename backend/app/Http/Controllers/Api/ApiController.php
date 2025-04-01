<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{

    // Login API
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required_without:username', 'email', 'string'],
            'username' => ['required_without:email', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required_without' => 'Az email cím vagy felhasználónév megadása kötelező.',
            'email.email' => 'Érvénytelen email formátum.',
            'email.string' => 'Az email cím csak szöveg formátumú lehet.',
            'username.required_without' => 'A felhasználónév vagy email cím megadása kötelező.',
            'username.string' => 'A felhasználónév csak szöveg formátumú lehet.',
            'password.required' => 'A jelszó megadása kötelező.',
            'password.string' => 'A jelszó csak szöveg formátumú lehet.',
        ]);

        $user = null;
        $loginField = null;
        $loginValue = null;

        if ($request->has('email')) {
            $loginField = 'email';
            $loginValue = $request->email;
            $user = User::where('email', $loginValue)->first();
        } else {
            $loginField = 'username';
            $loginValue = $request->username;
            $user = User::where('username', $loginValue)->first();
        }

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Érvénytelen azonosító adatok. A megadott " .
                    ($loginField == 'email' ? 'email cím' : 'felhasználónév') .
                    " nem található."
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "status" => false,
                "message" => "Érvénytelen jelszó."
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken("access_token")->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Sikeres bejelentkezés.",
            "user" => [
                "id" => $user->id,
                "name" => $user->firstname . ' ' . $user->lastname,
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
