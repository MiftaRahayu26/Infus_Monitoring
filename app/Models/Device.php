<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_key',
        'device_type',
        'status',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    /**
     * Relasi ke Patient (sebuah device bisa dipakai oleh satu pasien)
     */
    public function patient()
    {
        return $this->hasOne(Patient::class, 'device_key', 'device_key');
    }

    /**
     * Cek apakah device sedang online
     */
    public function isOnline()
    {
        return $this->status === 'online';
    }

    /**
     * Cek apakah device sedang terassign ke pasien
     */
    public function isAssigned()
    {
        return $this->patient()->exists();
    }

    /**
     * Update status menjadi online dengan timestamp sekarang
     */
    public function markAsOnline()
    {
        $this->update([
            'status' => 'online',
            'last_seen' => now(),
        ]);
    }

    /**
     * Update status menjadi offline
     */
    public function markAsOffline()
    {
        $this->update(['status' => 'offline']);
    }

    /**
     * Update status menjadi error
     */
    public function markAsError()
    {
        $this->update(['status' => 'error']);
    }

    /**
     * Accessor untuk badge status (untuk tampilan)
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'online' => '<span class="badge bg-success">Online</span>',
            'offline' => '<span class="badge bg-secondary">Offline</span>',
            'error' => '<span class="badge bg-danger">Error</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Accessor untuk last_seen yang sudah diformat
     */
    public function getLastSeenFormattedAttribute()
    {
        if (!$this->last_seen) {
            return 'Tidak pernah';
        }
        
        return $this->last_seen->diffForHumans();
    }
}