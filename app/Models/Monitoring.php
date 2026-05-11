<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use HasFactory;

    protected $table = 'monitoring';

    protected $fillable = [
        'patient_id',
        'device_id',                    
        'total_drops',
        'estimated_volume_remaining',
        'current_tpm',
        'tpm_target',                   
        'mean_interval',                
        'std_interval',                 
        'interval_data',                
        'status',
        'is_anomaly',
        'lstm_confidence',              
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at'     => 'datetime',
        'is_anomaly'      => 'boolean',
        'interval_data'   => 'array',   
        'mean_interval'   => 'float',   
        'std_interval'    => 'float',   
        'lstm_confidence' => 'float',   
    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function device()                       
    {
        return $this->belongsTo(Device::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'normal'   => 'Normal',
            'too_fast' => 'Terlalu Cepat',
            'too_slow' => 'Terlalu Lambat',
            'stuck'    => 'Macet',
            'empty'    => 'Habis',
            default    => 'Unknown',
        };
    }

    public function getStatusColorAttribute() 
    {
        return match($this->status) {
            'normal'   => 'green',
            'too_fast' => 'yellow',
            'too_slow' => 'yellow',
            'stuck'    => 'red',
            'empty'    => 'red',
            default    => 'gray',
        };
    }

    public function getTpmDeviationAttribute()      
    {
        if (!$this->tpm_target || $this->tpm_target == 0) return null;
        return round(
            (($this->current_tpm - $this->tpm_target) / $this->tpm_target) * 100,
            2
        );
    }
    public function scopeAnomalyOnly($query)        
    {
        return $query->where('is_anomaly', true);
    }

    public function scopeRecent($query, int $minutes = 60)  
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }
}