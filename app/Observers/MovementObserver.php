<?php

namespace App\Observers;

use App\Models\Movement;
use App\Models\Stock;
use App\Models\Causal;

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
        // Carica la causale con il suo tipo (load / unload)
        $causal = $movement->causal ?? $movement->load('causal')->causal;

        // Determina il segno effettivo: carico → +qnt, scarico → -qnt
        // Il parametro $sign permette di invertire tutto in caso di eliminazione
        $delta = $causal->type === Causal::TYPE_LOAD
            ? $movement->qnt * $sign
            : $movement->qnt * $sign * -1;

        // updateOrCreate: se non esiste la riga Stock la crea con qnt = delta
        // Se esiste, incrementa/decrementa
        $stock = Stock::query()->firstOrCreate(
            [
                'warehouse_id' => $movement->warehouse_id,
                'product_id'   => $movement->product_id,
            ],
            [
                'qnt'                => 0,
                'unit_of_measure_id' => $movement->unit_of_measure_id,
            ]
        );

        $stock->increment('qnt', $delta);
    }
}
