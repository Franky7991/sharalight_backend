<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_order_has_product_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_order_has_product_id');
            $table->foreignId('recipe_id')->constrained('recipes')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->timestamps();

            $table->foreign('customer_order_has_product_id', 'cohpd_cohp_id_foreign')
                  ->references('id')->on('customer_order_has_products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_order_has_product_details', function (Blueprint $table) {
            $table->dropForeign('cohpd_cohp_id_foreign');
        });
        Schema::dropIfExists('customer_order_has_product_details');
    }
};
