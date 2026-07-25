<?php

namespace App\Observers;

use App\Models\CustomerOrderHasProductDetail;
use App\Models\CustomerOrderSummary;
use App\Models\CustomerOrderHasProduct;
use App\Models\Product;

class CustomerOrderHasProductDetailObserver
{
    /**
     * Handle the CustomerOrderHasProductDetail "created" event.
     */
    public function created(CustomerOrderHasProductDetail $detail): void
    {
        $this->recalculateSummary($detail);
    }

    /**
     * Handle the CustomerOrderHasProductDetail "updated" event.
     */
    public function updated(CustomerOrderHasProductDetail $detail): void
    {
        $this->recalculateSummary($detail);
    }

    /**
     * Handle the CustomerOrderHasProductDetail "deleted" event.
     */
    public function deleted(CustomerOrderHasProductDetail $detail): void
    {
        $this->recalculateSummary($detail);
    }

    /**
     * Handle the CustomerOrderHasProductDetail "restored" event.
     */
    public function restored(CustomerOrderHasProductDetail $detail): void
    {
        $this->recalculateSummary($detail);
    }

    /**
     * Handle the CustomerOrderHasProductDetail "force deleted" event.
     */
    public function forceDeleted(CustomerOrderHasProductDetail $detail): void
    {
        $this->recalculateSummary($detail);
    }

    /**
     * Ricalcola il riepilogo per l'ordine
     */
    private function recalculateSummary(CustomerOrderHasProductDetail $detail): void
    {
        $orderProduct = CustomerOrderHasProduct::with('customerOrder')->find($detail->customer_order_has_product_id);
        if (!$orderProduct) return;

        $orderId = $orderProduct->customer_order_id;

        // Elimina i riepiloghi esistenti per questo ordine
        CustomerOrderSummary::where('customer_order_id', $orderId)->delete();

        // Carica tutti i dettagli dell'ordine
        $allDetails = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->with('details.product')
            ->get();

        $summary = [];

        foreach ($allDetails as $orderProduct) {
            foreach ($orderProduct->details as $detail) {
                $product = $detail->product;
                if (!$product) continue;

                // Mostra solo le materie prime
                if ($product->type !== Product::TYPE_RAW_MATERIAL) continue;

                // Usa la quantità convertita se disponibile, altrimenti quella originale
                $qnt = $detail->conversion_qnt ?? $detail->original_qnt;
                $uomId = $detail->conversion_unit_of_measure_id ?? $detail->original_unit_of_measure_id;

                if ($qnt === null || $uomId === null) continue;

                $key = $product->id . '_' . $uomId;

                if (!isset($summary[$key])) {
                    $summary[$key] = [
                        'customer_order_id' => $orderId,
                        'product_id' => $product->id,
                        'unit_of_measure_id' => $uomId,
                        'total_qnt' => 0,
                    ];
                }

                $summary[$key]['total_qnt'] += (float) $qnt;
            }
        }

        // Crea i record nel riepilogo
        foreach ($summary as $item) {
            CustomerOrderSummary::create($item);
        }
    }
}
