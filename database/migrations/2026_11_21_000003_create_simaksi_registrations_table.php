<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simaksi_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code', 30)->unique(); // e.g. SMK-202611-001

            // Ketua Rombongan
            $table->string('leader_name');
            $table->string('leader_nik', 20);
            $table->string('leader_phone', 25);
            $table->string('leader_email')->nullable();
            $table->string('leader_city')->nullable();

            // Kontak Darurat
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone', 25);
            $table->string('emergency_relationship', 30)->default('Keluarga');

            // Jadwal & Kapasitas
            $table->date('hiking_date');
            $table->date('return_date')->nullable();
            $table->unsignedSmallInteger('total_climbers')->default(2);

            // Alur Status: pending (menunggu bayar/review) -> paid (lunas & siap check-in) -> checked_in (di jalur) -> completed (selesai)
            $table->string('status', 30)->default('pending');
            $table->text('revision_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Aktor & Jejak Verifikasi
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();

            // Keuangan & Tarif
            $table->decimal('simaksi_fee', 12, 2)->default(0);
            $table->decimal('ojek_fee', 12, 2)->default(0);
            $table->decimal('tent_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_status', 20)->default('unpaid'); // unpaid, paid, refunded
            $table->string('payment_method', 30)->nullable(); // qris, transfer, cash_pos
            $table->timestamp('paid_at')->nullable();

            // Pengelolaan Sampah (PRD: jaminan sampah & tracking)
            $table->decimal('trash_deposit_amount', 12, 2)->default(50000);
            $table->string('trash_deposit_status', 20)->default('unpaid'); // unpaid, deposited, refunded, forfeited
            $table->string('trash_bag_code', 30)->nullable();

            $table->timestamps();
        });

        Schema::create('climber_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simaksi_registration_id')->constrained('simaksi_registrations')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('nik', 20)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('gender', 1)->default('L'); // L / P
            $table->string('city_origin')->nullable();
            $table->boolean('is_leader')->default(false);
            $table->boolean('is_checked_in')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climber_members');
        Schema::dropIfExists('simaksi_registrations');
    }
};
