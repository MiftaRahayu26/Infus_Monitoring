<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Monitoring;

class MonitoringController extends Controller
{
    /**
     * Get all monitoring data with pagination and filters
     */
    public function index(Request $request)
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
        
        // Filter berdasarkan pencarian nama atau ID pasien
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
        
        // Format data untuk frontend
        $formattedData = [];
        foreach ($patients as $patient) {
            $latestMonitoring = $patient->latestMonitoring;
            
            // Hitung sisa volume persentase
            $remainingPercent = 0;
            $remainingMl = $patient->initial_volume;
            
            if ($latestMonitoring) {
                $remainingMl = $latestMonitoring->estimated_volume_remaining;
                $remainingPercent = round(($remainingMl / $patient->initial_volume) * 100);
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
            
            // Tentukan status badge
            $status = $latestMonitoring->status ?? 'normal';
            $statusBadge = $this->getStatusBadge($status);
            
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
                'status_badge' => $statusBadge,
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
     * Get statistics for dashboard cards
     */
    public function stats()
    {
        $totalPatients = Patient::count();
        
        // Hitung infus aktif (status bukan empty/stuck)
        $activeInfusions = Patient::whereHas('latestMonitoring', function ($q) {
            $q->whereNotIn('status', ['empty', 'stuck']);
        })->count();
        
        // Hitung volume rendah (remaining < 50ml atau < 20%)
        $lowVolume = 0;
        $patients = Patient::with('latestMonitoring')->get();
        foreach ($patients as $patient) {
            $latestMonitoring = $patient->latestMonitoring;
            if ($latestMonitoring) {
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
     * Get real-time monitoring data for a specific patient
     */
    public function getRealTimeData($patientId)
    {
        $patient = Patient::with('monitorings')->findOrFail($patientId);
        $latestMonitoring = $patient->monitorings()->latest('recorded_at')->first();
        
        if (!$latestMonitoring) {
            return response()->json([
                'success' => false,
                'message' => 'Data monitoring tidak ditemukan'
            ]);
        }
        
        $remainingPercent = round(($latestMonitoring->estimated_volume_remaining / $patient->initial_volume) * 100);
        
        return response()->json([
            'success' => true,
            'data' => [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'remaining_percent' => $remainingPercent,
                'remaining_ml' => $latestMonitoring->estimated_volume_remaining,
                'current_tpm' => $latestMonitoring->current_tpm,
                'status' => $latestMonitoring->status,
                'last_update' => $latestMonitoring->recorded_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    /**
     * Export monitoring data to CSV
     */
    public function export(Request $request)
    {
        $status = $request->get('status', 'all');
        $room = $request->get('room', 'all');
        $search = $request->get('search', '');
        
        $query = Patient::with('latestMonitoring');
        
        if ($status !== 'all') {
            $query->whereHas('latestMonitoring', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
        
        if ($room !== 'all') {
            $query->where('room', 'like', "%{$room}%");
        }
        
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
            });
        }
        
        $patients = $query->get();
        
        // Buat file CSV
        $filename = 'monitoring_infus_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        // Header CSV
        fputcsv($handle, [
            'No', 'Nama Pasien', 'No RM', 'Ruang', 'Bed', 
            'Jenis Infus', 'Volume Awal', 'Sisa Volume', 
            'Sisa (%)', 'Target TPM', 'TPM Saat Ini', 
            'Status', 'Device Key', 'Waktu Mulai', 'Last Update'
        ]);
        
        $no = 1;
        foreach ($patients as $patient) {
            $latestMonitoring = $patient->latestMonitoring;
            $remainingPercent = 0;
            $remainingMl = $patient->initial_volume;
            
            if ($latestMonitoring) {
                $remainingMl = $latestMonitoring->estimated_volume_remaining;
                $remainingPercent = round(($remainingMl / $patient->initial_volume) * 100);
            }
            
            fputcsv($handle, [
                $no++,
                $patient->name,
                $patient->patient_id,
                $patient->room,
                $patient->bed_number,
                $patient->infusion_type,
                $patient->initial_volume,
                $remainingMl,
                $remainingPercent,
                $patient->target_tpm,
                $latestMonitoring->current_tpm ?? 0,
                $latestMonitoring->status ?? 'normal',
                $patient->device_key ?? '-',
                $patient->created_at->format('Y-m-d H:i:s'),
                $latestMonitoring->recorded_at->format('Y-m-d H:i:s') ?? '-',
            ]);
        }
        
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Get status badge HTML based on status
     */
    private function getStatusBadge($status)
    {
        switch ($status) {
            case 'normal':
                return '<span class="status-badge status-normal">Normal</span>';
            case 'too_fast':
                return '<span class="status-badge status-fast">Terlalu Cepat</span>';
            case 'too_slow':
                return '<span class="status-badge status-slow">Terlalu Lambat</span>';
            case 'stuck':
                return '<span class="status-badge status-stuck">Macet</span>';
            case 'empty':
                return '<span class="status-badge status-stuck">Habis</span>';
            default:
                return '<span class="status-badge">-</span>';
        }
    }
}