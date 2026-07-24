<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')       ->constrained('warehouses')      ->restrictOnDelete();
            $table->foreignId('product_id')         ->constrained('products')         ->restrictOnDelete();
            $table->foreignId('causal_id')          ->constrained('causals')          ->restrictOnDelete();
            $table->decimal('qnt', 15, 2);
            $table->foreignId('unit_of_measure_id') ->constrained('unit_of_measures') ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
