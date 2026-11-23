<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->foreignId('simaksi_registration_id')->nullable()->constrained('simaksi_registrations')->nullOnDelete();
            $table->foreignId('pos_order_id')->nullable()->constrained('pos_orders')->nullOnDelete();
            $table->string('customer_name');
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);

            // Serah Terima Pinjam
            $table->string('condition_out')->default('Bagus / Layak Pakai');
            $table->foreignId('handed_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handed_out_at')->nullable();

            // Pengembalian & Kondisi
            $table->string('condition_in')->nullable(); // Bagus, Rusak Ringan, Rusak Parah, Hilang
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();

            $table->string('status', 20)->default('rented'); // rented, returned_good, damaged, lost
            $table->decimal('damage_penalty', 12, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_records');
    }
};
