<?php

// use App\Http\Controllers\IotModelController;

use App\Http\Controllers\IotController\IotCommandController;
use App\Http\Controllers\IotController\IotModelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('iot')->group(function () {
    Route::get('/all-data', [IotModelController::class, 'index']);
    Route::post('/register', [IotModelController::class, "store"]);
    Route::get('/verify/{id}', [IotModelController::class, "verify"]);
    Route::get('/data/{id}', [IotModelController::class, 'show']);
    Route::patch('/change-status/{id}', [IotModelController::class, 'changeStatus']);
    Route::delete('/{id}', [IotModelController::class, 'destroy']);

    Route::post('/{device_id}/threshold', [IotCommandController::class, 'setThreshold']);
    // Route::prefix('/command')->group(function () {
        
    // });
});
