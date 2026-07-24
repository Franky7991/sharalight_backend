<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movement;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Causal;
use App\Models\Setting;

class MovementController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::query()->orderBy('name')->get();
        $products   = Product::query()->with('productCategory.unitOfMeasure')->orderBy('name')->get();

        // Causale default carico (da Impostazioni)
        $defaultLoadCausalId = Setting::get(Setting::KEY_WAREHOUSE_LOAD_CAUSAL);

        return view('movement.index', compact('warehouses', 'products', 'defaultLoadCausalId'));
    }

    public function listDataTable(Request $request)
    {
        $query = Movement::query()
            ->with(['warehouse', 'product', 'causal', 'unitOfMeasure'])
            ->get();

        return datatables($query)
            ->addColumn('warehouse_name',        fn($r) => $r->warehouse?->name        ?? '-')
            ->addColumn('product_name',          fn($r) => $r->product?->name          ?? '-')
            ->addColumn('causal_name',           fn($r) => $r->causal?->name           ?? '-')
            ->addColumn('unit_of_measure_symbol',fn($r) => $r->unitOfMeasure?->symbol  ?? '-')
            ->addColumn('causal_type_label', function ($r) {
                if (! $r->causal) return '-';
                $color = $r->causal->type === Causal::TYPE_LOAD ? 'success' : 'danger';
                return '<span class="badge badge-' . $color . '">' . $r->causal->typeLabel() . '</span>';
            })
            ->rawColumns(['causal_type_label'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'       => ['required', 'exists:warehouses,id'],
            'product_id'         => ['required', 'exists:products,id'],
            'causal_id'          => ['required', 'exists:causals,id'],
            'qnt'                => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
            'unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
        ]);

        Movement::query()->create([
            'warehouse_id'       => $request->warehouse_id,
            'product_id'         => $request->product_id,
            'causal_id'          => $request->causal_id,
            'qnt'                => str_replace(',', '.', str_replace('.', '', $request->qnt)),
            'unit_of_measure_id' => $request->unit_of_measure_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        abort(403, 'Eliminazione di un movimento non consentita.');
    }
}
