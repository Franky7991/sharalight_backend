<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce lo stato ordine cliente "In Spedizione"
     * (App\Models\CustomerOrder::STATE_IN_SHIPMENT).
     *
     * La colonna state_before_shipment memorizza lo stato dell'ordine prima
     * dell'inserimento in spedizione: serve a ripristinarlo quando l'ordine
     * viene rimosso dalla spedizione (nessun movimento di magazzino).
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->string('state_before_shipment')->nullable()->after('state');
        });

        // Ordini già presenti in spedizioni non ancora spedite: salva lo stato
        // attuale e porta l'ordine nello stato "In Spedizione".
        $orderIds = DB::table('shipment_details')
            ->join('shipments', 'shipments.id', '=', 'shipment_details.shipment_id')
            ->where('shipments.state', 'created')
            ->pluck('shipment_details.customer_order_id');

        foreach ($orderIds->unique() as $orderId) {
            // Un'unica UPDATE a due colonne: MySQL valuta le assegnazioni da
            // sinistra a destra, quindi state_before_shipment riceve lo stato
            // precedente prima che "state" venga sovrascritto.
            DB::table('customer_orders')
                ->where('id', $orderId)
                ->whereNotIn('state', ['in_shipment', 'shipped'])
                ->update([
                    'state_before_shipment' => DB::raw('state'),
                    'state'                 => 'in_shipment',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ripristina lo stato precedente degli ordini in spedizione.
        DB::table('customer_orders')
            ->where('state', 'in_shipment')
            ->update([
                'state'                 => DB::raw("COALESCE(state_before_shipment, 'products_allocated')"),
                'state_before_shipment' => null,
            ]);

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn('state_before_shipment');
        });
    }
};