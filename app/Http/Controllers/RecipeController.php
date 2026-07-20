<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recipe;

class RecipeController extends Controller
{
    public function listDataTable(Request $request, string $productId)
    {
        $query = Recipe::query()
            ->with(['productCategory', 'unitOfMeasure'])
            ->withCount('details')
            ->where('product_id', $productId)
            ->get();

        return datatables($query)
            ->addColumn('product_category_name', fn($row) => $row->productCategory?->name ?? '-')
            ->addColumn('unit_of_measure_symbol', fn($row) => $row->unitOfMeasure?->symbol ?? '-')
            ->addColumn('unit_of_measure_name',   fn($row) => $row->unitOfMeasure?->name   ?? '-')
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
