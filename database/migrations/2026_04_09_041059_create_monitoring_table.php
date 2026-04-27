<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('total_drops')->default(0);
            $table->float('estimated_volume_remaining',8,2);
            $table->integer('current_tpm')->default(0);
            $table->enum('status', ['normal', 'too_fast', 'too_slow', 'stuck', 'empty'])->default('normal');
            $table->boolean('is_anomaly')->default(false);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};
