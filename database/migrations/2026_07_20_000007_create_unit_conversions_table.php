<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_unit_of_measure_id')->constrained('unit_of_measures')->restrictOnDelete();
            $table->decimal('from_quantity', 15, 2);
            $table->foreignId('to_unit_of_measure_id')->constrained('unit_of_measures')->restrictOnDelete();
            $table->decimal('to_quantity', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
