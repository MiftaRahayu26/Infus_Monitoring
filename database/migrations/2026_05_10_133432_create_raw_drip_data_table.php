<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_drip_data', function (Blueprint $table) {
            $table->id();
            $table->string('device_key', 50);
            $table->foreignId('patient_id')->nullable()
                  ->constrained('patients')->onDelete('set null');
            $table->integer('tpm_target')->default(0);
            $table->json('interval_data');
            $table->integer('tpm_aktual')->default(0);
            $table->string('kondisi_label', 20)->default('unknown');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index('device_key');
            $table->index('kondisi_label');
            $table->index('tpm_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_drip_data');
    }
};