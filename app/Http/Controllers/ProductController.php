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
            ->addColumn('type_label', function ($row) {
                $colors = [
                    Product::TYPE_RAW_MATERIAL  => 'secondary',
                    Product::TYPE_SEMI_FINISHED  => 'warning',
                    Product::TYPE_FINISHED       => 'success',
                ];
                $color = $colors[$row->type] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . $row->typeLabel() . '</span>';
            })
            ->rawColumns(['type_label'])
            ->toJson();
    }

    public function create()
    {
        $productCategories = ProductCategory::query()->orderBy('name')->get();
        $productTypes      = Product::TYPES;
        return view('product.create', compact('productCategories', 'productTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'type'                => ['required', 'in:' . implode(',', array_keys(Product::TYPES))],
        ]);

        Product::query()->create([
            'name'                => $request->name,
            'product_category_id' => $request->product_category_id,
            'type'                => $request->type,
        ]);

        return redirect(route('products.index'));
    }

    public function show(string $id)
    {
        $product           = Product::query()->findOrFail($id);
        $productCategories = ProductCategory::query()->with('unitOfMeasure')->orderBy('name')->get();
        $productTypes      = Product::TYPES;
        return view('product.show', compact('product', 'productCategories', 'productTypes'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::query()->findOrFail($id);

        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'type'                => ['required', 'in:' . implode(',', array_keys(Product::TYPES))],
        ]);

        $product->update([
            'name'                => $request->name,
            'product_category_id' => $request->product_category_id,
            'type'                => $request->type,
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
