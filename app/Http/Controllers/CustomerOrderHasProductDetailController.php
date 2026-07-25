<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\CustomerOrderHasProductDetail;
use App\Models\Recipe;
use App\Models\Product;
use App\Services\UnitConversionService;

class CustomerOrderHasProductDetailController extends Controller
{
    /**
     * Restituisce le ricette del prodotto associato alla riga ordine,
     * con per ogni ricetta la categoria ingrediente, il totale convertito
     * e i prodotti disponibili per quella categoria.
     *
     * GET /customer-orders/{order}/products/{orderProduct}/details/config
     */
    public function config(string $orderId, string $orderProductId)
    {
        $orderProduct = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->with(['product', 'unitOfMeasure', 'details'])
            ->findOrFail($orderProductId);

        $recipes = Recipe::query()
            ->where('product_id', $orderProduct->product_id)
            ->with(['productCategory.unitOfMeasure', 'unitOfMeasure'])
            ->get();

        $saved       = $orderProduct->details->keyBy('recipe_id');
        $conv        = new UnitConversionService();
        $orderUomId  = (int) $orderProduct->unit_of_measure_id;
        $orderQnt    = (float) $orderProduct->qnt;
        $orderUomSym = $orderProduct->unitOfMeasure?->symbol ?? '';

        $result = $recipes->map(function ($recipe) use ($saved, $conv, $orderUomId, $orderQnt, $orderUomSym, $orderProduct) {
            $category = $recipe->productCategory;
            if (! $category) return null;

            $recipeUomId  = (int) ($recipe->unit_of_measure_id ?? 0);
            $recipeUomSym = $recipe->unitOfMeasure?->symbol ?? '';
            $recipeQnt    = (float) $recipe->quantity;

            $qntConverted = $conv->convert($orderQnt, $orderUomId, $recipeUomId);
            $total        = $recipeQnt * $qntConverted;

            $availableProducts = Product::query()
                ->where('product_category_id', $category->id)
                ->where('id', '!=', $orderProduct->product_id)
                ->with('productCategory.unitOfMeasure')
                ->orderBy('name')
                ->get();

            $selectedProduct = $availableProducts->firstWhere('id', $saved->get($recipe->id)?->product_id);
            $categoryUomId = $selectedProduct?->productCategory?->unit_of_measure_id ?? $category->unit_of_measure_id;
            $categoryUomSym = $selectedProduct?->productCategory?->unitOfMeasure?->symbol ?? $category->unitOfMeasure?->symbol ?? '';

            // Converti il totale necessario dall'unità della ricetta all'unità della categoria
            $convertedTotal = $conv->convert($total, $recipeUomId, $categoryUomId);

            // Se non c'è conversione necessaria (stessa unità), i campi conversion sono null
            $needsConversion = ($recipeUomId !== $categoryUomId) ? true : false;

            // Carica ricette annidate se il prodotto selezionato è semi-lavorato
            $nestedRecipes = [];
            if ($selectedProduct && $selectedProduct->hasRecipe()) {
                $nestedRecipes = $this->loadNestedRecipes($selectedProduct->id, $total, $recipeUomId, $conv, $saved, 0, [$orderProduct->product_id], $orderProduct);
            }

            return [
                'recipe_id'                    => $recipe->id,
                'category_id'                  => $category->id,
                'category_name'                => $category->name,
                'recipe_uom_symbol'            => $recipeUomSym,
                'total'                        => round($total, 4),
                'category_uom_symbol'          => $categoryUomSym,
                'converted_total'              => round($convertedTotal, 4),
                'original_qnt'                 => round($total, 4),
                'original_unit_of_measure_id'  => $recipeUomId,
                'conversion_qnt'               => $needsConversion ? round($convertedTotal, 4) : null,
                'conversion_unit_of_measure_id'=> $needsConversion ? $categoryUomId : null,
                'products'                     => $availableProducts->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'type' => $p->type]),
                'selected_product_id'          => $saved->get($recipe->id)?->product_id,
                'nested_recipes'               => $nestedRecipes,
            ];
        })->filter()->values();

        return response()->json([
            'order_product_id' => $orderProduct->id,
            'product_name'     => $orderProduct->product?->name ?? '',
            'order_qnt'        => $orderQnt,
            'order_uom_symbol' => $orderUomSym,
            'rows'             => $result,
        ]);
    }

    /**
     * Carica ricorsivamente le ricette per prodotti semi-lavorati
     */
    private function loadNestedRecipes($productId, $parentQnt, $parentUomId, $conv, $saved, $depth = 0, $excludeProductIds = [], $orderProduct = null)
    {
        if ($depth > 5) return []; // Limite di profondità per evitare loop infiniti

        $recipes = Recipe::query()
            ->where('product_id', $productId)
            ->with(['productCategory.unitOfMeasure', 'unitOfMeasure'])
            ->get();

        return $recipes->map(function ($recipe) use ($parentQnt, $parentUomId, $conv, $saved, $depth, $excludeProductIds, $orderProduct) {
            $category = $recipe->productCategory;
            if (! $category) return null;

            $recipeUomId  = (int) ($recipe->unit_of_measure_id ?? 0);
            $recipeUomSym = $recipe->unitOfMeasure?->symbol ?? '';
            $recipeQnt    = (float) $recipe->quantity;

            $qntConverted = $conv->convert($parentQnt, $parentUomId, $recipeUomId);
            $total        = $recipeQnt * $qntConverted;

            $availableProducts = Product::query()
                ->where('product_category_id', $category->id)
                ->whereNotIn('id', $excludeProductIds);

            // Escludi anche il prodotto principale se disponibile
            if ($orderProduct) {
                $availableProducts->where('id', '!=', $orderProduct->product_id);
            }

            $availableProducts = $availableProducts
                ->with('productCategory.unitOfMeasure')
                ->orderBy('name')
                ->get();

            $selectedProduct = $availableProducts->firstWhere('id', $saved->get($recipe->id)?->product_id);
            $categoryUomId = $selectedProduct?->productCategory?->unit_of_measure_id ?? $category->unit_of_measure_id;
            $categoryUomSym = $selectedProduct?->productCategory?->unitOfMeasure?->symbol ?? $category->unitOfMeasure?->symbol ?? '';

            $convertedTotal = $conv->convert($total, $recipeUomId, $categoryUomId);
            $needsConversion = ($recipeUomId !== $categoryUomId) ? true : false;

            // Ricorsione per prodotti semi-lavorati annidati
            $nestedRecipes = [];
            if ($selectedProduct && $selectedProduct->hasRecipe()) {
                $newExcludeIds = array_merge($excludeProductIds, [$selectedProduct->id]);
                $nestedRecipes = $this->loadNestedRecipes($selectedProduct->id, $total, $recipeUomId, $conv, $saved, $depth + 1, $newExcludeIds, $orderProduct);
            }

            return [
                'recipe_id'                    => $recipe->id,
                'category_id'                  => $category->id,
                'category_name'                => $category->name,
                'recipe_uom_symbol'            => $recipeUomSym,
                'total'                        => round($total, 4),
                'category_uom_symbol'          => $categoryUomSym,
                'converted_total'              => round($convertedTotal, 4),
                'original_qnt'                 => round($total, 4),
                'original_unit_of_measure_id'  => $recipeUomId,
                'conversion_qnt'               => $needsConversion ? round($convertedTotal, 4) : null,
                'conversion_unit_of_measure_id'=> $needsConversion ? $categoryUomId : null,
                'products'                     => $availableProducts->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'type' => $p->type]),
                'selected_product_id'          => $saved->get($recipe->id)?->product_id,
                'nested_recipes'               => $nestedRecipes,
                'depth'                        => $depth + 1,
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Salva (upsert) la scelta dei prodotti per ogni ricetta della riga ordine.
     *
     * POST /customer-orders/{order}/products/{orderProduct}/details
     * body: { selections: [ { recipe_id: X, product_id: Y }, ... ] }
     */
    public function save(Request $request, string $orderId, string $orderProductId)
    {
        $order = CustomerOrder::query()->findOrFail($orderId);

        if ($order->isProductsDefined()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile modificare gli ingredienti: l\'ordine è nello stato "Prodotti Definiti".',
            ], 403);
        }

        $orderProduct = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->findOrFail($orderProductId);

        $request->validate([
            'selections'                          => ['required', 'array'],
            'selections.*.recipe_id'             => ['required', 'exists:recipes,id'],
            'selections.*.product_id'           => ['required', 'exists:products,id'],
            'selections.*.original_qnt'          => ['nullable', 'numeric'],
            'selections.*.original_unit_of_measure_id' => ['nullable', 'exists:unit_of_measures,id'],
            'selections.*.conversion_qnt'         => ['nullable', 'numeric'],
            'selections.*.conversion_unit_of_measure_id' => ['nullable', 'exists:unit_of_measures,id'],
        ]);

        foreach ($request->selections as $sel) {
            CustomerOrderHasProductDetail::query()->updateOrCreate(
                [
                    'customer_order_has_product_id' => $orderProduct->id,
                    'recipe_id'                     => $sel['recipe_id'],
                ],
                [
                    'product_id'                    => $sel['product_id'],
                    'original_qnt'                  => $sel['original_qnt'] ?? null,
                    'original_unit_of_measure_id'   => $sel['original_unit_of_measure_id'] ?? null,
                    'conversion_qnt'                => $sel['conversion_qnt'] ?? null,
                    'conversion_unit_of_measure_id' => $sel['conversion_unit_of_measure_id'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}
