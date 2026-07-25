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
        $query = CustomerOrder::query()->with('user')->get();

        return datatables($query)
            ->addColumn('user_name', fn($r) => $r->user?->name ?? '-')
            ->addColumn('order_date_fmt', fn($r) => $r->order_date?->format('d/m/Y') ?? '-')
            ->addColumn('state_label', function ($r) {
                return '<span class="badge badge-secondary">' . $r->stateLabel() . '</span>';
            })
            ->rawColumns(['state_label'])
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
        CustomerOrder::query()->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            CustomerOrder::find($id)?->delete();
        }
        return response()->json(['success' => true]);
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
