<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Routes untuk IoT/ESP32 dan external API
| Routes disini TIDAK menggunakan middleware web (bebas CSRF)
*/

// Endpoint untuk ESP32
Route::post('/device/data', [DeviceController::class, 'receiveData']);
Route::post('/device/status/{deviceKey}', [DeviceController::class, 'updateStatus']);

// Endpoint testing koneksi
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API connected',
        'timestamp' => now()->toDateTimeString()
    ]);
});