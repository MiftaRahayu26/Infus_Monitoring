<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Monitoring;

class DashboardController extends Controller
{
    /**
     * Constructor - require authentication for all methods
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show dashboard page
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get all monitoring data for dashboard (AJAX)
     */
    public function getMonitoringData(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status', 'all');
        $room = $request->get('room', 'all');
        $search = $request->get('search', '');
        
        // Query pasien dengan monitoring terbaru
        $query = Patient::with('latestMonitoring');
        
        // Filter berdasarkan status
        if ($status !== 'all') {
            $query->whereHas('latestMonitoring', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
        
        // Filter berdasarkan ruang
        if ($room !== 'all') {
            $query->where('room', 'like', "%{$room}%");
        }
        
        // Filter berdasarkan pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
            });
        }
        
        // Urutkan berdasarkan created_at terbaru
        $query->orderBy('created_at', 'desc');
        
        // Pagination
        $patients = $query->paginate($perPage);
        
        // Format data
        $formattedData = [];
        foreach ($patients as $patient) {
            $latestMonitoring = $patient->latestMonitoring;
            
            // Hitung sisa volume persentase
            $remainingPercent = 100;
            $remainingMl = $patient->initial_volume;
            
            if ($latestMonitoring) {
                $remainingMl = $latestMonitoring->estimated_volume_remaining;
                if ($patient->initial_volume > 0) {
                    $remainingPercent = round(($remainingMl / $patient->initial_volume) * 100);
                }
                if ($remainingPercent < 0) $remainingPercent = 0;
                if ($remainingPercent > 100) $remainingPercent = 100;
            }
            
            // Hitung estimasi waktu selesai
            $startTime = $patient->created_at;
            $endTime = $startTime->copy()->addHours($patient->duration_hours);
            $now = now();
            
            $isFinished = $now->greaterThan($endTime);
            
            if ($isFinished) {
                $estimatedTimeText = 'Selesai';
            } else {
                $remainingMinutes = $now->diffInMinutes($endTime);
                if ($remainingMinutes < 60) {
                    $estimatedTimeText = $remainingMinutes . ' menit lagi';
                } else {
                    $remainingHours = floor($remainingMinutes / 60);
                    $remainingMins = $remainingMinutes % 60;
                    $estimatedTimeText = $remainingHours . ' jam ' . $remainingMins . ' menit lagi';
                }
            }
            
            // Tentukan status
            $status = $latestMonitoring->status ?? 'normal';
            
            $formattedData[] = [
                'id' => $patient->id,
                'name' => $patient->name,
                'patient_id' => $patient->patient_id,
                'room' => $patient->room,
                'bed_number' => $patient->bed_number,
                'infusion_type' => $patient->infusion_type,
                'initial_volume' => $patient->initial_volume,
                'remaining_ml' => $remainingMl,
                'remaining_percent' => $remainingPercent,
                'target_tpm' => $patient->target_tpm,
                'current_tpm' => $latestMonitoring->current_tpm ?? 0,
                'status' => $status,
                'device_key' => $patient->device_key,
                'estimated_time' => $estimatedTimeText,
                'start_time' => $startTime->format('H:i'),
                'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'total' => $patients->total(),
            'per_page' => $patients->perPage(),
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
        ]);
    }

    /**
     * Get statistics for dashboard cards (AJAX)
     */
    public function getStats()
    {
        $totalPatients = Patient::count();
        
        // Hitung infus aktif (status bukan empty/stuck)
        $activeInfusions = Patient::whereHas('latestMonitoring', function ($q) {
            $q->whereNotIn('status', ['empty', 'stuck']);
        })->count();
        
        // Hitung volume rendah (remaining < 20%)
        $lowVolume = 0;
        $patients = Patient::with('latestMonitoring')->get();
        foreach ($patients as $patient) {
            $latestMonitoring = $patient->latestMonitoring;
            if ($latestMonitoring && $patient->initial_volume > 0) {
                $remainingPercent = round(($latestMonitoring->estimated_volume_remaining / $patient->initial_volume) * 100);
                if ($remainingPercent <= 20 && $remainingPercent > 0) {
                    $lowVolume++;
                }
            }
        }
        
        // Hitung anomali (status bukan normal)
        $anomalyCount = Patient::whereHas('latestMonitoring', function ($q) {
            $q->where('status', '!=', 'normal');
        })->count();
        
        return response()->json([
            'success' => true,
            'total_patients' => $totalPatients,
            'active_infusions' => $activeInfusions,
            'low_volume' => $lowVolume,
            'anomaly_count' => $anomalyCount,
        ]);
    }

    /**
     * Receive data from ESP32 (IoT endpoint)
     * Ini akan dipanggil oleh ESP32 untuk mengirim data tetesan
     */
    public function receiveInfusionData(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string',
            'total_drops' => 'required|integer',
            'current_tpm' => 'required|integer',
            'remaining_volume' => 'required|numeric',
        ]);

        // Cari pasien berdasarkan device_key
        $patient = Patient::where('device_key', $request->device_key)->first();
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak terdaftar atau tidak terassign ke pasien'
            ], 404);
        }

        // Hitung persentase sisa volume
        $remainingPercent = round(($request->remaining_volume / $patient->initial_volume) * 100);
        if ($remainingPercent < 0) $remainingPercent = 0;
        if ($remainingPercent > 100) $remainingPercent = 100;
        
        // Tentukan status berdasarkan volume dan TPM
        $status = 'normal';
        if ($remainingPercent <= 0) {
            $status = 'empty';
        } elseif ($remainingPercent <= 20) {
            $status = 'stuck';
        } elseif ($request->current_tpm > ($patient->target_tpm * 1.2)) {
            $status = 'too_fast';
        } elseif ($request->current_tpm < ($patient->target_tpm * 0.8) && $remainingPercent > 20) {
            $status = 'too_slow';
        }

        // Simpan data monitoring
        $monitoring = Monitoring::create([
            'patient_id' => $patient->id,
            'total_drops' => $request->total_drops,
            'estimated_volume_remaining' => $request->remaining_volume,
            'current_tpm' => $request->current_tpm,
            'status' => $status,
            'is_anomaly' => ($status !== 'normal'),
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diterima',
            'data' => [
                'patient_name' => $patient->nama,
                'status' => $status,
                'remaining_percent' => $remainingPercent
            ]
        ]);
    }

    /**
     * Update data dari ESP32 (endpoint untuk update volume dan TPM)
     */
    public function updateData(Request $request)
    {
        // Endpoint untuk update data dari ESP32
        // Bisa menggunakan method yang sama dengan receiveInfusionData
        return $this->receiveInfusionData($request);
    }
}