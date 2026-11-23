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
            $table->string('group_photo_path', 500)->nullable()->after('payment_proof_path');
            $table->timestamp('group_photo_uploaded_at')->nullable()->after('group_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropColumn(['group_photo_path', 'group_photo_uploaded_at']);
        });
    }
};
