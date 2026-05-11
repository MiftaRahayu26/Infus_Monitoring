<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LstmDataset extends Model
{
    protected $table = 'lstm_datasets';

    protected $fillable = [
        'device_key',
        'total_drops',
        'current_tpm',
        'interval_drops',
        'status',
        'label',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}