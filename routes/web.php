<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\DeviceController;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('api')->group(function () {
        
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::put('/patients/{id}', [PatientController::class, 'update']);
        Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
        Route::post('/patients/{id}/update-volume', [PatientController::class, 'updateVolume']);
        
        Route::get('/monitoring/data', [MonitoringController::class, 'index']);
        Route::get('/monitoring/stats', [MonitoringController::class, 'stats']);
        Route::get('/monitoring/real-time/{patientId}', [MonitoringController::class, 'getRealTimeData']);
        Route::get('/monitoring/export', [MonitoringController::class, 'export']);
        
        Route::get('/devices', [DeviceController::class, 'index']);
        Route::get('/devices/available', [DeviceController::class, 'getAvailableDevices']);
        Route::get('/devices/{id}', [DeviceController::class, 'show']);
        Route::post('/devices', [DeviceController::class, 'store']);
        Route::put('/devices/{id}', [DeviceController::class, 'update']);
        Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);
        Route::post('/devices/assign', [DeviceController::class, 'assignToPatient']);
        
    });
});

Route::post('/api/device/status/{deviceKey}', [DeviceController::class, 'updateStatus'])
    ->withoutMiddleware(['web']);  

Route::post('/api/device/data', [DeviceController::class, 'receiveData'])
    ->withoutMiddleware(['web']);  

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/api/dataset/export/{deviceKey}', function($deviceKey) {
    $data = \App\Models\LstmDataset::where('device_key', $deviceKey)
        ->orderBy('recorded_at', 'asc')
        ->get();
    
    $csv = "recorded_at,total_drops,current_tpm,interval_drops,status,label\n";
    foreach ($data as $row) {
        $csv .= "{$row->recorded_at},{$row->total_drops},{$row->current_tpm},{$row->interval_drops},{$row->status},{$row->label}\n";
    }
    
    return response($csv, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="lstm_dataset_'.$deviceKey.'.csv"',
    ]);
})->withoutMiddleware(['web', 'auth']);
