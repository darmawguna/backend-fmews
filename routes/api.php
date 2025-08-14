<?php

use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\IotController\IotCommandController;
use App\Http\Controllers\IotController\IotModelController;

use App\Http\Controllers\PublicFacilityController;
use App\Http\Controllers\ShelterController;
use App\Models\FacilityCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('kategori-fasilitas', FacilityCategoryController::class);
Route::apiResource('fasilitas-publik', PublicFacilityController::class);
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
