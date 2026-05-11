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
        'current_volume',        
        'drop_factor',
        'duration_hours',
        'target_tpm',
        'current_tpm',           
        'status',                 
        'device_key',
        'user_id',
        'last_monitoring_at',    
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function monitorings()
    {
        return $this->hasMany(Monitoring::class);
    }

    public function latestMonitoring()
    {
        return $this->hasOne(Monitoring::class)->latest('recorded_at');
    }
    
    public static function findByDeviceKey($deviceKey)
    {
        return self::where('device_key', $deviceKey)->first();
    }

}
