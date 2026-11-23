<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_quotas', function (Blueprint $table) {
            $table->id();
            $table->date('quota_date')->unique();
            $table->unsignedInteger('max_quota')->default(150); // Kuota maksimal sesuai PRD
            $table->unsignedInteger('booked_quota')->default(0);
            $table->boolean('is_closed')->default(false); // Penutupan darurat / badai
            $table->string('closure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('trail_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('status', 30)->default('open'); // open, caution, closed
            $table->string('pos2_water_status')->default('Melimpah (12 L/mnt)');
            $table->string('night_temp_celsius', 20)->default('9°C');
            $table->string('weather_condition', 50)->default('Cerah Berawan');
            $table->string('soil_condition', 50)->default('Padat & Kering');
            $table->string('wind_speed', 50)->default('14 km/jam Barat Daya');
            $table->string('sar_frequency', 30)->default('149.250 MHz');
            $table->text('ranger_notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trail_conditions');
        Schema::dropIfExists('daily_quotas');
    }
};
