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
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code', 30)->unique(); // EXP-YYYYMMDD-XXXX
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 50); // bbm_genset, operasional_pos, logistik_sar, konsumsi_petugas, kebersihan_sampah, perbaikan_alat, lainnya
            $table->decimal('amount', 12, 2);
            $table->string('recipient_name'); // Toko / Driver / Pihak ketiga
            $table->string('payment_method', 20)->default('cash'); // cash, transfer
            $table->text('notes'); // Keterangan keperluan mendadak
            $table->string('receipt_photo_path')->nullable();
            $table->string('status', 20)->default('active'); // active, voided
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status'], 'exp_created_status_idx');
            $table->index('category', 'exp_category_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
