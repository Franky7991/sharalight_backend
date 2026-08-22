<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Recipe;

class StockController extends Controller
{
    public function index()
    {
        return view('stock.index');
    }

    public function listDataTable(Request $request)
    {
        // Precarica tutte le ricette con i loro dettagli per evitare N+1 query
        $recipes = Recipe::query()
            ->with(['details.product:id,name'])
            ->get()
            ->keyBy('product_id');

        $query = Stock::query()
            ->with(['warehouse', 'product', 'unitOfMeasure'])
            ->get();

        return datatables($query)
            ->addColumn('warehouse_name',         fn($r) => $r->warehouse?->name        ?? '-')
            ->addColumn('product_name',           fn($r) => $r->product?->name          ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($r) => $r->unitOfMeasure?->symbol  ?? '-')
            ->addColumn('ingredients', function ($r) use ($recipes) {
                $productId = $r->product_id;

                // Usa la ricetta precaricata
                $recipe = $recipes[$productId] ?? null;
                if (! $recipe || $recipe->details->isEmpty()) {
                    return '<span class="text-muted">—</span>';
                }

                $parts = $recipe->details->map(function ($d) {
                    return e($d->product?->name ?? '?');
                })->toArray();

                return '<small class="text-muted">' . implode(', ', $parts) . '</small>';
            })
            ->rawColumns(['ingredients'])
            ->toJson();
    }
}
