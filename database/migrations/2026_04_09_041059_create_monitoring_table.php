<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->onDelete('cascade');

            $table->integer('total_drops')->default(0);
            $table->float('estimated_volume_remaining', 8, 2);
            $table->integer('current_tpm')->default(0);

            $table->foreignId('device_id')
                  ->nullable()
                  ->constrained('devices')
                  ->onDelete('set null');

            $table->integer('tpm_target')->nullable();

            $table->float('mean_interval', 8, 4)->nullable();
            $table->float('std_interval', 8, 4)->nullable();

            $table->json('interval_data')->nullable();

            $table->enum('status', [
                'normal',
                'too_fast',
                'too_slow',
                'stuck',
                'empty',
                'unknown',      
            ])->default('unknown');  

            $table->boolean('is_anomaly')->default(false);

            $table->float('lstm_confidence', 5, 2)->nullable();

            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['patient_id', 'recorded_at']);
            $table->index('is_anomaly');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};