<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_order_has_product_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_order_has_product_id');
            $table->foreign('customer_order_has_product_id', 'cophw_product_fk')
                  ->references('id')->on('customer_order_has_products')->cascadeOnDelete();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id', 'cophw_warehouse_fk')
                  ->references('id')->on('warehouses')->restrictOnDelete();
            $table->decimal('qnt', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['customer_order_has_product_id', 'warehouse_id'], 'cophw_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_order_has_product_warehouses');
    }
};