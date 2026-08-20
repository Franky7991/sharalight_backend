<?php

namespace App\Services;

use App\Models\CustomerOrderHasProduct;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\ProductionRecord;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\UnitOfMeasure;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Restituisce il piano di produzione:
     * - prodotti da produrre con quantità ordinata / prodotta / rimanente;
     * - materie prime necessarie (per la parte ancora da produrre) con giacenza.
     */
    public function plan(ProductionOrder $order): array
    {
        $order->load([
            'details.customerOrderHasProduct.product',
            'details.customerOrderHasProduct.unitOfMeasure',
            'details.customerOrderHasProduct.details.product',
        ]);

        $warehouseId = (int) $order->warehouse_id;
        $uomSymbols  = UnitOfMeasure::query()->pluck('symbol', 'id');

        $products  = [];
        $materials = [];

        foreach ($order->details as $detail) {
            $cop = $detail->customerOrderHasProduct;
            if (! $cop) {
                continue;
            }

            $ordered   = (float) $cop->qnt;
            $produced  = (float) ($cop->qnt_produced ?? 0);
            $remaining = max(0, $ordered - $produced);

            $rawMaterials = $this->rawMaterials($cop);

            $products[] = [
                'id'           => $detail->id,
                'product_name' => $cop->product?->name ?? '-',
                'qnt'          => $ordered,
                'qnt_produced' => $produced,
                'remaining'    => $remaining,
                'uom_symbol'   => $cop->unitOfMeasure?->symbol ?? '-',
                'ingredients'  => $this->ingredients($cop),
            ];

            if ($remaining <= 0) {
                continue;
            }

            $ratio = $ordered > 0 ? $remaining / $ordered : 0;

            foreach ($rawMaterials as $m) {
                $need = $m['qnt'] * $ratio;
                if ($need <= 0) {
                    continue;
                }

                $key = $m['product_id'] . ':' . $m['unit_of_measure_id'];

                if (! isset($materials[$key])) {
                    $materials[$key] = [
                        'product_id'         => $m['product_id'],
                        'product_name'       => $m['product_name'],
                        'unit_of_measure_id' => $m['unit_of_measure_id'],
                        'uom_symbol'         => $uomSymbols[$m['unit_of_measure_id']] ?? '-',
                        'required_qnt'       => 0.0,
                    ];
                }

                $materials[$key]['required_qnt'] += $need;
            }
        }

        // Giacenza attuale per ogni materia prima nel magazzino di produzione.
        $materials = array_map(function (array $m) use ($warehouseId) {
            $required = round($m['required_qnt'], 2);

            $stock = Stock::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $m['product_id'])
                ->where('unit_of_measure_id', $m['unit_of_measure_id'])
                ->first();

            $available = $stock ? (float) $stock->qnt : 0.0;
            $missing   = round(max(0, $required - $available), 2);

            $m['required_qnt']  = $required;
            $m['available_qnt'] = $available;
            $m['missing_qnt']   = $missing;
            $m['is_missing']    = $missing > 0;

            return $m;
        }, $materials);

        usort($materials, fn ($a, $b) => strcmp($a['product_name'], $b['product_name']));

        $missingCount = count(array_filter($materials, fn ($m) => $m['is_missing']));

        return [
            'products'      => array_values($products),
            'materials'     => array_values($materials),
            'missing_count' => $missingCount,
        ];
    }

    /**
     * Calcola il fabbisogno di materie prime per una quantità specifica
     * e verifica la giacenza disponibile nel magazzino dell'ordine.
     */
    public function requirements(ProductionOrder $order, ProductionOrderDetail $detail, float $qnt): array
    {
        $detail->load('customerOrderHasProduct.details.product');
        $cop = $detail->customerOrderHasProduct;

        $warehouseId = (int) $order->warehouse_id;
        $uomSymbols  = UnitOfMeasure::query()->pluck('symbol', 'id');

        $materials = [];

        if ($cop) {
            $ordered   = (float) $cop->qnt;
            $produced  = (float) ($cop->qnt_produced ?? 0);
            $remaining = max(0, $ordered - $produced);

            $qnt   = max(0, min($qnt, $remaining));
            $ratio = $ordered > 0 ? $qnt / $ordered : 0;

            foreach ($this->rawMaterials($cop) as $m) {
                $need = round($m['qnt'] * $ratio, 2);
                if ($need <= 0) {
                    continue;
                }

                $stock = Stock::query()
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $m['product_id'])
                    ->where('unit_of_measure_id', $m['unit_of_measure_id'])
                    ->first();

                $available = $stock ? (float) $stock->qnt : 0.0;
                $missing   = round(max(0, $need - $available), 2);

                $materials[] = [
                    'product_id'         => $m['product_id'],
                    'product_name'       => $m['product_name'],
                    'unit_of_measure_id' => $m['unit_of_measure_id'],
                    'uom_symbol'         => $uomSymbols[$m['unit_of_measure_id']] ?? '-',
                    'required_qnt'       => $need,
                    'available_qnt'      => $available,
                    'missing_qnt'        => $missing,
                    'is_missing'         => $missing > 0,
                ];
            }
        }

        usort($materials, fn ($a, $b) => strcmp($a['product_name'], $b['product_name']));

        $missingCount = count(array_filter($materials, fn ($m) => $m['is_missing']));

        return [
            'sufficient'    => $qnt > 0 && $missingCount === 0,
            'missing_count' => $missingCount,
            'materials'     => array_values($materials),
        ];
    }

    /**
     * Registra la produzione di una quantità per una specifica riga:
     * scarica le materie prime (proporzionalmente), carica il prodotto finito,
     * registra la produzione e aggiorna qnt_produced.
     */
    public function produce(ProductionOrder $order, ProductionOrderDetail $detail, float $qnt): void
    {
        if (! $order->isInProcessing()) {
            throw new \RuntimeException('La produzione è consentita solo per ordini nello stato "In Lavorazione".');
        }

        $unloadCausalId = Setting::get(Setting::KEY_PRODUCTION_UNLOAD_CAUSAL);
        $loadCausalId   = Setting::get(Setting::KEY_PRODUCTION_LOAD_CAUSAL);

        if (! $unloadCausalId || ! $loadCausalId) {
            throw new \RuntimeException('Causali di produzione non configurate. Impostale in Impostazioni.');
        }

        $detail->load('customerOrderHasProduct.details.product');
        $cop = $detail->customerOrderHasProduct;

        if (! $cop) {
            throw new \RuntimeException('Riga di produzione non trovata.');
        }

        $ordered   = (float) $cop->qnt;
        $remaining = max(0, $ordered - (float) ($cop->qnt_produced ?? 0));

        if ($qnt <= 0) {
            throw new \RuntimeException('La quantità da produrre deve essere maggiore di zero.');
        }

        if ($qnt > $remaining + 0.0001) {
            throw new \RuntimeException('La quantità da produrre supera la quantità rimanente di questa riga.');
        }

        $req = $this->requirements($order, $detail, $qnt);

        if (! $req['sufficient']) {
            $missing = array_column(array_filter($req['materials'], fn ($m) => $m['is_missing']), 'product_name');
            throw new \RuntimeException('Materie prime insufficienti a magazzino: ' . implode(', ', $missing) . '.');
        }

        DB::transaction(function () use ($order, $detail, $cop, $qnt, $req, $unloadCausalId, $loadCausalId) {
            // 1. Scarico materie prime (proporzionale alla quantità prodotta)
            foreach ($req['materials'] as $m) {
                Movement::query()->create([
                    'warehouse_id'       => $order->warehouse_id,
                    'product_id'         => $m['product_id'],
                    'causal_id'          => $unloadCausalId,
                    'qnt'                => $m['required_qnt'],
                    'unit_of_measure_id' => $m['unit_of_measure_id'],
                ]);
            }

            // 2. Carico prodotto finito
            Movement::query()->create([
                'warehouse_id'       => $order->warehouse_id,
                'product_id'         => $cop->product_id,
                'causal_id'          => $loadCausalId,
                'qnt'                => $qnt,
                'unit_of_measure_id' => $cop->unit_of_measure_id,
            ]);

            // 3. Registro della produzione
            ProductionRecord::query()->create([
                'production_order_id'           => $order->id,
                'production_order_detail_id'    => $detail->id,
                'customer_order_has_product_id' => $cop->id,
                'product_id'                    => $cop->product_id,
                'qnt'                           => $qnt,
                'unit_of_measure_id'            => $cop->unit_of_measure_id,
            ]);

            // 4. Aggiorna la quantità prodotta
            CustomerOrderHasProduct::query()->where('id', $cop->id)->increment('qnt_produced', $qnt);

            // 5. Completa l'ordine se tutte le righe sono interamente prodotte
            $this->completeIfDone($order);
        });
    }

    /**
     * Elenco materie prime necessarie (per l'intera quantità ordinata della riga).
     */
    private function rawMaterials(CustomerOrderHasProduct $cop): array
    {
        $materials = [];

        foreach ($cop->details as $d) {
            // Solo materie prime (stessa logica del Riepilogo ordine cliente).
            if (! $d->product || $d->product->type !== Product::TYPE_RAW_MATERIAL) {
                continue;
            }

            $qnt   = (float) ($d->conversion_qnt ?? $d->original_qnt ?? 0);
            $uomId = (int) ($d->conversion_unit_of_measure_id ?? $d->original_unit_of_measure_id ?? 0);

            if ($qnt <= 0 || $uomId <= 0) {
                continue;
            }

            $productId = (int) $d->product_id;
            $key       = $productId . ':' . $uomId;

            if (! isset($materials[$key])) {
                $materials[$key] = [
                    'product_id'         => $productId,
                    'product_name'       => $d->product->name,
                    'unit_of_measure_id' => $uomId,
                    'qnt'                => 0.0,
                ];
            }

            $materials[$key]['qnt'] += $qnt;
        }

        return array_values($materials);
    }

    /**
     * Elenco ingredienti (materie prime) di una riga ordine cliente,
     * con nome, quantità e unità di misura.
     */
    public function ingredients(CustomerOrderHasProduct $cop): array
    {
        $uomSymbols = UnitOfMeasure::query()->pluck('symbol', 'id');

        return array_map(function (array $m) use ($uomSymbols) {
            return [
                'product_name' => $m['product_name'],
                'qnt'          => round($m['qnt'], 2),
                'uom_symbol'   => $uomSymbols[$m['unit_of_measure_id']] ?? '-',
            ];
        }, $this->rawMaterials($cop));
    }

    /**
     * Porta l'ordine in "Completato" quando tutte le righe sono interamente prodotte.
     */
    private function completeIfDone(ProductionOrder $order): void
    {
        $order->load('details.customerOrderHasProduct');

        foreach ($order->details as $d) {
            $cop = $d->customerOrderHasProduct;
            if (! $cop) {
                continue;
            }

            if ((float) $cop->qnt_produced < (float) $cop->qnt) {
                return;
            }
        }

        $order->update(['state' => ProductionOrder::STATE_COMPLETED]);
    }
}