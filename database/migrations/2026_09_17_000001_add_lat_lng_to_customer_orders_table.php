<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coordinate geografiche dell'indirizzo di consegna dell'ordine cliente.
     *
     * lat/lng vengono valorizzati quando l'indirizzo è scelto da un servizio
     * di geocoding (autocomplete nella webapp): restano NULL per gli ordini
     * inseriti a mano o creati dal pannello di amministrazione.
     *
     * decimal(10,7) = 3 cifre intere + 7 decimali: copre l'intero range
     * ammesso (lat -90..90, lng -180..180) con precisione di ~1 cm.
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('address');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
