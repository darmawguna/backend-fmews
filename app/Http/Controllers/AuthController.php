<?php

namespace App\Http\Controllers; // Pastikan namespace Anda benar

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie; // <-- Tambahkan ini

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|string|in:admin,petugas' // Sesuaikan role jika perlu
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'petugas', // Default role
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        return $this->createNewToken($token);
    }

    public function logout()
    {
        auth()->logout();

        // Buat cookie 'token' yang sudah kedaluwarsa untuk menghapusnya di browser
        $cookie = Cookie::forget('token');

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ])->withCookie($cookie); // <-- Kirim cookie yang sudah dihapus
    }

    public function refresh()
    {
        // Fungsi refresh juga akan secara otomatis membuat token baru di cookie
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()
        ]);
    }

    /**
     * Get the token array structure and attach it to a cookie.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $ttlInMinutes = (int) \Tymon\JWTAuth\Facades\JWTAuth::factory()->getTTL();

        // Buat cookie yang aman
        $cookie = cookie(
            'token',                // Nama cookie
            $token,                 // Nilai (token JWT)
            $ttlInMinutes,          // Masa berlaku dalam menit
            '/',                    // Path
            null,                   // Domain
            config('app.env') !== 'local', // secure (true jika bukan local)
            true,                   // httpOnly (tidak bisa diakses JS)
            false,                  // raw
            'lax'                   // sameSite
        );

        // Kirim respons JSON hanya dengan data user, tanpa token
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()
        ])->withCookie($cookie); // <-- Lampirkan cookie ke respons
    }
}