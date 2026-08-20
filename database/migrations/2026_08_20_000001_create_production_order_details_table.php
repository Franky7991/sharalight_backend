<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('customer_order_has_product_id')->constrained('customer_order_has_products')->restrictOnDelete();
            $table->timestamps();

            // Una riga di ordine cliente può essere pianificata in un solo ordine di produzione.
            $table->unique('customer_order_has_product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_details');
    }
};