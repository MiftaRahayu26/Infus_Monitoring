<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->decimal('current_volume', 8, 2)->nullable()->after('initial_volume');
            $table->integer('current_tpm')->default(0)->after('target_tpm');
            $table->string('status')->default('normal')->after('current_tpm');
            $table->timestamp('last_monitoring_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['current_volume', 'current_tpm', 'status', 'last_monitoring_at']);
        });
    }
};