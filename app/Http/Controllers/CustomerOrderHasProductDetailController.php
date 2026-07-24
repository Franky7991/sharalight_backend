<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        $result = $recipes->map(function ($recipe) use ($saved, $conv, $orderUomId, $orderQnt, $orderUomSym) {
            $category = $recipe->productCategory;
            if (! $category) return null;

            $recipeUomId  = (int) ($recipe->unit_of_measure_id ?? 0);
            $recipeUomSym = $recipe->unitOfMeasure?->symbol ?? '';
            $recipeQnt    = (float) $recipe->quantity;

            $qntConverted = $conv->convert($orderQnt, $orderUomId, $recipeUomId);
            $total        = $recipeQnt * $qntConverted;

            $availableProducts = Product::query()
                ->where('product_category_id', $category->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return [
                'recipe_id'           => $recipe->id,
                'category_id'         => $category->id,
                'category_name'       => $category->name,
                'recipe_uom_symbol'   => $recipeUomSym,
                'total'               => round($total, 4),
                'products'            => $availableProducts->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                'selected_product_id' => $saved->get($recipe->id)?->product_id,
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
     * Salva (upsert) la scelta dei prodotti per ogni ricetta della riga ordine.
     *
     * POST /customer-orders/{order}/products/{orderProduct}/details
     * body: { selections: [ { recipe_id: X, product_id: Y }, ... ] }
     */
    public function save(Request $request, string $orderId, string $orderProductId)
    {
        $orderProduct = CustomerOrderHasProduct::query()
            ->where('customer_order_id', $orderId)
            ->findOrFail($orderProductId);

        $request->validate([
            'selections'               => ['required', 'array'],
            'selections.*.recipe_id'  => ['required', 'exists:recipes,id'],
            'selections.*.product_id' => ['required', 'exists:products,id'],
        ]);

        foreach ($request->selections as $sel) {
            CustomerOrderHasProductDetail::query()->updateOrCreate(
                [
                    'customer_order_has_product_id' => $orderProduct->id,
                    'recipe_id'                     => $sel['recipe_id'],
                ],
                [
                    'product_id' => $sel['product_id'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}
