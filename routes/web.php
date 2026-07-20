<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (login/logout only - no registration)
Auth::routes([
    'register' => false,
    'reset'    => false,
    'verify'   => false,
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {

    // Le rotte custom vanno PRIMA di resource per evitare conflitti
    Route::post('/products/list/table', [App\Http\Controllers\ProductController::class, 'listDataTable'])->name('products.datatable');
    Route::post('/products/delete', [App\Http\Controllers\ProductController::class, 'delete'])->name('products.delete');
    Route::resource('products', App\Http\Controllers\ProductController::class);

    Route::post('/product-categories/list/table', [App\Http\Controllers\ProductCategoryController::class, 'listDataTable'])->name('product-categories.datatable');
    Route::post('/product-categories/delete', [App\Http\Controllers\ProductCategoryController::class, 'delete'])->name('product-categories.delete');
    Route::resource('product-categories', App\Http\Controllers\ProductCategoryController::class);

    Route::post('/unit-of-measures/list/table', [App\Http\Controllers\UnitOfMeasureController::class, 'listDataTable'])->name('unit-of-measures.datatable');
    Route::post('/unit-of-measures/delete', [App\Http\Controllers\UnitOfMeasureController::class, 'delete'])->name('unit-of-measures.delete');
    Route::resource('unit-of-measures', App\Http\Controllers\UnitOfMeasureController::class);

    Route::post('/users/list/table', [App\Http\Controllers\UserController::class, 'listDataTable'])->name('users.datatable');
    Route::post('/users/delete', [App\Http\Controllers\UserController::class, 'delete'])->name('users.delete');

    // User CRUD
    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::post('/warehouses/list/table', [App\Http\Controllers\WarehouseController::class, 'listDataTable'])->name('warehouses.datatable');
    Route::post('/warehouses/delete', [App\Http\Controllers\WarehouseController::class, 'delete'])->name('warehouses.delete');
    Route::resource('warehouses', App\Http\Controllers\WarehouseController::class);

    // Recipe routes
    Route::post('/recipes/list/table/{product}', [App\Http\Controllers\RecipeController::class, 'listDataTable'])->name('recipes.datatable');
    Route::post('/recipes', [App\Http\Controllers\RecipeController::class, 'store'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [App\Http\Controllers\RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [App\Http\Controllers\RecipeController::class, 'destroy'])->name('recipes.destroy');

    // Recipe detail routes
    Route::post('/recipe-details/list/details/{recipe}', [App\Http\Controllers\RecipeDetailController::class, 'listDetails'])->name('recipe-details.datatable');
    Route::post('/recipe-details/list/products/{recipe}', [App\Http\Controllers\RecipeDetailController::class, 'listAvailableProducts'])->name('recipe-details.products');
    Route::post('/recipe-details', [App\Http\Controllers\RecipeDetailController::class, 'store'])->name('recipe-details.store');
    Route::delete('/recipe-details/{recipeDetail}', [App\Http\Controllers\RecipeDetailController::class, 'destroy'])->name('recipe-details.destroy');

    // Causal routes
    Route::post('/causals/list/table', [App\Http\Controllers\CausalController::class, 'listDataTable'])->name('causals.datatable');
    Route::post('/causals/delete', [App\Http\Controllers\CausalController::class, 'delete'])->name('causals.delete');
    Route::resource('causals', App\Http\Controllers\CausalController::class);

});
