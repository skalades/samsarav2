<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('registration_code');
        });

        // Backfill tiket yang sudah ada agar semua tautan & verifikasi publik memiliki token acak kuat
        DB::table('simaksi_registrations')
            ->whereNull('access_token')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('simaksi_registrations')
                        ->where('id', $row->id)
                        ->update(['access_token' => bin2hex(random_bytes(16))]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropUnique(['access_token']);
            $table->dropColumn('access_token');
        });
    }
};
