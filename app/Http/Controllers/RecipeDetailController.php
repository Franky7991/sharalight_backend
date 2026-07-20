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
     * Esclude i prodotti già inseriti nel dettaglio.
     */
    public function listAvailableProducts(Request $request, string $recipeId)
    {
        $recipe = Recipe::query()->findOrFail($recipeId);

        $usedIds = RecipeDetail::query()
            ->where('recipe_id', $recipeId)
            ->pluck('product_id');

        $products = Product::query()
            ->where('product_category_id', $recipe->product_category_id)
            ->whereNotIn('id', $usedIds)
            ->get();

        return datatables($products)
            ->toJson();
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
