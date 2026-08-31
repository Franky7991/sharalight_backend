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

    // CustomerOrder routes
    Route::post('/customer-orders/list/table', [App\Http\Controllers\CustomerOrderController::class, 'listDataTable'])->name('customer-orders.datatable');
    Route::post('/customer-orders/delete',     [App\Http\Controllers\CustomerOrderController::class, 'delete'])->name('customer-orders.delete');
    Route::post('/customer-orders',            [App\Http\Controllers\CustomerOrderController::class, 'store'])->name('customer-orders.store');
    Route::put('/customer-orders/{order}',     [App\Http\Controllers\CustomerOrderController::class, 'update'])->name('customer-orders.update');
    Route::put('/customer-orders/{order}/state',[App\Http\Controllers\CustomerOrderController::class, 'changeState'])->name('customer-orders.change-state');
    Route::delete('/customer-orders/{order}',  [App\Http\Controllers\CustomerOrderController::class, 'destroy'])->name('customer-orders.destroy');
    Route::get('/customer-orders/{order}',     [App\Http\Controllers\CustomerOrderController::class, 'show'])->name('customer-orders.show');
    Route::get('/customer-orders/{order}/summary', [App\Http\Controllers\CustomerOrderController::class, 'summary'])->name('customer-orders.summary');
    Route::get('/customer-orders',             [App\Http\Controllers\CustomerOrderController::class, 'index'])->name('customer-orders.index');

    // CustomerOrderHasProduct routes
    Route::post('/customer-orders/{order}/products/list/table', [App\Http\Controllers\CustomerOrderHasProductController::class, 'listDataTable'])->name('customer-order-products.datatable');
    Route::post('/customer-orders/{order}/products',            [App\Http\Controllers\CustomerOrderHasProductController::class, 'store'])->name('customer-order-products.store');
    Route::delete('/customer-orders/{order}/products/{product}',[App\Http\Controllers\CustomerOrderHasProductController::class, 'destroy'])->name('customer-order-products.destroy');
    Route::get('/customer-orders/{order}/products/{product}/warehouses', [App\Http\Controllers\CustomerOrderHasProductController::class, 'warehouseConfig'])->name('customer-order-products.warehouses.config');
    Route::post('/customer-orders/{order}/products/{product}/warehouses', [App\Http\Controllers\CustomerOrderHasProductController::class, 'saveWarehouses'])->name('customer-order-products.warehouses.save');

    // CustomerOrderHasProductDetail routes
    Route::get('/customer-orders/{order}/products/{orderProduct}/details/config', [App\Http\Controllers\CustomerOrderHasProductDetailController::class, 'config'])->name('customer-order-product-details.config');
    Route::post('/customer-orders/{order}/products/{orderProduct}/details',       [App\Http\Controllers\CustomerOrderHasProductDetailController::class, 'save'])->name('customer-order-product-details.save');

    // ProductionOrder routes
    Route::post('/production-orders/list/table', [App\Http\Controllers\ProductionOrderController::class, 'listDataTable'])->name('production-orders.datatable');
    Route::post('/production-orders/delete',       [App\Http\Controllers\ProductionOrderController::class, 'delete'])->name('production-orders.delete');
    Route::post('/production-orders',              [App\Http\Controllers\ProductionOrderController::class, 'store'])->name('production-orders.store');
    Route::put('/production-orders/{order}',        [App\Http\Controllers\ProductionOrderController::class, 'update'])->name('production-orders.update');
    Route::put('/production-orders/{order}/state',  [App\Http\Controllers\ProductionOrderController::class, 'changeState'])->name('production-orders.change-state');
    Route::post('/production-orders/{order}/produce', [App\Http\Controllers\ProductionOrderController::class, 'produce'])->name('production-orders.produce');
    Route::get('/production-orders/{order}/products/{detail}/requirements', [App\Http\Controllers\ProductionOrderController::class, 'requirements'])->name('production-orders.requirements');
    Route::delete('/production-orders/{order}',     [App\Http\Controllers\ProductionOrderController::class, 'destroy'])->name('production-orders.destroy');
    Route::get('/production-orders/{order}',       [App\Http\Controllers\ProductionOrderController::class, 'show'])->name('production-orders.show');
    Route::get('/production-orders',               [App\Http\Controllers\ProductionOrderController::class, 'index'])->name('production-orders.index');

    // ProductionOrderDetail routes
    Route::post('/production-orders/{order}/details/list/table', [App\Http\Controllers\ProductionOrderDetailController::class, 'listDataTable'])->name('production-order-details.datatable');
    Route::post('/production-orders/{order}/details',            [App\Http\Controllers\ProductionOrderDetailController::class, 'store'])->name('production-order-details.store');
    Route::delete('/production-orders/{order}/details/{detail}', [App\Http\Controllers\ProductionOrderDetailController::class, 'destroy'])->name('production-order-details.destroy');

    // Shipment routes
    Route::post('/shipments/list/table',              [App\Http\Controllers\ShipmentController::class, 'listDataTable'])->name('shipments.datatable');
    Route::post('/shipments/delete',                  [App\Http\Controllers\ShipmentController::class, 'delete'])->name('shipments.delete');
    Route::post('/shipments',                         [App\Http\Controllers\ShipmentController::class, 'store'])->name('shipments.store');
    Route::put('/shipments/{shipment}',               [App\Http\Controllers\ShipmentController::class, 'update'])->name('shipments.update');
    Route::put('/shipments/{shipment}/state',         [App\Http\Controllers\ShipmentController::class, 'changeState'])->name('shipments.change-state');
    Route::delete('/shipments/{shipment}',            [App\Http\Controllers\ShipmentController::class, 'destroy'])->name('shipments.destroy');
    Route::get('/shipments/{shipment}',               [App\Http\Controllers\ShipmentController::class, 'show'])->name('shipments.show');
    Route::get('/shipments',                          [App\Http\Controllers\ShipmentController::class, 'index'])->name('shipments.index');

    // ShipmentDetail routes
    Route::post('/shipments/{shipment}/orders/list/table', [App\Http\Controllers\ShipmentDetailController::class, 'listDataTable'])->name('shipment-details.datatable');
    Route::post('/shipments/{shipment}/products/list/table', [App\Http\Controllers\ShipmentDetailController::class, 'listProductsDataTable'])->name('shipment-products.datatable');
    Route::post('/shipments/{shipment}/orders',            [App\Http\Controllers\ShipmentDetailController::class, 'store'])->name('shipment-details.store');
    Route::delete('/shipments/{shipment}/orders/{detail}', [App\Http\Controllers\ShipmentDetailController::class, 'destroy'])->name('shipment-details.destroy');

    // Stock routes
    Route::post('/stocks/list/table', [App\Http\Controllers\StockController::class, 'listDataTable'])->name('stocks.datatable');
    Route::get('/stocks', [App\Http\Controllers\StockController::class, 'index'])->name('stocks.index');

    // Movement routes
    Route::post('/movements/list/table', [App\Http\Controllers\MovementController::class, 'listDataTable'])->name('movements.datatable');
    Route::post('/movements', [App\Http\Controllers\MovementController::class, 'store'])->name('movements.store');
    Route::delete('/movements/{movement}', [App\Http\Controllers\MovementController::class, 'destroy'])->name('movements.destroy');
    Route::get('/movements', [App\Http\Controllers\MovementController::class, 'index'])->name('movements.index');

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Le rotte custom vanno PRIMA di resource per evitare conflitti
    Route::post('/products/list/table', [App\Http\Controllers\ProductController::class, 'listDataTable'])->name('products.datatable');
    Route::post('/products/delete', [App\Http\Controllers\ProductController::class, 'delete'])->name('products.delete');
    Route::get('/products/{product}/tree', [App\Http\Controllers\ProductController::class, 'tree'])->name('products.tree');
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

    // UnitConversion routes
    Route::post('/unit-conversions/list/table', [App\Http\Controllers\UnitConversionController::class, 'listDataTable'])->name('unit-conversions.datatable');
    Route::post('/unit-conversions/delete', [App\Http\Controllers\UnitConversionController::class, 'delete'])->name('unit-conversions.delete');
    Route::resource('unit-conversions', App\Http\Controllers\UnitConversionController::class);

});
