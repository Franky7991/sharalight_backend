<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->decimal('qnt', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->decimal('qnt', 15, 4)->change();
        });
    }
};
