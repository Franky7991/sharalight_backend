<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prezzi sugli ordini cliente.
     *
     * Formula: (prezzo candela + prezzo ingredienti scelti) × quantità
     *  - customer_order_has_products.price:          prezzo unitario della riga
     *                                                (prodotto ordinato + materie prime scelte)
     *  - customer_order_has_product_details.price:   prezzo fisso dell'ingrediente selezionato (nullable)
     *  - customer_orders.price:                      totale ordine = somma di (prezzo unitario × qnt)
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('qnt_produced');
        });

        Schema::table('customer_order_has_products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('qnt_produced');
        });

        Schema::table('customer_order_has_product_details', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('conversion_unit_of_measure_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_order_has_product_details', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('customer_order_has_products', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};