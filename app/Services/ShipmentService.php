<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\Movement;
use App\Models\Setting;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Marca la spedizione come "Spedita".
     *
     * Operazioni eseguite in transazione:
     * 1. crea i movimenti di scarico per i prodotti (dai magazzini allocati)
     *    di tutti gli ordini clienti presenti nella spedizione;
     * 2. porta la spedizione nello stato "Spedito";
     * 3. porta tutti gli ordini clienti collegati nello stato "Spedito".
     */
    public function markAsShipped(Shipment $shipment): void
    {
        if ($shipment->isShipped()) {
            return;
        }

        if (! $shipment->isCreated()) {
            throw new \RuntimeException('La spedizione non è nello stato "Creato".');
        }

        $unloadCausalId = Setting::get(Setting::KEY_SHIPMENT_UNLOAD_CAUSAL);

        if (! $unloadCausalId) {
            throw new \RuntimeException('Causale di scarico per spedizione non configurata. Impostala in Impostazioni.');
        }

        $orders = $shipment->customerOrders()->with([
            'products.warehouses',
            'products.details.product',
        ])->get();

        if ($orders->isEmpty()) {
            throw new \RuntimeException('Nessun ordine cliente presente nella spedizione.');
        }

        DB::transaction(function () use ($shipment, $orders, $unloadCausalId) {
            // 1. Movimenti di scarico: un movimento per ogni allocazione a magazzino
            foreach ($orders as $order) {
                foreach ($order->products as $cop) {
                    // Composition key: hash degli ingredienti scelti ordinati per recipe_id
                    // (già eager-loaded: details.product)
                    $compositionKey = null;

                    if ($cop->details->isNotEmpty()) {
                        $details  = $cop->details->sortBy('recipe_id');
                        $keyParts = $details->map(fn($d) => $d->recipe_id . ':' . $d->product_id)->toArray();
                        $compositionKey = substr(md5(implode('|', $keyParts)), 0, 16);
                    }

                    foreach ($cop->warehouses as $allocation) {
                        if ((float) $allocation->qnt <= 0) {
                            continue;
                        }

                        Movement::query()->create([
                            'warehouse_id'       => $allocation->warehouse_id,
                            'product_id'         => $cop->product_id,
                            'causal_id'          => $unloadCausalId,
                            'qnt'                => (float) $allocation->qnt,
                            'unit_of_measure_id' => $cop->unit_of_measure_id,
                            'composition_key'    => $compositionKey,
                        ]);
                    }
                }
            }

            // 2. Stato spedizione
            $shipment->update(['state' => Shipment::STATE_SHIPPED]);

            // 3. Stato ordini clienti collegati
            CustomerOrder::query()
                ->whereIn('id', $orders->pluck('id'))
                ->update(['state' => CustomerOrder::STATE_SHIPPED]);
        });
    }
}