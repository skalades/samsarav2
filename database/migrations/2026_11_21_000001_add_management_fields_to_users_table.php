<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('verifikator')->after('email'); // superadmin, admin, verifikator, kasir, petugas_lapangan, inventori, auditor
            $table->string('phone', 25)->nullable()->after('role');
            $table->string('status', 20)->default('active')->after('phone'); // active, suspended
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'status', 'last_login_at']);
        });
    }
};
