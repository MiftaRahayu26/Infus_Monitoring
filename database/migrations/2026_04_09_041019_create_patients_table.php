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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('patient_id')->unique();
            $table->string('room')->nullable();
            $table->string('bed_number');
            $table->integer('infusion_type')->default('NaCl 0,9%');
            $table->integer('initial_volume');
            $table->integer('drop_factor')->default(20);
            $table->integer('duration_hours');
            $table->integer('target_tpm');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
