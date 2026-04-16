<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Monitoring;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('latestMonitoring')->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'patient_id' => 'required|string|max:50|unique:patients',
            'room' => 'nullable|string|max:50',
            'bed_number' => 'nullable|string|max:10',
            'infusion_type' => 'required|string',
            'initial_volume' => 'required|integer|min:50|max:3000',
            'drop_factor' => 'required|integer|in:20,60',
            'duration_hours' => 'required|integer|min:1|max:24',
            'target_tpm' => 'required|integer',
            'device_key' => 'nullable|string|max:100'
        ]);

        $initialVolume = $request->initial_volume;
        
        $targetTpm = $request->target_tpm;
        if (!$targetTpm) {
            $targetTpm = round(($request->initial_volume * $request->drop_factor) / ($request->duration_hours * 60));
        }

        $patient = Patient::create([
            'name' => $request->name,              
            'patient_id' => $request->patient_id,
            'room' => $request->room ?? '-',
            'bed_number' => $request->bed_number ?? '-',
            'infusion_type' => $request->infusion_type,
            'initial_volume' => $initialVolume,
            'drop_factor' => $request->drop_factor,
            'duration_hours' => $request->duration_hours,
            'target_tpm' => $targetTpm,
            'device_key' => $request->device_key,
            'user_id' => auth()->id(),
        ]);

        Monitoring::create([
            'patient_id' => $patient->id,
            'total_drops' => 0,
            'estimated_volume_remaining' => $initialVolume,
            'current_tpm' => 0,
            'status' => 'normal',
            'is_anomaly' => false,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil ditambahkan',
            'data' => $patient
        ], 201);
    }

    public function show($id)
    {
        $patient = Patient::with('monitorings')->findOrFail($id);
        
        $latestMonitoring = $patient->monitorings()->latest('recorded_at')->first();
        $remainingPercent = 0;
        $remainingMl = 0;
        
        if ($latestMonitoring) {
            $remainingMl = $latestMonitoring->estimated_volume_remaining;
            $remainingPercent = round(($remainingMl / $patient->initial_volume) * 100);
        }
        
        $startTime = $patient->created_at;
        $endTime = $startTime->copy()->addHours($patient->duration_hours);
        $now = now();
        
        $isFinished = $now->greaterThan($endTime);
        $remainingTimeText = $isFinished ? 'Sudah habis' : $now->diffForHumans($endTime, true);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $patient->id,
                'name' => $patient->nama,          
                'patient_id' => $patient->patient_id,
                'room' => $patient->room,
                'bed_number' => $patient->bed_number,
                'infusion_type' => $patient->infusion_type,
                'initial_volume' => $patient->initial_volume,
                'remaining_volume' => $remainingMl,
                'remaining_percent' => $remainingPercent,
                'target_tpm' => $patient->target_tpm,
                'current_tpm' => $latestMonitoring->current_tpm ?? 0,
                'drop_factor' => $patient->drop_factor,
                'duration_hours' => $patient->duration_hours,
                'status' => $latestMonitoring->status ?? 'normal',
                'device_key' => $patient->device_key,
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'remaining_time' => $remainingTimeText,
                'is_finished' => $isFinished,
                'monitoring_history' => $patient->monitorings()->take(10)->get()
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255', 
            'room' => 'nullable|string|max:50',
            'bed_number' => 'nullable|string|max:10',
            'infusion_type' => 'sometimes|string',
            'drop_factor' => 'sometimes|integer|in:20,60',
            'duration_hours' => 'sometimes|integer|min:1|max:24',
            'target_tpm' => 'sometimes|integer',
            'device_key' => 'nullable|string|max:100'
        ]);

        $updateData = $request->only([
            'room', 'bed_number', 'infusion_type', 
            'drop_factor', 'duration_hours', 'target_tpm', 'device_key'
        ]);
        
        if ($request->has('name')) {
            $updateData['nama'] = $request->name; 
        }
        
        $patient->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Data pasien berhasil diupdate',
            'data' => $patient
        ]);
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->monitorings()->delete();
        $patient->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil dihapus'
        ]);
    }

    public function updateVolume(Request $request, $id)
    {
        $request->validate([
            'volume_percent' => 'required|integer|min:0|max:100'
        ]);
        
        $patient = Patient::findOrFail($id);
        $newVolumeMl = round(($request->volume_percent / 100) * $patient->initial_volume);
        
        $monitoring = Monitoring::create([
            'patient_id' => $patient->id,
            'total_drops' => 0,
            'estimated_volume_remaining' => $newVolumeMl,
            'current_tpm' => 0,
            'status' => $this->getStatusByVolume($request->volume_percent),
            'is_anomaly' => false,
            'recorded_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Volume infus berhasil diupdate',
            'data' => [
                'remaining_ml' => $newVolumeMl,
                'remaining_percent' => $request->volume_percent,
                'status' => $monitoring->status
            ]
        ]); 
    }

    private function getStatusByVolume($percent)
    {
        if ($percent <= 0) return 'empty';
        if ($percent <= 20) return 'stuck';
        if ($percent <= 50) return 'too_slow';
        return 'normal';
    }
}