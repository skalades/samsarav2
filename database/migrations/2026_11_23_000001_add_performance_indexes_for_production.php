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
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->index(['status', 'hiking_date'], 'simaksi_status_hiking_date_idx');
            $table->index('hiking_date', 'simaksi_hiking_date_idx');
            $table->index('leader_phone', 'simaksi_leader_phone_idx');
        });

        Schema::table('ojek_trips', function (Blueprint $table) {
            $table->index('trip_status', 'ojek_trips_trip_status_idx');
            $table->index('settlement_status', 'ojek_trips_settlement_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at'], 'audit_logs_action_created_at_idx');
            $table->index('created_at', 'audit_logs_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropIndex('simaksi_status_hiking_date_idx');
            $table->dropIndex('simaksi_hiking_date_idx');
            $table->dropIndex('simaksi_leader_phone_idx');
        });

        Schema::table('ojek_trips', function (Blueprint $table) {
            $table->dropIndex('ojek_trips_trip_status_idx');
            $table->dropIndex('ojek_trips_settlement_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_action_created_at_idx');
            $table->dropIndex('audit_logs_created_at_idx');
        });
    }
};
