<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
     protected $fillable = [
        'nama',
        'patient_id',
        'room',
        'bed_number',
        'infusion_type',
        'initial_volume',
        'current_volume',        // ✅ Tambah ini
        'drop_factor',
        'duration_hours',
        'target_tpm',
        'current_tpm',           // ✅ Tambah ini
        'status',                 // ✅ Tambah ini
        'device_key',
        'user_id',
        'last_monitoring_at',    // ✅ Tambah ini
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Monitoring
    public function monitorings()
    {
        return $this->hasMany(Monitoring::class);
    }

    // Relasi ambil data monitoring terbaru
    public function latestMonitoring()
    {
        return $this->hasOne(Monitoring::class)->latest('recorded_at');
    }
    
    public static function findByDeviceKey($deviceKey)
    {
        return self::where('device_key', $deviceKey)->first();
    }
    // Relasi ke Device (berdasarkan device_key)
    // public function device()
    // {
    //    return $this->belongsTo(Device::class, 'device_key', 'device_key');
    // }
}
