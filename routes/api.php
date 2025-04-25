<?php

// use App\Http\Controllers\IotModelController;

use App\Http\Controllers\IotController\IotCommandController;
use App\Http\Controllers\IotController\IotModelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('iot')->group(function () {
    Route::get('/all-data', [IotModelController::class, 'index']);
    Route::get('/sensors', [IotModelController::class, 'getAll']);
    Route::post('/register', [IotModelController::class, "store"]);
    Route::get('/verify/{id}', [IotModelController::class, "verify"]);
    Route::get('/{id}', [IotModelController::class, 'show']);
    Route::patch('/{device_id}/change-status', [IotCommandController::class, 'changeStatus']);
    Route::delete('/{id}', [IotModelController::class, 'destroy']);
    Route::post('/{device_id}/threshold', [IotCommandController::class, 'setThreshold']);
    // Route::prefix('/command')->group(function () {
    // });
});
