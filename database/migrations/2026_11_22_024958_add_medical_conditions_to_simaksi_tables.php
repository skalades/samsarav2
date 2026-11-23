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
            $table->string('medical_conditions')->nullable()->default('Tidak Ada')->after('leader_city');
        });

        Schema::table('climber_members', function (Blueprint $table) {
            $table->string('medical_conditions')->nullable()->default('Tidak Ada')->after('city_origin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropColumn('medical_conditions');
        });

        Schema::table('climber_members', function (Blueprint $table) {
            $table->dropColumn('medical_conditions');
        });
    }
};
