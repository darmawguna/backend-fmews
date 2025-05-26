<?php

use App\Http\Controllers\IotController\IotCommandController;
use App\Http\Controllers\IotController\IotModelController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::middleware(['web'])->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['web'])->post('/login', [AuthController::class, 'login']);

Route::prefix('iot')->group(function () {
    Route::get('/all-data', [IotModelController::class, 'index']);
    Route::get('/whitelist-device', [IotModelController::class, 'whitelistDevice']);
    Route::get('/sensors', [IotModelController::class, 'getAll']);
    Route::post('/create-device', [IotModelController::class, "createDevice"]);
    Route::get('/verify/{id}', [IotModelController::class, "verify"]);
    Route::get('/{id}', [IotModelController::class, 'show']);
    Route::patch('/{device_id}/change-status', [IotCommandController::class, 'changeStatus']);
    Route::delete('/{id}', [IotModelController::class, 'destroy']);
    Route::post('/{device_id}/threshold', [IotCommandController::class, 'setThreshold']);
    Route::post('/generate-token', [IotModelController::class, 'generateToken']);
    Route::post('/register-device', [IotModelController::class, 'registerDevice']);
    Route::get('/ota-script', [IotModelController::class, 'fetchOtaScript']);
});
