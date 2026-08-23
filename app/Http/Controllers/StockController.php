<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        return view('stock.index');
    }

    public function listDataTable(Request $request)
    {
        $query = Stock::query()
            ->with(['warehouse', 'product', 'unitOfMeasure'])
            ->get();

        return datatables($query)
            ->addColumn('warehouse_name',         fn($r) => $r->warehouse?->name        ?? '-')
            ->addColumn('product_name',           fn($r) => $r->product?->name          ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($r) => $r->unitOfMeasure?->symbol  ?? '-')
            ->addColumn('ingredients', function ($r) {
                // Usa i dati di composizione memorizzati nello stock
                $data = $r->composition_data;

                if (empty($data)) {
                    return '<span class="text-muted">—</span>';
                }

                $parts = [];
                foreach ($data as $item) {
                    $parts[] = '<strong>' . e($item['product_name'] ?? '?') . '</strong>';
                }

                return '<small>' . implode(', ', $parts) . '</small>';
            })
            ->rawColumns(['ingredients'])
            ->toJson();
    }
}
