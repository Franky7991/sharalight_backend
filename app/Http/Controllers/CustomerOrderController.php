<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\UnitOfMeasure;

class CustomerOrderController extends Controller
{
    public function index()
    {
        return view('customer_order.index');
    }

    public function show(string $id)
    {
        $order          = CustomerOrder::query()->with('user')->findOrFail($id);
        $products       = Product::query()
            ->whereIn('type', Product::TYPES_WITH_RECIPE)
            ->with('productCategory.unitOfMeasure')
            ->orderBy('name')
            ->get();
        $unitOfMeasures = UnitOfMeasure::query()->orderBy('name')->get();

        return view('customer_order.show', compact('order', 'products', 'unitOfMeasures'));
    }

    public function listDataTable(Request $request)
    {
        $query = CustomerOrder::query()
            ->with('user')
            ->withSum('products', 'qnt_produced')
            ->get();

        return datatables($query)
            ->addColumn('user_name', fn($r) => $r->user?->name ?? '-')
            ->addColumn('order_date_fmt', fn($r) => $r->order_date?->format('d/m/Y') ?? '-')
            ->addColumn('state_label', function ($r) {
                return '<span class="badge badge-secondary">' . $r->stateLabel() . '</span>';
            })
            ->addColumn('progress_pct', function ($r) {
                $qnt      = (float) $r->qnt;
                $produced = (float) ($r->products_sum_qnt_produced ?? 0);

                return $qnt > 0 ? round($produced / $qnt * 100, 1) : 0.0;
            })
            ->addColumn('progress_bar_html', function ($r) {
                $qnt      = (float) $r->qnt;
                $produced = (float) ($r->products_sum_qnt_produced ?? 0);
                $pct      = $qnt > 0 ? min(100, (int) round($produced / $qnt * 100)) : 0;

                if ($qnt <= 0) {
                    return '<span class="text-muted small">0%</span>';
                }

                $class = $pct >= 100 ? 'bg-success' : ($pct > 0 ? 'bg-info progress-bar-striped' : 'bg-secondary');

                return '<div class="progress" style="height:18px;">'
                     . '<div class="progress-bar ' . $class . '" role="progressbar"'
                     . ' style="width:' . max($pct, 15) . '%; min-width:2em;"'
                     . ' aria-valuenow="' . $pct . '" aria-valuemin="0" aria-valuemax="100">'
                     . $pct . '%'
                     . '</div></div>';
            })
            ->rawColumns(['state_label', 'progress_bar_html'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'address'    => ['required', 'string', 'max:500'],
            'order_date' => ['required', 'date', 'after:today'],
        ]);

        CustomerOrder::query()->create([
            'progressive' => CustomerOrder::generateProgressive(),
            'address'     => $request->address,
            'user_id'     => auth()->id(),
            'order_date'  => $request->order_date,
            'state'       => CustomerOrder::STATE_CREATED,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $order = CustomerOrder::query()->findOrFail($id);

        $request->validate([
            'address'    => ['required', 'string', 'max:500'],
            'order_date' => ['required', 'date', 'after:today'],
        ]);

        $order->update([
            'address'    => $request->address,
            'order_date' => $request->order_date,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $order = CustomerOrder::query()->findOrFail($id);

        if (! $order->canBeModified()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile eliminare l\'ordine in questo stato.',
            ], 403);
        }

        $order->delete();
        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $deleted = 0;
        $blocked = 0;

        foreach ($request->ids as $id) {
            $order = CustomerOrder::find($id);
            if (! $order) continue;

            if (! $order->canBeModified()) {
                $blocked++;
                continue;
            }

            $order->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'blocked' => $blocked,
        ]);
    }

    /**
     * Cambia lo stato dell'ordine.
     *
     * PUT /customer-orders/{order}/state
     * body: { state: "products_defined" }
     */
    public function changeState(Request $request, string $id)
    {
        $order = CustomerOrder::query()->findOrFail($id);

        $request->validate([
            'state' => ['required', 'string', 'in:' . implode(',', array_keys(CustomerOrder::STATES))],
        ]);

        $newState = $request->input('state');

        // Passaggio da "created" a "products_defined"
        if ($order->state === CustomerOrder::STATE_CREATED
            && $newState === CustomerOrder::STATE_PRODUCTS_DEFINED) {
            $order->update(['state' => $newState]);
            return response()->json([
                'success'     => true,
                'state'       => $order->state,
                'state_label' => $order->stateLabel(),
            ]);
        }

        // Passaggio da "products_defined" a "products_allocated"
        if ($order->state === CustomerOrder::STATE_PRODUCTS_DEFINED
            && $newState === CustomerOrder::STATE_PRODUCTS_ALLOCATED) {
            if (! $order->areAllProductsAllocated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non tutti i prodotti sono allocati ai magazzini.',
                ], 400);
            }
            $order->update(['state' => $newState]);
            return response()->json([
                'success'     => true,
                'state'       => $order->state,
                'state_label' => $order->stateLabel(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transizione di stato non valida.',
        ], 422);
    }

    /**
     * Calcola il riepilogo delle quantità degli ingredienti per l'ordine
     *
     * GET /customer-orders/{order}/summary
     */
    public function summary(string $id)
    {
        $warehouses = \App\Models\Warehouse::orderBy('name')->get();

        $summaries = \App\Models\CustomerOrderSummary::query()
            ->where('customer_order_id', $id)
            ->with(['product', 'unitOfMeasure'])
            ->get()
            ->map(function ($summary) use ($warehouses) {
                $warehouseStocks = [];

                foreach ($warehouses as $warehouse) {
                    $stock = \App\Models\Stock::query()
                        ->where('warehouse_id', $warehouse->id)
                        ->where('product_id', $summary->product_id)
                        ->where('unit_of_measure_id', $summary->unit_of_measure_id)
                        ->first();

                    $warehouseStocks[$warehouse->id] = [
                        'warehouse_name' => $warehouse->name,
                        'qnt' => $stock ? (float) $stock->qnt : 0,
                        'is_negative' => $stock && (float) $stock->qnt < 0,
                    ];
                }

                return [
                    'product_id' => $summary->product_id,
                    'product_name' => $summary->product?->name ?? '',
                    'unit_of_measure_id' => $summary->unit_of_measure_id,
                    'unit_of_measure_symbol' => $summary->unitOfMeasure?->symbol ?? '',
                    'total_qnt' => (float) $summary->total_qnt,
                    'warehouse_stocks' => $warehouseStocks,
                ];
            })
            ->sortBy('product_name')
            ->values();

        return response()->json([
            'summaries' => $summaries,
            'warehouses' => $warehouses,
        ]);
    }
}

