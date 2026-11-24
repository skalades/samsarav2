<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revamp SAMSARA OPS:
     * - 4 level akses (superadmin, kasir, gatekeeper, ranger)
     * - Alur pemeriksaan Gate naik/turun + checklist alat & logistik (hybrid)
     * - Log pesan WhatsApp Gateway
     * - Deposit sampah dinonaktifkan sementara
     */
    public function up(): void
    {
        // 1. Migrasi peran lama → 4 level
        $map = [
            'admin' => 'superadmin',
            'auditor' => 'superadmin',
            'verifikator' => 'kasir',
            'cashier' => 'kasir',
            'inventori' => 'kasir',
            'gate_officer' => 'gatekeeper',
            'petugas_lapangan' => 'gatekeeper',
        ];
        foreach ($map as $old => $new) {
            DB::table('users')->where('role', $old)->update(['role' => $new]);
        }

        // 2. Jejak pemeriksaan Gate & serah-terima Kasir
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->foreignId('gate_up_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gate_up_at')->nullable();
            $table->text('gate_up_notes')->nullable();
            $table->string('gate_photo_path')->nullable();
            $table->boolean('gate_photo_verified')->default(false);

            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();

            $table->foreignId('gate_down_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gate_down_at')->nullable();
            $table->text('gate_down_notes')->nullable();
            $table->boolean('gate_down_clear')->nullable();

            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
        });

        // 3. Checklist alat (centang) & logistik (jumlah) per rombongan
        Schema::create('simaksi_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simaksi_registration_id')->constrained('simaksi_registrations')->cascadeOnDelete();
            $table->string('type', 20); // gear (centang) | logistic (jumlah)
            $table->string('name', 100);
            $table->string('unit', 20)->nullable();
            $table->unsignedInteger('qty_up')->nullable();
            $table->unsignedInteger('qty_down')->nullable();
            $table->boolean('checked_up')->default(false);
            $table->boolean('checked_down')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['simaksi_registration_id', 'type']);
        });

        // 4. Log pesan WhatsApp Gateway (antrian, terkirim, gagal)
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simaksi_registration_id')->nullable()->constrained('simaksi_registrations')->nullOnDelete();
            $table->string('event', 50); // payment_confirmed, manual, test
            $table->string('phone', 30);
            $table->text('message');
            $table->string('driver', 30);
            $table->string('status', 20)->default('queued'); // queued, sent, failed, skipped
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // 5. Deposit sampah dinonaktifkan sementara
        if (Schema::hasTable('app_settings')) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => 'trash_deposit_enabled'],
                ['value' => '0', 'label' => 'Status Deposit Sampah', 'type' => 'boolean', 'group' => 'tariffs', 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('simaksi_inspection_items');

        Schema::table('simaksi_registrations', function (Blueprint $table) {
            foreach (['gate_up_by', 'released_by', 'gate_down_by', 'finalized_by'] as $fk) {
                $table->dropConstrainedForeignId($fk);
            }
            $table->dropColumn([
                'gate_up_at', 'gate_up_notes', 'gate_photo_path', 'gate_photo_verified',
                'released_at', 'gate_down_at', 'gate_down_notes', 'gate_down_clear', 'finalized_at',
            ]);
        });
    }
};
