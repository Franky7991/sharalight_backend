<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('customer_order_id')->constrained('customer_orders')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['shipment_id', 'customer_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_details');
    }
};