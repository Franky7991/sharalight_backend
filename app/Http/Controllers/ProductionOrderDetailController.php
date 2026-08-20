<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Services\ProductionService;

class ProductionOrderDetailController extends Controller
{
    public function listDataTable(Request $request, string $productionOrderId)
    {
        $rows = ProductionOrderDetail::query()
            ->where('production_order_id', $productionOrderId)
            ->with([
                'customerOrderHasProduct.product',
                'customerOrderHasProduct.unitOfMeasure',
                'customerOrderHasProduct.customerOrder',
                'customerOrderHasProduct.details.product',
            ])
            ->get();

        $service = app(ProductionService::class);

        return datatables($rows)
            ->addColumn('product_name', fn ($r) => $r->customerOrderHasProduct?->product?->name ?? '-')
            ->addColumn('ingredients_html', function ($r) use ($service) {
                $cop = $r->customerOrderHasProduct;
                if (! $cop) {
                    return '<span class="text-muted small">—</span>';
                }

                $ingredients = $service->ingredients($cop);
                if (empty($ingredients)) {
                    return '<span class="text-muted small">—</span>';
                }

                $parts = array_map(
                    fn ($i) => e($i['product_name'])
                        . ' <span class="text-muted">(' . number_format($i['qnt'], 2, ',', '.') . ' ' . e($i['uom_symbol']) . ')</span>',
                    $ingredients
                );

                return '<ul class="list-unstyled mb-0 small">'
                    . implode('', array_map(fn ($p) => '<li>' . $p . '</li>', $parts))
                    . '</ul>';
            })
            ->addColumn('customer_order_progressive', fn ($r) => $r->customerOrderHasProduct?->customerOrder?->progressive ?? '-')
            ->addColumn('qnt', fn ($r) => number_format((float) ($r->customerOrderHasProduct?->qnt ?? 0), 2, ',', '.'))
            ->addColumn('uom_symbol', fn ($r) => $r->customerOrderHasProduct?->unitOfMeasure?->symbol ?? '-')
            ->rawColumns(['ingredients_html'])
            ->toJson();
    }

    public function store(Request $request, string $productionOrderId)
    {
        $productionOrder = ProductionOrder::query()->findOrFail($productionOrderId);

        if (! $productionOrder->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile aggiungere righe: l\'ordine di produzione non è nello stato "Creato".',
            ], 403);
        }

        $request->validate([
            'customer_order_has_product_id' => ['required', 'exists:customer_order_has_products,id'],
        ]);

        // Solo le righe appartenenti a ordini cliente nello stato "products_allocated".
        $orderProduct = CustomerOrderHasProduct::query()
            ->where('id', $request->customer_order_has_product_id)
            ->whereHas('customerOrder', fn ($q) => $q->where('state', CustomerOrder::STATE_PRODUCTS_ALLOCATED))
            ->first();

        if (! $orderProduct) {
            return response()->json([
                'success' => false,
                'message' => 'È possibile aggiungere solo prodotti di ordini cliente nello stato "Prodotti Allocati".',
            ], 422);
        }

        $already = ProductionOrderDetail::query()
            ->where('customer_order_has_product_id', $orderProduct->id)
            ->exists();

        if ($already) {
            return response()->json([
                'success' => false,
                'message' => 'Questa riga ordine è già presente in un ordine di produzione.',
            ], 422);
        }

        ProductionOrderDetail::query()->create([
            'production_order_id'           => $productionOrder->id,
            'customer_order_has_product_id' => $orderProduct->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $productionOrderId, string $id)
    {
        $productionOrder = ProductionOrder::query()->findOrFail($productionOrderId);

        if (! $productionOrder->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile rimuovere righe: l\'ordine di produzione non è nello stato "Creato".',
            ], 403);
        }

        ProductionOrderDetail::query()
            ->where('production_order_id', $productionOrderId)
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }
}