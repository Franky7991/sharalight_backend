<?php

namespace App\Services;

use App\Models\UnitConversion;
use Illuminate\Support\Collection;

class UnitConversionService
{
    private Collection $conversions;

    public function __construct()
    {
        $this->conversions = UnitConversion::all();
    }

    /**
     * Converte $quantity dall'UdM $fromId all'UdM $toId.
     *
     * Ordine di ricerca:
     *   1. Inversa: esiste una riga con from=$toId, to=$fromId → usa il reciproco
     *   2. Diretta: esiste una riga con from=$fromId, to=$toId
     *   3. Nessuna trovata → restituisce la quantità invariata (nessun avviso)
     */
    public function convert(float $quantity, int $fromId, int $toId): float
    {
        if ($fromId === $toId) {
            return $quantity;
        }

        // 1. Inversa
        $inverse = $this->conversions->first(
            fn($c) => (int)$c->from_unit_of_measure_id === $toId
                   && (int)$c->to_unit_of_measure_id   === $fromId
        );

        if ($inverse) {
            return $quantity * ((float) $inverse->from_quantity / (float) $inverse->to_quantity);
        }

        // 2. Diretta
        $direct = $this->conversions->first(
            fn($c) => (int)$c->from_unit_of_measure_id === $fromId
                   && (int)$c->to_unit_of_measure_id   === $toId
        );

        if ($direct) {
            return $quantity * ((float) $direct->to_quantity / (float) $direct->from_quantity);
        }

        // 3. Nessuna conversione: restituisce invariato
        return $quantity;
    }
}
