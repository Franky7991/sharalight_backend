<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('product_category.index');
    }

    public function listDataTable(Request $request)
    {
        $query = ProductCategory::query()->with('unitOfMeasure')->get();
        return datatables($query)
            ->addColumn('unit_of_measure_name', fn($row) => $row->unitOfMeasure?->name ?? '-')
            ->toJson();
    }

    public function create()
    {
        $unitOfMeasures = UnitOfMeasure::query()->orderBy('name')->get();
        return view('product_category.create', compact('unitOfMeasures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'unit_of_measure_id'  => ['required', 'exists:unit_of_measures,id'],
        ]);

        ProductCategory::query()->create([
            'name'               => $request->name,
            'unit_of_measure_id' => $request->unit_of_measure_id,
        ]);

        return redirect(route('product-categories.index'));
    }

    public function show(string $id)
    {
        $productCategory = ProductCategory::query()->findOrFail($id);
        $unitOfMeasures  = UnitOfMeasure::query()->orderBy('name')->get();
        return view('product_category.show', compact('productCategory', 'unitOfMeasures'));
    }

    public function update(Request $request, string $id)
    {
        $productCategory = ProductCategory::query()->findOrFail($id);

        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
        ]);

        $productCategory->update([
            'name'               => $request->name,
            'unit_of_measure_id' => $request->unit_of_measure_id,
        ]);

        return redirect(route('product-categories.index'));
    }

    public function destroy(string $id)
    {
        ProductCategory::query()->findOrFail($id)->delete();
        return redirect(route('product-categories.index'));
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $item = ProductCategory::find($id);
            if ($item === null) {
                continue;
            }
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
