<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\CustomerOrderHasProductWarehouse;
use App\Models\Warehouse;
use App\Services\UnitConversionService;

class CustomerOrderHasProductController extends Controller
{
    public function listDataTable(Request $request, string $orderId)
    {
        $rows = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->with([
                'product',
                'unitOfMeasure',
                'details.recipe.productCategory',
                'details.recipe.unitOfMeasure',
                'details.product',
            ])
            ->get();

        $conv = new UnitConversionService();
        $warehouses = Warehouse::query()->orderBy('name')->get();

        return datatables($rows)
            ->addColumn('product_name',          fn($r) => $r->product?->name          ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($r) => $r->unitOfMeasure?->symbol ?? '-')
            ->addColumn('warehouses_allocated',  fn($r) => $r->warehouses_allocated ? 1 : 0)
            ->addColumn('warehouses_html', function ($r) use ($warehouses) {
                $allocations = $r->warehouses->keyBy('warehouse_id');
                if ($allocations->isEmpty()) {
                    return '<span class="text-muted small">—</span>';
                }

                $uomSymbol = $r->unitOfMeasure?->symbol ?? '';
                $parts = [];
                foreach ($warehouses as $w) {
                    $a = $allocations->get($w->id);
                    if ($a && (float) $a->qnt > 0) {
                        $parts[] = '<span class="badge badge-light border mr-1">'
                                 . e($w->name) . ': <strong>' . number_format((float) $a->qnt, 2, ',', '.') . '</strong>'
                                 . ($uomSymbol ? ' ' . e($uomSymbol) : '')
                                 . '</span>';
                    }
                }

                return $parts ? implode(' ', $parts) : '<span class="text-muted small">—</span>';
            })
            ->addColumn('details_html', function ($r) use ($conv) {
                if ($r->details->isEmpty()) {
                    return '<span class="text-muted small">—</span>';
                }

                $parts = $r->details->map(function ($d) {
                    $cat          = $d->recipe?->productCategory?->name ?? '?';
                    $prod         = $d->product?->name ?? '?';

                    // Se c'è conversione salvata, usa quella, altrimenti usa la quantità originale
                    if ($d->conversion_qnt !== null && $d->conversion_unit_of_measure_id !== null) {
                        $qnt   = $d->conversion_qnt;
                        $uomId = $d->conversion_unit_of_measure_id;
                    } else {
                        $qnt   = $d->original_qnt;
                        $uomId = $d->original_unit_of_measure_id;
                    }

                    // Ottieni il simbolo dell'unità di misura
                    $uomSym = '';
                    if ($uomId) {
                        $uom = \App\Models\UnitOfMeasure::find($uomId);
                        $uomSym = $uom?->symbol ?? '';
                    }

                    $qntStr = '<span class="text-muted">'
                            . number_format($qnt, 2, ',', '.')
                            . ($uomSym ? ' ' . e($uomSym) : '')
                            . '</span>';

                    return '<span class="badge badge-light border mr-1">'
                         . '<strong>' . e($cat) . ':</strong> '
                         . e($prod) . ' ' . $qntStr
                         . '</span>';
                });

                return $parts->implode(' ');
            })
            ->rawColumns(['details_html', 'warehouses_html'])
            ->toJson();
    }

    public function store(Request $request, string $orderId)
    {
        $order = CustomerOrder::query()->findOrFail($orderId);

        if ($order->isProductsDefined()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile aggiungere prodotti: l\'ordine è nello stato "Prodotti Definiti".',
            ], 403);
        }

        $request->validate([
            'product_id'         => ['required', 'exists:products,id'],
            'qnt'                => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
            'unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
        ]);

        CustomerOrderHasProduct::query()->create([
            'customer_order_id'  => $orderId,
            'product_id'         => $request->product_id,
            'qnt'                => str_replace(',', '.', $request->qnt),
            'unit_of_measure_id' => $request->unit_of_measure_id,
        ]);

        $this->recalculateOrderQnt($order);

        return response()->json(['success' => true]);
    }

    public function destroy(string $orderId, string $id)
    {
        $order = CustomerOrder::query()->findOrFail($orderId);

        if ($order->isProductsDefined()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile rimuovere prodotti: l\'ordine è nello stato "Prodotti Definiti".',
            ], 403);
        }

        $row = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->findOrFail($id);

        $row->delete();

        $this->recalculateOrderQnt($order);

        return response()->json(['success' => true]);
    }

    /**
     * Restituisce la configurazione per l'allocazione a magazzino:
     * elenco di tutti i magazzini con le quantità già allocate per questo prodotto.
     *
     * GET /customer-orders/{order}/products/{product}/warehouses
     */
    public function warehouseConfig(string $orderId, string $productId)
    {
        $orderProduct = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->with('unitOfMeasure')
            ->findOrFail($productId);

        $warehouses = Warehouse::query()->orderBy('name')->get();
        $allocated = CustomerOrderHasProductWarehouse::query()
            ->where('customer_order_has_product_id', $productId)
            ->get()
            ->keyBy('warehouse_id');

        $rows = $warehouses->map(function ($w) use ($allocated) {
            $a = $allocated->get($w->id);
            return [
                'warehouse_id'   => $w->id,
                'warehouse_name' => $w->name,
                'qnt'            => $a ? (float) $a->qnt : 0,
            ];
        });

        return response()->json([
            'product_name'     => $orderProduct->product?->name ?? '',
            'order_qnt'        => (float) $orderProduct->qnt,
            'uom_symbol'       => $orderProduct->unitOfMeasure?->symbol ?? '',
            'rows'             => $rows,
        ]);
    }

    /**
     * Salva l'allocazione a magazzino per il prodotto della riga ordine.
     *
     * POST /customer-orders/{order}/products/{product}/warehouses
     * body: { allocations: [ { warehouse_id: X, qnt: Y }, ... ] }
     */
    public function saveWarehouses(Request $request, string $orderId, string $productId)
    {
        $orderProduct = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->findOrFail($productId);

        $request->validate([
            'allocations'                => ['required', 'array'],
            'allocations.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'allocations.*.qnt'          => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($request->allocations as $alloc) {
            CustomerOrderHasProductWarehouse::query()->updateOrCreate(
                [
                    'customer_order_has_product_id' => $orderProduct->id,
                    'warehouse_id'                  => $alloc['warehouse_id'],
                ],
                [
                    'qnt' => $alloc['qnt'],
                ]
            );
        }

        $orderProduct->update(['warehouses_allocated' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Ricalcola la quantità totale dell'ordine sommando le qnt di tutti i prodotti.
     */
    private function recalculateOrderQnt(CustomerOrder $order): void
    {
        $total = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $order->id)
            ->sum('qnt');

        $order->update(['qnt' => $total]);
    }
}
