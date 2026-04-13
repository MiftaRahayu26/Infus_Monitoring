<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use HasFactory;

    // Nama tabel (karena Anda pakai 'monitoring')
    protected $table = 'monitoring';

    protected $fillable = [
        'patient_id',
        'total_drops',
        'estimated_volume_remaining',
        'current_tpm',
        'status',
        'is_anomaly',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'is_anomaly' => 'boolean',
    ];

    // Relasi ke Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Accessor untuk status dalam bahasa Indonesia
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'normal' => 'Normal',
            'too_fast' => 'Terlalu Cepat',
            'too_slow' => 'Terlalu Lambat',
            'stuck' => 'Macet',
            'empty' => 'Habis',
            default => 'Unknown',
        };
    }
}