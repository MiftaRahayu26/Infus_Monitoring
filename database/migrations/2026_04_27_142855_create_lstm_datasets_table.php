<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lstm_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('device_key');
            $table->integer('total_drops');
            $table->integer('current_tpm');
            $table->integer('interval_drops')->default(0);
            $table->string('status');
            $table->string('label'); 
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lstm_datasets');
    }
};