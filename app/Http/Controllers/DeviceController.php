<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Patient;
use App\Models\Monitoring;
use App\Models\RawDripData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $deviceType = $request->get('device_type', 'infus');
        $devices = Device::where('device_type', $deviceType)
                        ->orderBy('created_at', 'desc')
                        ->get();
        return response()->json($devices);
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string|max:100|unique:devices',
            'device_type' => 'required|string|in:suhu,infus',
        ]);

        $device = Device::create([
            'device_key'  => $request->device_key,
            'device_type' => $request->device_type,
            'status'      => 'offline',
            'last_seen'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil ditambahkan',
            'device'  => $device
        ], 201);
    }

    public function show($id)
    {
        $device = Device::findOrFail($id);
        return response()->json(['success' => true, 'device' => $device]);
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $request->validate([
            'device_key'  => 'sometimes|string|max:100|unique:devices,device_key,' . $id,
            'device_type' => 'sometimes|string|in:suhu,infus',
            'status'      => 'sometimes|string|in:online,offline,error',
        ]);
        $device->update($request->only(['device_key', 'device_type', 'status']));
        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diupdate',
            'device'  => $device
        ]);
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        return response()->json(['success' => true, 'message' => 'Device berhasil dihapus']);
    }

    public function updateStatus(Request $request, $deviceKey)
    {
        Log::info('ESP32 Status Update', ['device_key' => $deviceKey, 'data' => $request->all()]);
        $request->validate(['status' => 'required|string|in:online,offline,error']);

        $device = Device::firstOrCreate(
            ['device_key' => $deviceKey],
            ['device_type' => 'infus', 'status' => $request->status, 'last_seen' => now()]
        );
        $device->update(['status' => $request->status, 'last_seen' => now()]);

        return response()->json(['success' => true, 'message' => 'Status device berhasil diupdate']);
    }

    public function getAvailableDevices()
    {
        $devices = Device::where('device_type', 'infus')
                        ->whereDoesntHave('patient')
                        ->get();
        return response()->json($devices);
    }

    public function assignToPatient(Request $request)
    {
        $request->validate([
            'device_key' => 'required|string|exists:devices,device_key',
            'patient_id' => 'required|integer|exists:patients,id',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $patient->device_key = $request->device_key;
        $patient->save();

        $device = Device::where('device_key', $request->device_key)->first();
        $device->update(['status' => 'online', 'last_seen' => now()]);

        return response()->json(['success' => true, 'message' => 'Device berhasil diassign ke pasien']);
    }

    public function receiveData(Request $request)
    {
        Log::info('ESP32 DATA RECEIVED', [
            'all_data' => $request->all(),
            'ip'       => $request->ip(),
        ]);

        try {
            $request->validate([
                'device_key'       => 'required|string',
                'total_drops'      => 'required|integer|min:0',
                'current_tpm'      => 'required|integer|min:0',
                'remaining_volume' => 'required|numeric|min:0',
                'interval_data'    => 'nullable|array',
                'mean_interval'    => 'nullable|numeric',
                'interval_count'   => 'nullable|integer',
                'status_lokal'     => 'nullable|string',
            ]);

            $device = Device::firstOrCreate(
                ['device_key' => $request->device_key],
                ['device_type' => 'infus', 'status' => 'online', 'last_seen' => now()]
            );
            $device->update(['status' => 'online', 'last_seen' => now()]);

            $patient = Patient::where('device_key', $request->device_key)->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device belum diassign ke pasien manapun. tidak terassign'
                ], 404);
            }

            $remainingPercent = 0;
            if ($patient->initial_volume > 0) {
                $remainingPercent = round(($request->remaining_volume / $patient->initial_volume) * 100);
                $remainingPercent = max(0, min(100, $remainingPercent));
            }

            $intervalData  = $request->input('interval_data', []);
            $intervalCount = count($intervalData);

            $meanInterval = null;
            $stdInterval  = null;

            if ($intervalCount > 0) {
                $meanInterval = array_sum($intervalData) / $intervalCount;
                if ($intervalCount > 1) {
                    $variance = 0;
                    foreach ($intervalData as $val) {
                        $variance += pow($val - $meanInterval, 2);
                    }
                    $stdInterval = sqrt($variance / $intervalCount);
                } else {
                    $stdInterval = 0;
                }
                $meanInterval = round($meanInterval, 4);
                $stdInterval  = round($stdInterval, 4);
            }

            // GANTI DENGAN INI:
            $tpmTarget = $patient->target_tpm ?? 0;
            $status    = 'normal';

            if ($remainingPercent <= 0) {
                // Volume habis
                $status = 'empty';

            } elseif ($request->status_lokal === 'stuck') {
                // ESP32 deteksi tidak ada tetes >30 detik
                $status = 'stuck';

            } elseif ($request->current_tpm === 0 && $remainingPercent > 0) {
                // Tidak ada tetesan tapi volume masih ada → macet
                $status = 'stuck';

            } elseif ($tpmTarget > 0 && $request->current_tpm > ($tpmTarget * 1.15)) {
                // TPM aktual jauh di atas target
                $status = 'too_fast';

            } elseif ($tpmTarget > 0
                    && $request->current_tpm > 0
                    && $request->current_tpm < ($tpmTarget * 0.85)) {
                // TPM aktual jauh di bawah target
                $status = 'too_slow';

            } elseif ($tpmTarget > 0 && $meanInterval !== null) {
                // Cek lebih detail dari pola interval
                $intervalTarget = 60.0 / $tpmTarget;
                if ($meanInterval > ($intervalTarget * 1.15)) {
                    $status = 'too_slow';
                } elseif ($meanInterval < ($intervalTarget * 0.85)) {
                    $status = 'too_fast';
                }
            }

            $lstmConfidence = null;

            if ($intervalCount > 0) {
                $lstmResult = $this->panggilLstmApi($intervalData);
                if ($lstmResult) {
                    $status         = $this->konversiLabelLstm($lstmResult['kondisi']);
                    $lstmConfidence = $lstmResult['kepercayaan'] ?? null;
                    Log::info('LSTM Prediction', [
                        'kondisi'      => $lstmResult['kondisi'],
                        'kepercayaan'  => $lstmConfidence,
                        'status_final' => $status,
                    ]);
                } else {
                    Log::warning('LSTM API tidak merespons, pakai rule-based');
                }
            }

            $monitoring = Monitoring::create([
                'patient_id'                  => $patient->id,
                'device_id'                   => $device->id,
                'total_drops'                 => $request->total_drops,
                'estimated_volume_remaining'  => $request->remaining_volume,
                'current_tpm'                 => $request->current_tpm,
                'tpm_target'                  => $tpmTarget,
                'mean_interval'               => $meanInterval,
                'std_interval'                => $stdInterval,
                'interval_data'               => $intervalData,
                'status'                      => $status,
                'is_anomaly'                  => ($status !== 'normal'),
                'lstm_confidence'             => $lstmConfidence,
                'recorded_at'                 => now(),
            ]);

            if ($intervalCount > 0) {
                RawDripData::create([
                    'device_key'    => $request->device_key,
                    'patient_id'    => $patient->id,
                    'tpm_target'    => $tpmTarget,
                    'interval_data' => $intervalData,
                    'tpm_aktual'    => $request->current_tpm,
                    'kondisi_label' => 'unknown',
                    'recorded_at'   => now(),
                ]);

                Log::info('Raw drip data tersimpan', [
                    'device_key'     => $request->device_key,
                    'interval_count' => $intervalCount,
                    'tpm_aktual'     => $request->current_tpm,
                ]);
            }

            $patient->update([
                'current_volume'      => $request->remaining_volume,
                'current_tpm'         => $request->current_tpm,
                'status'              => $status,
                'last_monitoring_at'  => now(),
            ]);

            Log::info('Data monitoring tersimpan', [
                'patient_id'        => $patient->id,
                'status'            => $status,
                'interval_count'    => $intervalCount,
                'mean_interval'     => $meanInterval,
                'lstm_confidence'   => $lstmConfidence,
                'remaining_percent' => $remainingPercent,
            ]);

            $lastRecord = \App\Models\LstmDataset::where('device_key', $request->device_key)
                ->latest('recorded_at')->first();

            $shouldSave    = !$lastRecord;
            $intervalDrops = 0;

            if ($lastRecord) {
                $minutesDiff = $lastRecord->recorded_at->diffInMinutes(now());
                if ($minutesDiff >= 1) {
                    $shouldSave    = true;
                    $intervalDrops = $request->total_drops - $lastRecord->total_drops;
                }
            }

            if ($shouldSave) {
                \App\Models\LstmDataset::create([
                    'device_key'     => $request->device_key,
                    'total_drops'    => $request->total_drops,
                    'current_tpm'    => $request->current_tpm,
                    'interval_drops' => $intervalDrops,
                    'status'         => $status,
                    'label'          => ($status === 'normal') ? 'normal' : 'anomaly',
                    'recorded_at'    => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data'    => [
                    'patient_name'      => $patient->name,
                    'status'            => $status,
                    'remaining_percent' => $remainingPercent,
                    'interval_count'    => $intervalCount,
                    'mean_interval'     => $meanInterval,
                    'lstm_confidence'   => $lstmConfidence,
                    'recorded_at'       => $monitoring->recorded_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error receiveData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function panggilLstmApi(array $intervalData): ?array
    {
        try {
            $flaskUrl = env('LSTM_API_URL', 'http://127.0.0.1:5000/predict');
            $response = Http::timeout(5)->post($flaskUrl, [
                'interval_data' => $intervalData,
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            Log::warning('LSTM API response tidak sukses', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::warning('LSTM API tidak bisa dihubungi: ' . $e->getMessage());
            return null;
        }
    }

    private function konversiLabelLstm(string $kondisi): string
    {
        return match($kondisi) {
            'normal'         => 'normal',
            'terlalu_cepat'  => 'too_fast',
            'terlalu_lambat' => 'too_slow',
            'macet'          => 'stuck',
            'habis'          => 'empty',
            default          => 'normal',
        };
    }
}