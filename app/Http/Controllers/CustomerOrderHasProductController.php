<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
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

        return datatables($rows)
            ->addColumn('product_name',          fn($r) => $r->product?->name          ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($r) => $r->unitOfMeasure?->symbol ?? '-')
            ->addColumn('details_html', function ($r) use ($conv) {
                if ($r->details->isEmpty()) {
                    return '<span class="text-muted small">—</span>';
                }

                // UdM dell'ordine (quella del prodotto / categoria)
                $orderUomId = (int) $r->unit_of_measure_id;
                $orderQnt   = (float) $r->qnt;

                $parts = $r->details->map(function ($d) use ($conv, $orderQnt, $orderUomId) {
                    $cat          = $d->recipe?->productCategory?->name ?? '?';
                    $prod         = $d->product?->name ?? '?';
                    $recipeUomId  = (int) ($d->recipe?->unit_of_measure_id ?? 0);
                    $recipeUomSym = $d->recipe?->unitOfMeasure?->symbol ?? '';
                    $recipeQnt    = (float) ($d->recipe?->quantity ?? 0);

                    $total    = $recipeQnt * $conv->convert($orderQnt, $orderUomId, $recipeUomId);
                    $totalStr = '<span class="text-muted">'
                              . number_format($total, 2, ',', '.')
                              . ($recipeUomSym ? ' ' . e($recipeUomSym) : '')
                              . '</span>';

                    return '<span class="badge badge-light border mr-1">'
                         . '<strong>' . e($cat) . ':</strong> '
                         . e($prod) . ' ' . $totalStr
                         . '</span>';
                });

                return $parts->implode(' ');
            })
            ->rawColumns(['details_html'])
            ->toJson();
    }

    public function store(Request $request, string $orderId)
    {
        CustomerOrder::query()->findOrFail($orderId);

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

        return response()->json(['success' => true]);
    }

    public function destroy(string $orderId, string $id)
    {
        $row = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->findOrFail($id);

        $row->delete();

        return response()->json(['success' => true]);
    }
}
