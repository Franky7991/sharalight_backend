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
        $products   = Product::query()
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->with('productCategory.unitOfMeasure')
            ->orderBy('name')
            ->get();

        // Causale default carico (da Impostazioni)
        $defaultLoadCausalId = Setting::get(Setting::KEY_WAREHOUSE_LOAD_CAUSAL);

        return view('movement.index', compact('warehouses', 'products', 'defaultLoadCausalId'));
    }

    public function listDataTable(Request $request)
    {
        $query = Movement::query()
            ->with(['warehouse', 'product', 'causal', 'unitOfMeasure', 'productionRecord.product'])
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
            ->addColumn('production_info', function ($r) {
                $rec = $r->productionRecord;
                if (! $rec) return '<span class="text-muted">—</span>';

                $productName = e($rec->product?->name ?? '-');
                $html = '<strong>' . $productName . '</strong> ' . number_format((float) $rec->qnt, 2, ',', '.') . ' ' . e($rec->unitOfMeasure?->symbol ?? '');

                // Ingredienti: movimenti di scarico nello stesso record (escludendo il prodotto finito stesso)
                $rec->loadMissing('movements.product');
                $ingredients = $rec->movements->filter(fn ($m) => $m->product_id !== $rec->product_id);
                if ($ingredients->isNotEmpty()) {
                    $parts = $ingredients->map(fn ($m) =>
                        e($m->product?->name ?? '?')
                        . ' ' . number_format(abs((float) $m->qnt), 2, ',', '.')
                        . ' ' . e($m->unitOfMeasure?->symbol ?? '')
                    )->toArray();
                    $html .= '<br><small class="text-muted">Ingredienti: ' . implode(', ', $parts) . '</small>';
                }

                return $html;
            })
            ->rawColumns(['causal_type_label', 'production_info'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'       => ['required', 'exists:warehouses,id'],
            'product_id'         => ['required', 'exists:products,id,type,' . Product::TYPE_RAW_MATERIAL],
            'causal_id'          => ['required', 'exists:causals,id'],
            'qnt'                => ['required', 'regex:/^\d{1,3}(\.\d{3})*([,.]\d{1,2})?$|^\d{1,15}([.,]\d{1,2})?$/'],
            'unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
        ]);

        // Converte formato italiano (1.000,00) in float per il DB
        $qnt = $request->qnt;
        // Rimuove i punti delle migliaia, poi converte virgola in punto decimale
        $qnt = str_replace(',', '.', str_replace('.', '', $qnt));

        Movement::query()->create([
            'warehouse_id'       => $request->warehouse_id,
            'product_id'         => $request->product_id,
            'causal_id'          => $request->causal_id,
            'qnt'                => $qnt,
            'unit_of_measure_id' => $request->unit_of_measure_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        abort(403, 'Eliminazione di un movimento non consentita.');
    }
}
