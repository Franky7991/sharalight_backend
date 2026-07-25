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
        Schema::create('customer_order_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_order_id');
            $table->foreign('customer_order_id')->references('id')->on('customer_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->unsignedBigInteger('unit_of_measure_id');
            $table->foreign('unit_of_measure_id')->references('id')->on('unit_of_measures')->restrictOnDelete();
            $table->decimal('total_qnt', 10, 4);
            $table->timestamps();

            $table->unique(['customer_order_id', 'product_id', 'unit_of_measure_id'], 'cos_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_order_summaries');
    }
};
