<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->foreignId('unit_of_measure_id')
                  ->nullable()
                  ->after('product_category_id')
                  ->constrained('unit_of_measures')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropColumn('unit_of_measure_id');
        });
    }
};
