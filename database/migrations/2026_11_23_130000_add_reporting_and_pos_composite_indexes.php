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
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'pos_orders_status_created_at_idx');
        });

        Schema::table('operational_expenses', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'operational_expenses_status_created_at_idx');
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->index(['pos_order_id', 'category'], 'pos_order_items_order_cat_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropIndex('pos_orders_status_created_at_idx');
        });

        Schema::table('operational_expenses', function (Blueprint $table) {
            $table->dropIndex('operational_expenses_status_created_at_idx');
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropIndex('pos_order_items_order_cat_idx');
        });
    }
};
