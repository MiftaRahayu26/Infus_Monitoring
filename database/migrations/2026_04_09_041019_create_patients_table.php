<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('patient_id')->unique();
            $table->string('room')->nullable();
            $table->string('bed_number')->nullable()->change();
            $table->string('infusion_type')->default('NaCl 0,9%')->change();
            $table->integer('initial_volume');
            $table->integer('drop_factor')->default(20);
            $table->integer('duration_hours');
            $table->integer('target_tpm');
            $table->foreignId('user_id')->nullable()->change();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('infusion_type')->default(0)->change();
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('bed_number')->nullable(false)->change();
        });
    }
};
