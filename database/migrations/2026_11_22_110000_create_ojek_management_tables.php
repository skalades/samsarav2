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
        // 1. Master Data Ojek Resmi Basecamp
        Schema::create('ojek_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique(); // No Rompi / No Lambung, misal: OJK-01
            $table->string('name'); // Nama Lengkap Pengemudi
            $table->string('plate_number'); // Plat Nomor Kendaraan, misal: Z 4521 GA
            $table->string('phone')->nullable(); // Nomor WhatsApp / HP
            $table->string('status')->default('standby'); // standby, on_trip, off_duty, suspended
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Transaksi Operasional & Penugasan Trip Ojek
        Schema::create('ojek_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_code')->unique(); // VCR-OJK-YYYYMMDD-XXXX
            $table->foreignId('simaksi_registration_id')->nullable()->constrained('simaksi_registrations')->nullOnDelete();
            $table->foreignId('pos_order_id')->nullable()->constrained('pos_orders')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('ojek_drivers')->nullOnDelete();
            $table->string('passenger_name');
            $table->string('passenger_phone')->nullable();
            $table->string('route')->default('Basecamp - Pos 1 (Kebun Teh)');
            $table->decimal('fare_amount', 12, 2)->default(30000);
            $table->decimal('driver_share', 12, 2)->default(25000);
            $table->decimal('basecamp_share', 12, 2)->default(5000);
            $table->string('payment_status')->default('paid'); // pending, paid, refunded
            $table->string('trip_status')->default('voucher_issued'); // voucher_issued, assigned, on_trip, completed, cancelled
            $table->string('settlement_status')->default('unsettled'); // unsettled, settled
            $table->unsignedBigInteger('ojek_settlement_id')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Rekonsiliasi & Pencairan Honor Pengemudi (Settlement / Payout)
        Schema::create('ojek_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_code')->unique(); // STL-OJK-YYYYMMDD-XXXX
            $table->foreignId('driver_id')->constrained('ojek_drivers')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_trips');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, transfer
            $table->text('notes')->nullable();
            $table->timestamp('settled_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojek_settlements');
        Schema::dropIfExists('ojek_trips');
        Schema::dropIfExists('ojek_drivers');
    }
};
