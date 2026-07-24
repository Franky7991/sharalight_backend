<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\UnitConversion;

class RecipeController extends Controller
{
    public function listDataTable(Request $request, string $productId)
    {
        $query = Recipe::query()
            ->with(['productCategory', 'unitOfMeasure'])
            ->withCount('details')
            ->where('product_id', $productId)
            ->get();

        // Carica tutte le conversioni indicizzate per from_unit_of_measure_id
        $conversions = UnitConversion::query()
            ->with(['fromUnitOfMeasure', 'toUnitOfMeasure'])
            ->get()
            ->groupBy('from_unit_of_measure_id');

        return datatables($query)
            ->addColumn('product_category_name', fn($row) => $row->productCategory?->name ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($row) => $row->unitOfMeasure?->symbol ?? '-')
            ->addColumn('unit_of_measure_name',   fn($row) => $row->unitOfMeasure?->name   ?? '-')
            ->addColumn('conversion_label', function ($row) use ($conversions) {
                if (! $row->unit_of_measure_id) return '';
                $matches = $conversions->get($row->unit_of_measure_id);
                if (! $matches || $matches->isEmpty()) return '';

                $recipeQty = (float) $row->quantity;

                return $matches->map(function ($c) use ($recipeQty) {
                    // Proporziona la conversione alla quantità della riga ricetta
                    $factor    = $c->from_quantity > 0 ? ($c->to_quantity / $c->from_quantity) : 0;
                    $converted = $recipeQty * $factor;

                    $fromQty = number_format($recipeQty, 2, ',', '.');
                    $toQty   = number_format($converted,  2, ',', '.');
                    $fromSym = $c->fromUnitOfMeasure?->symbol ?? '?';
                    $toSym   = $c->toUnitOfMeasure?->symbol   ?? '?';
                    $toName  = $c->toUnitOfMeasure?->name     ?? '';

                    return $fromQty . ' ' . $fromSym . ' → ' . $toQty . ' ' . $toSym
                         . ($toName ? ' <small class="text-muted">(' . $toName . ')</small>' : '');
                })->implode('<br>');
            })
            ->rawColumns(['conversion_label'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'          => ['required', 'exists:products,id'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'unit_of_measure_id'  => ['required', 'exists:unit_of_measures,id'],
            'quantity'            => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
        ]);

        Recipe::query()->create([
            'product_id'          => $request->product_id,
            'product_category_id' => $request->product_category_id,
            'unit_of_measure_id'  => $request->unit_of_measure_id,
            'quantity'            => str_replace(',', '.', str_replace('.', '', $request->quantity)),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $recipe = Recipe::query()->findOrFail($id);

        $request->validate([
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'unit_of_measure_id'  => ['required', 'exists:unit_of_measures,id'],
            'quantity'            => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
        ]);

        $recipe->update([
            'product_category_id' => $request->product_category_id,
            'unit_of_measure_id'  => $request->unit_of_measure_id,
            'quantity'            => str_replace(',', '.', str_replace('.', '', $request->quantity)),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        Recipe::query()->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
