<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')       ->constrained('warehouses')      ->restrictOnDelete();
            $table->foreignId('product_id')         ->constrained('products')         ->restrictOnDelete();
            $table->decimal('qnt', 15, 2)->default(0);
            $table->foreignId('unit_of_measure_id') ->constrained('unit_of_measures') ->restrictOnDelete();
            $table->timestamps();

            // Una sola riga per combinazione magazzino + prodotto
            $table->unique(['warehouse_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
