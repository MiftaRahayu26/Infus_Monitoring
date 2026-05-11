<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawDripData extends Model
{
    use HasFactory;

    protected $table = 'raw_drip_data';

    protected $fillable = [
        'device_key',
        'patient_id',
        'tpm_target',
        'interval_data',
        'tpm_aktual',
        'kondisi_label',
        'recorded_at',
    ];

    protected $casts = [
        'interval_data' => 'array', 
        'recorded_at'   => 'datetime',
        'tpm_target'    => 'integer',
        'tpm_aktual'    => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getKondisiLabelIdAttribute(): string
    {
        return match($this->kondisi_label) {
            'normal'    => 'Normal',
            'too_fast'  => 'Terlalu Cepat',
            'too_slow'  => 'Terlalu Lambat',
            'stuck'     => 'Macet',
            'empty'     => 'Habis',
            default     => 'Belum Dilabel',
        };
    }
    public function getJumlahTetesAttribute(): int
    {
        return count($this->interval_data ?? []);
    }
    public function getMeanIntervalAttribute(): ?float
    {
        $data = $this->interval_data;
        if (empty($data)) return null;
        return round(array_sum($data) / count($data), 4);
    }
    public function getSudahDilabelAttribute(): bool
    {
        return $this->kondisi_label !== 'unknown';
    }

    public function scopeBerlabel($query)
    {
        return $query->where('kondisi_label', '!=', 'unknown');
    }

    public function scopeBelumDilabel($query)
    {
        return $query->where('kondisi_label', 'unknown');
    }

    public function scopeKondisi($query, string $kondisi)
    {
        return $query->where('kondisi_label', $kondisi);
    }

    public function scopeTpm($query, int $tpm)
    {
        return $query->where('tpm_target', $tpm);
    }

    public function scopeDevice($query, string $deviceKey)
    {
        return $query->where('device_key', $deviceKey);
    }

    public function scopePeriode($query, string $dari, string $sampai)
    {
        return $query->whereBetween('recorded_at', [$dari, $sampai]);
    }
}