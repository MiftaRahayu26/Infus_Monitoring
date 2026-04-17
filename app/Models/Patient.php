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
        'drop_factor',
        'duration_hours',
        'target_tpm',
        'device_key',     
        'user_id',
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
    
    // Relasi ke Device (berdasarkan device_key)
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_key', 'device_key');
    }
}
