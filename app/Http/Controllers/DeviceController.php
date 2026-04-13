<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

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
     * Update device status (called by ESP32)
     */
    public function updateStatus(Request $request, $deviceKey)
    {
        $request->validate([
            'status' => 'required|string|in:online,offline,error',
        ]);

        $device = Device::where('device_key', $deviceKey)->firstOrFail();
        
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
        $patient = \App\Models\Patient::findOrFail($request->patient_id);
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
     * Receive data from ESP32 (IoT endpoint)
     */
    public function receiveData(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string|exists:devices,device_key',
            'total_drops' => 'required|integer',
            'current_tpm' => 'required|integer',
            'remaining_volume' => 'required|numeric',
        ]);

        // Cari device
        $device = Device::where('device_key', $request->device_key)->first();
        
        // Update device status
        $device->update([
            'status' => 'online',
            'last_seen' => now(),
        ]);

        // Cari pasien yang terassign dengan device ini
        $patient = \App\Models\Patient::where('device_key', $request->device_key)->first();
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak terassign ke pasien manapun'
            ], 404);
        }

        // Hitung persentase sisa volume
        $remainingPercent = round(($request->remaining_volume / $patient->initial_volume) * 100);
        
        // Tentukan status berdasarkan volume
        $status = 'normal';
        if ($remainingPercent <= 0) {
            $status = 'empty';
        } elseif ($remainingPercent <= 20) {
            $status = 'stuck';
        } elseif ($remainingPercent <= 50) {
            $status = 'too_slow';
        } elseif ($request->current_tpm > ($patient->target_tpm * 1.2)) {
            $status = 'too_fast';
        } elseif ($request->current_tpm < ($patient->target_tpm * 0.8)) {
            $status = 'too_slow';
        }

        // Simpan data monitoring
        $monitoring = \App\Models\Monitoring::create([
            'patient_id' => $patient->id,
            'total_drops' => $request->total_drops,
            'estimated_volume_remaining' => $request->remaining_volume,
            'current_tpm' => $request->current_tpm,
            'status' => $status,
            'is_anomaly' => ($status !== 'normal'),
            'recorded_at' => now(),
        ]);

        // Cek apakah perlu kirim notifikasi (untuk Telegram nanti)
        $needNotification = false;
        if ($status === 'stuck' || $status === 'empty' || $status === 'too_fast') {
            $needNotification = true;
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diterima',
            'data' => [
                'patient_name' => $patient->name,
                'status' => $status,
                'need_notification' => $needNotification
            ]
        ]);
    }
}