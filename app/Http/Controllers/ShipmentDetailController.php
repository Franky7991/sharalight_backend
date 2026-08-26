<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\Shipment;
use App\Models\ShipmentDetail;

class ShipmentDetailController extends Controller
{
    public function listDataTable(Request $request, string $shipmentId)
    {
        $rows = ShipmentDetail::query()
            ->where('shipment_id', $shipmentId)
            ->with(['customerOrder'])
            ->get();

        return datatables($rows)
            ->addColumn('progressive', fn ($r) => $r->customerOrder?->progressive ?? '-')
            ->addColumn('order_date_fmt', fn ($r) => $r->customerOrder?->order_date?->format('d/m/Y') ?? '-')
            ->addColumn('address', function ($r) {
                return $r->customerOrder ? e($r->customerOrder->address) : '-';
            })
            ->rawColumns(['address'])
            ->toJson();
    }

    public function store(Request $request, string $shipmentId)
    {
        $shipment = Shipment::query()->findOrFail($shipmentId);

        if (! $shipment->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile aggiungere ordini: la spedizione non è nello stato "Creato".',
            ], 403);
        }

        $request->validate([
            'customer_order_id' => ['required', 'exists:customer_orders,id'],
        ]);

        // Solo ordini interamente prodotti (progressbar al 100%).
        $order = CustomerOrder::query()
            ->where('id', $request->customer_order_id)
            ->withSum('products', 'qnt_produced')
            ->first();

        if (! $order || ! $order->isFullyProduced()) {
            return response()->json([
                'success' => false,
                'message' => 'È possibile aggiungere solo ordini clienti interamente prodotti (progressbar al 100%).',
            ], 422);
        }

        $already = ShipmentDetail::query()
            ->where('shipment_id', $shipmentId)
            ->where('customer_order_id', $order->id)
            ->exists();

        if ($already) {
            return response()->json([
                'success' => false,
                'message' => 'Questo ordine cliente è già presente nella spedizione.',
            ], 422);
        }

        ShipmentDetail::query()->create([
            'shipment_id'        => $shipmentId,
            'customer_order_id'  => $order->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $shipmentId, string $id)
    {
        $shipment = Shipment::query()->findOrFail($shipmentId);

        if (! $shipment->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile rimuovere ordini: la spedizione non è nello stato "Creato".',
            ], 403);
        }

        ShipmentDetail::query()
            ->where('shipment_id', $shipmentId)
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Elenca tutti i prodotti degli ordini clienti inseriti nella spedizione.
     *
     * POST /shipments/{shipment}/products/list/table
     */
    public function listProductsDataTable(Request $request, string $shipmentId)
    {
        $rows = CustomerOrderHasProduct::query()
            ->whereHas('customerOrder', fn ($q) => $q->whereHas(
                'shipmentDetails',
                fn ($s) => $s->where('shipment_id', $shipmentId)
            ))
            ->with([
                'customerOrder',
                'product',
                'unitOfMeasure',
                'details.recipe.productCategory',
                'details.recipe.unitOfMeasure',
                'details.product',
            ])
            ->orderBy('id')
            ->get();

        return datatables($rows)
            ->addColumn('order_progressive', fn ($r) => $r->customerOrder?->progressive ?? '-')
            ->addColumn('product_name', function ($r) {
                return $r->product ? e($r->product->name) : '-';
            })
            ->addColumn('composition_html', function ($r) {
                if ($r->details->isEmpty()) {
                    return '<span class="text-muted small">—</span>';
                }

                $parts = $r->details->map(function ($d) {
                    $cat  = $d->recipe?->productCategory?->name ?? '?';
                    $prod = $d->product?->name ?? '?';

                    // Se c'è conversione salvata, usa quella, altrimenti la quantità originale
                    if ($d->conversion_qnt !== null && $d->conversion_unit_of_measure_id !== null) {
                        $qnt   = $d->conversion_qnt;
                        $uomId = $d->conversion_unit_of_measure_id;
                    } else {
                        $qnt   = $d->original_qnt;
                        $uomId = $d->original_unit_of_measure_id;
                    }

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
            ->addColumn('qnt_fmt', fn ($r) => number_format((float) $r->qnt, 2, ',', '.'))
            ->addColumn('unit_of_measure_symbol', fn ($r) => $r->unitOfMeasure?->symbol ?? '-')
            ->addColumn('qnt_produced_fmt', fn ($r) => number_format((float) $r->qnt_produced, 2, ',', '.'))
            ->rawColumns(['product_name', 'composition_html'])
            ->toJson();
    }
}