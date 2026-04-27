<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Patient;
use App\Models\Monitoring;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices.
     */
    public function index(Request $request)
    {
        $deviceType = $request->get('device_type', 'infus');
        
        $devices = Device::where('device_type', $deviceType)
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return response()->json($devices);
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string|max:100|unique:devices',
            'device_type' => 'required|string|in:suhu,infus',
        ]);

        $device = Device::create([
            'device_key' => $request->device_key,
            'device_type' => $request->device_type,
            'status' => 'offline',
            'last_seen' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil ditambahkan',
            'device' => $device
        ], 201);
    }

    /**
     * Display the specified device.
     */
    public function show($id)
    {
        $device = Device::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'device' => $device
        ]);
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        
        $request->validate([
            'device_key' => 'sometimes|string|max:100|unique:devices,device_key,' . $id,
            'device_type' => 'sometimes|string|in:suhu,infus',
            'status' => 'sometimes|string|in:online,offline,error',
        ]);

        $device->update($request->only([
            'device_key', 'device_type', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diupdate',
            'device' => $device
        ]);
    }

    /**
     * Remove the specified device.
     */
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        
        // Cek apakah device sedang terhubung ke pasien
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil dihapus'
        ]);
    }

    /**
     * ✅ PERBAIKI: Update device status (called by ESP32)
     */
    public function updateStatus(Request $request, $deviceKey)
    {
        // ✅ Log data masuk
        Log::info('ESP32 Status Update', [
            'device_key' => $deviceKey,
            'data' => $request->all()
        ]);
        
        $request->validate([
            'status' => 'required|string|in:online,offline,error',
        ]);

        // Cari atau buat device baru
        $device = Device::firstOrCreate(
            ['device_key' => $deviceKey],
            [
                'device_type' => 'infus',
                'status' => $request->status,
                'last_seen' => now(),
            ]
        );
        
        // Update status
        $device->update([
            'status' => $request->status,
            'last_seen' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status device berhasil diupdate'
        ]);
    }

    /**
     * Get devices that are not assigned to any patient
     */
    public function getAvailableDevices()
    {
        $devices = Device::where('device_type', 'infus')
                        ->whereDoesntHave('patient')
                        ->get();
        
        return response()->json($devices);
    }

    /**
     * Assign device to patient
     */
    public function assignToPatient(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string|exists:devices,device_key',
            'patient_id' => 'required|integer|exists:patients,id',
        ]);

        // Update patient dengan device_key
        $patient = Patient::findOrFail($request->patient_id);
        $patient->device_key = $request->device_key;
        $patient->save();

        // Update device status
        $device = Device::where('device_key', $request->device_key)->first();
        $device->status = 'online';
        $device->last_seen = now();
        $device->save();

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diassign ke pasien'
        ]);
    }

    /**
     * ✅ PERBAIKI: Receive data from ESP32 (IoT endpoint)
     */
    public function receiveData(Request $request)
    {
        // ✅ LOG SEMUA DATA MASUK
        Log::info('ESP32 DATA RECEIVED', [
            'all_data' => $request->all(),
            //'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            //'time' => now()->format('Y-m-d H:i:s')
        ]);
        
        try {
            // Validasi input
            $request->validate([
                'device_key' => 'required|string',
                'total_drops' => 'required|integer|min:0',
                'current_tpm' => 'required|integer|min:0',
                'remaining_volume' => 'required|numeric|min:0',
            ]);

            // ✅ Auto-register device jika belum ada
            $device = Device::firstOrCreate(
                ['device_key' => $request->device_key],
                [
                    'device_type' => 'infus',
                    'status' => 'online',
                    'last_seen' => now(),
                ]
            );
            
            // Update status device
            $device->update([
                'status' => 'online',
                'last_seen' => now(),
            ]);

            // Cari pasien yang terhubung
            $patient = Patient::where('device_key', $request->device_key)->first();
            
            if (!$patient) {
                // Log::warning('Device belum diassign ke pasien', [
                //    'device_key' => $request->device_key
                // ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Device belum diassign ke pasien manapun'
                ], 404);
            }

            // Hitung persentase sisa
            $remainingPercent = 0;
            if ($patient->initial_volume > 0) {
                $remainingPercent = round(($request->remaining_volume / $patient->initial_volume) * 100);
                $remainingPercent = max(0, min(100, $remainingPercent));
            }
            
            // ✅ Tentukan status
            $status = 'normal';
            if ($remainingPercent <= 0) {
                $status = 'empty';
            } elseif ($remainingPercent <= 20) {
                $status = 'stuck';
            } elseif ($request->current_tpm > ($patient->target_tpm * 1.5)) {
                $status = 'too_fast';
            } elseif ($request->current_tpm > 0 && $request->current_tpm < ($patient->target_tpm * 0.3)) {
                $status = 'too_slow';
            }

            // Simpan monitoring
            $monitoring = Monitoring::create([
                'patient_id' => $patient->id,
                'total_drops' => $request->total_drops,
                'estimated_volume_remaining' => $request->remaining_volume,
                'current_tpm' => $request->current_tpm,
                'status' => $status,
                'is_anomaly' => ($status !== 'normal'),
                'recorded_at' => now(),
            ]);
            $patient->update([
                'current_volume' => $request->remaining_volume,  // ⚠️ Perlu tambah kolom ini!
                'current_tpm' => $request->current_tpm,
                'status' => $status,
                'last_monitoring_at' => now(),
            ]);
            Log::info('Data monitoring tersimpan', [
                'patient_id' => $patient->id,
                'status' => $status,
                'remaining_percent' => $remainingPercent
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => [
                    'patient_name' => $patient->nama,
                    'status' => $status,
                    'remaining_percent' => $remainingPercent,
                    'recorded_at' => $monitoring->recorded_at->format('Y-m-d H:i:s')
                ]
            ], 201);
            
        } catch (\Exception $e) {
        Log::error('Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
}