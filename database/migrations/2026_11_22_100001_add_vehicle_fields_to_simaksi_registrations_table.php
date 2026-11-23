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
            $table->boolean('has_vehicle')->default(false)->after('tent_count');
            $table->unsignedSmallInteger('motorcycle_count')->default(0)->after('has_vehicle');
            $table->unsignedSmallInteger('car_count')->default(0)->after('motorcycle_count');
            $table->decimal('parking_fee', 12, 2)->default(0)->after('car_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropColumn(['has_vehicle', 'motorcycle_count', 'car_count', 'parking_fee']);
        });
    }
};
