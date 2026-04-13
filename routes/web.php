<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// ROUTE UNTUK GUEST (BELUM LOGIN)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// ============================================
// ROUTE UNTUK USER YANG SUDAH LOGIN
// ============================================
Route::middleware('auth')->group(function () {

    // Halaman Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ========================================
    // API ENDPOINTS UNTUK PATIENTS
    // ========================================
    Route::prefix('api')->group(function () {
        
        // Patient Management
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::put('/patients/{id}', [PatientController::class, 'update']);
        Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
        Route::post('/patients/{id}/update-volume', [PatientController::class, 'updateVolume']);
        
        // Monitoring Management
        Route::get('/monitoring/data', [MonitoringController::class, 'index']);
        Route::get('/monitoring/stats', [MonitoringController::class, 'stats']);
        Route::get('/monitoring/real-time/{patientId}', [MonitoringController::class, 'getRealTimeData']);
        Route::get('/monitoring/export', [MonitoringController::class, 'export']);
        
        // Device Management
        Route::get('/devices', [DeviceController::class, 'index']);
        Route::get('/devices/available', [DeviceController::class, 'getAvailableDevices']);
        Route::get('/devices/{id}', [DeviceController::class, 'show']);
        Route::post('/devices', [DeviceController::class, 'store']);
        Route::put('/devices/{id}', [DeviceController::class, 'update']);
        Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);
        Route::post('/devices/assign', [DeviceController::class, 'assignToPatient']);
        
        // ESP32 IoT Endpoint (tanpa auth, karena dipanggil oleh ESP32)
        // Route ini akan dipindahkan ke luar middleware auth nanti
    });
});

// ============================================
// ROUTE UNTUK ESP32 (TANPA AUTH)
// ============================================
Route::post('/api/device/status/{deviceKey}', [DeviceController::class, 'updateStatus']);
Route::post('/api/device/data', [DeviceController::class, 'receiveData']);

// ============================================
// REDIRECT ROOT
// ============================================
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});