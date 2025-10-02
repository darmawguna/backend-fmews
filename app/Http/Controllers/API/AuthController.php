<?php

namespace App\Http\Controllers\API; // Perhatikan namespace, biasanya 'Api' bukan 'API'

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // Gunakan Facade Validator lengkap
use Tymon\JWTAuth\Facades\JWTAuth; // Import Facade JWTAuth jika diperlukan, atau cukup pakai helper auth()

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware auth:api akan melindungi semua metode kecuali 'login' dan 'register'
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User.
     * Mengganti logika Sanctum dengan hanya membuat user.
     * Pengguna akan perlu login terpisah untuk mendapatkan token JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // Pastikan minimal 8 karakter dan konfirmasi
            'role' => 'sometimes|string|in:administrator,petugas,user', // Tambahkan validasi role
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422); // Gunakan 422 untuk kesalahan validasi
        }

        // Tentukan created_by berdasarkan user yang sedang login, jika ada dan admin/petugas
        $createdBy = null;
        if (Auth::guard('api')->check()) { // Periksa apakah ada user yang terautentikasi melalui JWT guard
            $currentUser = Auth::guard('api')->user();
            if ($currentUser->isAdministrator() || $currentUser->isPetugas()) {
                $createdBy = $currentUser->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user', // Default role 'user'
            'is_active' => $request->is_active ?? true,
            'created_by' => $createdBy,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user
        ], 201); // Gunakan 201 untuk created
    }

    /**
     * Get a JWT via given credentials.
     * Mengganti logika login Sanctum dengan JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Opsional: Pastikan user aktif sebelum mencoba login
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is inactive or not found.'
            ], 401);
        }

        // --- PENTING: Ini adalah cara JWT mengautentikasi dan mendapatkan token ---
        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Get the authenticated User profile.
     * Menggunakan helper `auth()->user()` untuk JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request) // Ganti nama metode dari 'profile' ke 'userProfile' agar konsisten
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user() // Menggunakan helper auth() untuk JWT
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     * Mengganti logika logout Sanctum dengan JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout(); // Menggunakan helper auth() untuk JWT logout

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }


    /**
     * Get the token array structure.
     * Ini adalah metode helper untuk format respons token JWT.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60, // TTL dalam detik
            'user' => auth()->user()
        ]);
    }
}