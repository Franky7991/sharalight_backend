<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    public function index()
    {
        return view('product.index');
    }

    public function listDataTable(Request $request)
    {
        $query = Product::query()->with('productCategory')->get();
        return datatables($query)
            ->addColumn('product_category_name', fn($row) => $row->productCategory?->name ?? '-')
            ->addColumn('finished_product_label', fn($row) => $row->finished_product
                ? '<span class="badge badge-success">Sì</span>'
                : '<span class="badge badge-secondary">No</span>')
            ->rawColumns(['finished_product_label'])
            ->toJson();
    }

    public function create()
    {
        $productCategories = ProductCategory::query()->orderBy('name')->get();
        return view('product.create', compact('productCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'finished_product'    => ['nullable', 'boolean'],
        ]);

        Product::query()->create([
            'name'                => $request->name,
            'product_category_id' => $request->product_category_id,
            'finished_product'    => $request->boolean('finished_product'),
        ]);

        return redirect(route('products.index'));
    }

    public function show(string $id)
    {
        $product           = Product::query()->findOrFail($id);
        $productCategories = ProductCategory::query()->orderBy('name')->get();
        return view('product.show', compact('product', 'productCategories'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::query()->findOrFail($id);

        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'finished_product'    => ['nullable', 'boolean'],
        ]);

        $product->update([
            'name'                => $request->name,
            'product_category_id' => $request->product_category_id,
            'finished_product'    => $request->boolean('finished_product'),
        ]);

        return redirect(route('products.index'));
    }

    public function destroy(string $id)
    {
        Product::query()->findOrFail($id)->delete();
        return redirect(route('products.index'));
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $item = Product::find($id);
            if ($item === null) {
                continue;
            }
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
