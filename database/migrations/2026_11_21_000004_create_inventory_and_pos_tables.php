<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('name');
            $table->string('category', 40); // tenda, shuttle_ojek, alat_gunung, porter, konsumsi
            $table->string('type', 20)->default('good'); // rental, good, service
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock_total')->default(0);
            $table->integer('stock_available')->default(0);
            $table->string('unit', 20)->default('unit'); // unit, trip, pcs, porsi
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique(); // POS-202611-0001
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('simaksi_registration_id')->nullable()->constrained('simaksi_registrations')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 25)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('cash_tendered', 12, 2)->default(0);
            $table->decimal('cash_change', 12, 2)->default(0);
            $table->string('payment_method', 20)->default('cash'); // cash, qris, transfer
            $table->string('status', 20)->default('completed'); // completed, voided, refunded
            $table->text('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('item_name');
            $table->string('category', 40)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
        Schema::dropIfExists('inventory_items');
    }
};
