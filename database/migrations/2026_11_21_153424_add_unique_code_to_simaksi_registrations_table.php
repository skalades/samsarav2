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
            $table->unsignedSmallInteger('unique_code')->default(0)->after('tent_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
