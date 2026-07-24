<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use App\Models\Recipe;

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
            ->addColumn('has_recipe', fn($row) => $row->hasRecipe())
            ->rawColumns(['type_label'])
            ->toJson();
    }

    /**
     * Restituisce il grafo a rete delle categorie annidate per tutte le ricette di un prodotto.
     * Ogni occorrenza di categoria è un nodo distinto (anche se stessa categoria ripetuta in rami diversi).
     */
    public function tree(string $productId)
    {
        $product = Product::query()->findOrFail($productId);

        if (!$product->hasRecipe()) {
            return response()->json(['message' => 'Questo prodotto non ha ricette.'], 404);
        }

        $nodes  = [];
        $edges  = [];
        $nodeSeq = 0;  // contatore per ID unici

        // Nodo radice = il prodotto stesso
        $rootId           = 'n0';
        $nodes[$rootId]   = [
            'id'    => $rootId,
            'label' => $product->name,
            'level' => 0,
            'root'  => true,
        ];

        $recipes = Recipe::query()
            ->with(['productCategory', 'unitOfMeasure', 'details.product'])
            ->where('product_id', $productId)
            ->get();

        foreach ($recipes as $recipe) {
            $this->buildCategoryGraph($recipe, $rootId, 1, $nodes, $edges, $nodeSeq, []);
        }

        return response()->json([
            'product_name' => $product->name,
            'nodes'        => array_values($nodes),
            'edges'        => array_values($edges),
        ]);
    }

    private function buildCategoryGraph(
        Recipe $recipe,
        string $parentNodeId,
        int    $level,
        array  &$nodes,
        array  &$edges,
        int    &$nodeSeq,
        array  $visited
    ): void {
        $nodeSeq++;
        $nodeId   = 'n' . $nodeSeq;
        $qty      = (float) $recipe->quantity;
        $uom      = $recipe->unitOfMeasure?->symbol ?? '';
        $catLabel = $recipe->productCategory?->name ?? 'Sconosciuta';

        $nodes[$nodeId] = [
            'id'    => $nodeId,
            'label' => $catLabel,
            'level' => $level,
            'root'  => false,
        ];

        $edges[] = [
            'from'  => $parentNodeId,
            'to'    => $nodeId,
            'label' => number_format($qty, 2, ',', '.') . ($uom ? ' ' . $uom : ''),
        ];

        // Scendi nei prodotti del dettaglio che hanno ricette proprie
        foreach ($recipe->details as $detail) {
            $subProduct = $detail->product;
            if (!$subProduct || !$subProduct->hasRecipe()) continue;
            if (in_array($subProduct->id, $visited)) continue;  // guard cicli

            $subRecipes = Recipe::query()
                ->with(['productCategory', 'unitOfMeasure', 'details.product'])
                ->where('product_id', $subProduct->id)
                ->get();

            foreach ($subRecipes as $subRecipe) {
                $this->buildCategoryGraph(
                    $subRecipe,
                    $nodeId,
                    $level + 1,
                    $nodes,
                    $edges,
                    $nodeSeq,
                    array_merge($visited, [$subProduct->id])
                );
            }
        }
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
        $unitOfMeasures    = UnitOfMeasure::query()->orderBy('name')->get();
        return view('product.show', compact('product', 'productCategories', 'productTypes', 'unitOfMeasures'));
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
