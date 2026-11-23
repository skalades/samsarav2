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
            $table->unsignedSmallInteger('ojek_count')->default(0)->after('ojek_fee');
            $table->unsignedSmallInteger('tent_count')->default(0)->after('tent_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropColumn(['ojek_count', 'tent_count']);
        });
    }
};
