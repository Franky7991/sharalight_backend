<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\RecipeDetail;
use App\Models\Product;

class RecipeDetailController extends Controller
{
    /**
     * DataTable dei dettagli già inseriti per una recipe.
     */
    public function listDetails(Request $request, string $recipeId)
    {
        $details = RecipeDetail::query()
            ->with('product')
            ->where('recipe_id', $recipeId)
            ->get();

        return datatables($details)
            ->addColumn('product_name', fn($row) => $row->product?->name ?? '-')
            ->toJson();
    }

    /**
     * DataTable dei prodotti disponibili per la categoria della recipe.
     * Esclude i prodotti già inseriti nel dettaglio e il prodotto principale della ricetta.
     */
    public function listAvailableProducts(Request $request, string $recipeId)
    {
        $recipe = Recipe::query()->with('product')->findOrFail($recipeId);

        $usedIds = RecipeDetail::query()
            ->where('recipe_id', $recipeId)
            ->pluck('product_id')
            ->toArray();

        // Escludi anche il prodotto principale (quello per cui si sta costruendo la ricetta)
        $excludeIds = array_merge($usedIds, [$recipe->product_id]);

        $products = Product::query()
            ->where('product_category_id', $recipe->product_category_id)
            ->whereNotIn('id', $excludeIds)
            ->orderBy('name')
            ->get();

        // Debug: log per verificare il risultato
        \Log::info('RecipeDetailController::listAvailableProducts', [
            'recipe_id'           => $recipeId,
            'product_id'          => $recipe->product_id,
            'product_category_id' => $recipe->product_category_id,
            'used_ids'            => $usedIds,
            'exclude_ids'         => $excludeIds,
            'found_products'      => $products->pluck('name', 'id')->toArray(),
            'total_found'         => $products->count(),
        ]);

        // Se il parametro ?debug=1 è presente, restituisci JSON semplice per debug
        if ($request->get('debug')) {
            return response()->json([
                'recipe'          => $recipe->only(['id', 'product_id', 'product_category_id']),
                'recipe_product'  => $recipe->product->only(['id', 'name', 'product_category_id']),
                'exclude_ids'     => $excludeIds,
                'found_products'  => $products->map(fn($p) => [
                    'id'   => $p->id,
                    'name' => $p->name,
                    'type' => $p->type,
                    'product_category_id' => $p->product_category_id,
                ]),
            ]);
        }

        return datatables($products)->toJson();
    }

    /**
     * Inserisce un prodotto nel dettaglio ricetta.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipe_id'  => ['required', 'exists:recipes,id'],
            'product_id' => ['required', 'exists:products,id'],
        ]);

        // Verifica che il prodotto appartenga alla categoria della recipe
        $recipe  = Recipe::query()->findOrFail($request->recipe_id);
        $product = Product::query()->findOrFail($request->product_id);

        if ($product->product_category_id !== $recipe->product_category_id) {
            return response()->json([
                'message' => 'Il prodotto non appartiene alla categoria della ricetta.',
            ], 422);
        }

        RecipeDetail::query()->firstOrCreate([
            'recipe_id'  => $request->recipe_id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Elimina un dettaglio ricetta.
     */
    public function destroy(string $id)
    {
        RecipeDetail::query()->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
