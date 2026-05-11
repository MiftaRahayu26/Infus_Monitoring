<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;       

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring', function (Blueprint $table) {

            $table->foreignId('device_id')
                  ->nullable()
                  ->after('patient_id')
                  ->constrained('devices')
                  ->onDelete('set null');

            $table->integer('tpm_target')
                  ->nullable()
                  ->after('current_tpm');

            $table->float('mean_interval', 8, 4)
                  ->nullable()
                  ->after('tpm_target');

            $table->float('std_interval', 8, 4)
                  ->nullable()
                  ->after('mean_interval');

            $table->json('interval_data')
                  ->nullable()
                  ->after('std_interval');

            $table->float('lstm_confidence', 5, 2)
                  ->nullable()
                  ->after('is_anomaly');

            $table->index(['patient_id', 'recorded_at']);
            $table->index('is_anomaly');
            $table->index('status');
        });

        DB::statement("ALTER TABLE monitoring MODIFY COLUMN status 
            ENUM('normal','too_fast','too_slow','stuck','empty','unknown') 
            NOT NULL DEFAULT 'unknown'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE monitoring MODIFY COLUMN status 
            ENUM('normal','too_fast','too_slow','stuck','empty') 
            NOT NULL DEFAULT 'normal'");

        Schema::table('monitoring', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropColumn([
                'device_id',
                'tpm_target',
                'mean_interval',
                'std_interval',
                'interval_data',
                'lstm_confidence',
            ]);

            $table->dropIndex(['patient_id', 'recorded_at']);
            $table->dropIndex(['is_anomaly']);
            $table->dropIndex(['status']);
        });
    }
};