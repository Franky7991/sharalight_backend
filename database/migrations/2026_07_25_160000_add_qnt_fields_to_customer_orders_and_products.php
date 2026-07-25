<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->decimal('qnt', 10, 2)->default(0)->after('state');
            $table->decimal('qnt_produced', 10, 2)->default(0)->after('qnt');
        });

        Schema::table('customer_order_has_products', function (Blueprint $table) {
            $table->decimal('qnt_produced', 10, 2)->default(0)->after('qnt');
        });
    }

    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn(['qnt', 'qnt_produced']);
        });

        Schema::table('customer_order_has_products', function (Blueprint $table) {
            $table->dropColumn('qnt_produced');
        });
    }
};