<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\IotController\IotCommandController;
use App\Http\Controllers\IotController\IotModelController;

use App\Http\Controllers\PublicFacilityController;
use App\Http\Controllers\ShelterController;
use App\Http\Middleware\RoleMiddleware;
use App\Models\FacilityCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
// Route::get('/logout', [AuthController::class, 'logout']);
// Route::get('/refresh', [AuthController::class, 'refresh']);
// Route::get('/user-profile', [AuthController::class, 'userProfile'])->middleware('auth:api');


Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);

    Route::apiResource('kategori-fasilitas', FacilityCategoryController::class);
    // Route::apiResource('fasilitas-publik', PublicFacilityController::class);
    Route::get('shelter/shelter-stats', [ShelterController::class, 'getShelterStats']);
    Route::get('shelter/getAll', [ShelterController::class, 'getAll']);
    Route::apiResource('shelter', ShelterController::class);


    Route::prefix('iot')->group(function () {
        Route::get('/all-data', [IotModelController::class, 'index']);
        Route::get('/dashboard-stats', [IotModelController::class, 'getDashboardStats']);
        Route::get('/whitelist-device', [IotModelController::class, 'whitelistDevice']);
        Route::get('/sensors', [IotModelController::class, 'getAll']);
        Route::post('/create-device', [IotModelController::class, "createDevice"]);
        Route::post('/create-and-register', [IotModelController::class, 'createAndRegisterDevice']);
        Route::post('/retry-registration', [IotModelController::class, 'retryRegistration']);
        Route::get('/verify/{id}', [IotModelController::class, "verify"]);
        Route::get('/{id}', [IotModelController::class, 'show']);
        Route::patch('/{device_id}/change-status', [IotCommandController::class, 'changeStatus']);
        Route::delete('/{id}', [IotModelController::class, 'destroy']);
        Route::put('/{id}/update', [IotModelController::class, 'update']);
        Route::post('/{device_id}/threshold', [IotCommandController::class, 'setThreshold']);
        Route::post('/generate-token', [IotModelController::class, 'generateToken']);
        Route::post('/register-device', [IotModelController::class, 'registerDevice']);
        Route::get('/ota-script', [IotModelController::class, 'fetchOtaScript']);
    });

    Route::prefix('fasilitas-publik')->group(function () {
        // 🔹 GET: Dapatkan daftar fasilitas (dengan search & pagination)
        Route::get('/', [PublicFacilityController::class, 'index']);
        // 🔹 POST: Tambah fasilitas baru
        Route::post('/', [PublicFacilityController::class, 'store']);
        // 🔹 GET: Dapatkan detail fasilitas
        Route::get('/{id}', [PublicFacilityController::class, 'show']);
        // 🔹 PUT: Update fasilitas
        Route::put('/{id}', [PublicFacilityController::class, 'update']);
        // 🔹 DELETE: Hapus fasilitas
        Route::delete('/{id}', [PublicFacilityController::class, 'destroy']);
        // 🔹 GET: Dapatkan statistik fasilitas
        Route::get('/stats', [PublicFacilityController::class, 'stats']);
    });

    // Routes requiring specific roles
    Route::get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the Administrator Dashboard!']);
    })->middleware('role:administrator');

    Route::get('/petugas-area', function () {
        return response()->json(['message' => 'Welcome to the Petugas Area!']);
    })->middleware('role:petugas');

    Route::get('/user-area', function () {
        return response()->json(['message' => 'Welcome to the User Area!']);
    })->middleware('role:user');
});


