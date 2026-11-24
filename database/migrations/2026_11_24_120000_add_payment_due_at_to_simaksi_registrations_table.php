<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batas waktu pembayaran: booking yang belum dibayar & belum unggah bukti
     * setelah melewati payment_due_at akan dibatalkan otomatis dan kuotanya dilepas.
     */
    public function up(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->timestamp('payment_due_at')->nullable()->after('paid_at');
            $table->index(['status', 'payment_due_at'], 'simaksi_status_payment_due_idx');
        });
    }

    public function down(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropIndex('simaksi_status_payment_due_idx');
            $table->dropColumn('payment_due_at');
        });
    }
};
