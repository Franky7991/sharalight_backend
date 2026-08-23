<?php

namespace App\Observers;

use App\Models\Movement;
use App\Models\Stock;
use App\Models\Causal;
use App\Models\CustomerOrderHasProduct;
use App\Models\ProductionRecord;

class MovementObserver
{
    /**
     * Quando viene creato un movimento, aggiorna (o crea) la riga Stock.
     */
    public function created(Movement $movement): void
    {
        $this->applyMovement($movement, +1);
    }

    /**
     * Quando un movimento viene eliminato, annulla il suo effetto sullo Stock.
     */
    public function deleted(Movement $movement): void
    {
        $this->applyMovement($movement, -1);
    }

    // -------------------------------------------------------------------------

    private function applyMovement(Movement $movement, int $sign): void
    {
        $causal = $movement->causal ?? $movement->load('causal')->causal;

        $delta = $causal->type === Causal::TYPE_LOAD
            ? $movement->qnt * $sign
            : $movement->qnt * $sign * -1;

        // Calcola la chiave di composizione SOLO per i movimenti di carico da produzione
        // (ovvero quando un prodotto finito/semi-lavorato viene messo a magazzino).
        // Gli scarichi di materie prime usano sempre stock generico (composition_key = null).
        $compositionKey  = null;
        $compositionData = null;

        if ($movement->production_record_id && $causal->type === Causal::TYPE_LOAD) {
            $record = $movement->productionRecord
                ?? ProductionRecord::with('customerOrderHasProduct.details.product')->find($movement->production_record_id);

            if ($record && $record->customerOrderHasProduct) {
                $cop = $record->customerOrderHasProduct;
                $cop->loadMissing('details.product');

                $details = $cop->details->sortBy('recipe_id');

                if ($details->isNotEmpty()) {
                    // Chiave: hash degli ingredienti scelti ordinati per recipe_id
                    $keyParts = $details->map(fn($d) => $d->recipe_id . ':' . $d->product_id)->toArray();
                    $compositionKey = substr(md5(implode('|', $keyParts)), 0, 16);

                    // Dati per la visualizzazione
                    $compositionData = $details->map(function ($d) {
                        return [
                            'product_name' => $d->product?->name ?? '?',
                            'product_id'   => $d->product_id,
                        ];
                    })->values()->toArray();
                }
            }
        }

        $stock = Stock::query()->firstOrCreate(
            [
                'warehouse_id'    => $movement->warehouse_id,
                'product_id'      => $movement->product_id,
                'composition_key' => $compositionKey,
            ],
            [
                'qnt'                => 0,
                'unit_of_measure_id' => $movement->unit_of_measure_id,
                'composition_data'   => $compositionData,
            ]
        );

        // Se lo stock esisteva già ma senza composition_data, aggiornalo
        if ($compositionData && empty($stock->composition_data)) {
            $stock->update(['composition_data' => $compositionData]);
        }

        $stock->increment('qnt', $delta);
    }
}
