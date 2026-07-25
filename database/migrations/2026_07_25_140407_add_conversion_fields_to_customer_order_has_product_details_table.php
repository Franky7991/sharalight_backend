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
        Schema::table('customer_order_has_product_details', function (Blueprint $table) {
            $table->decimal('original_qnt', 10, 4)->nullable()->after('product_id');
            $table->unsignedBigInteger('original_unit_of_measure_id')->nullable()->after('original_qnt');
            $table->foreign('original_unit_of_measure_id', 'cohpd_original_uom_id')->references('id')->on('unit_of_measures')->restrictOnDelete();
            $table->decimal('conversion_qnt', 10, 4)->nullable()->after('original_unit_of_measure_id');
            $table->unsignedBigInteger('conversion_unit_of_measure_id')->nullable()->after('conversion_qnt');
            $table->foreign('conversion_unit_of_measure_id', 'cohpd_conversion_uom_id')->references('id')->on('unit_of_measures')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_order_has_product_details', function (Blueprint $table) {
            $table->dropForeign('cohpd_conversion_uom_id');
            $table->dropForeign('cohpd_original_uom_id');
            $table->dropColumn(['conversion_unit_of_measure_id', 'conversion_qnt', 'original_unit_of_measure_id', 'original_qnt']);
        });
    }
};
