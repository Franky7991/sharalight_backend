<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeocodeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (webapp)
|--------------------------------------------------------------------------
|
| Endpoints di autenticazione consumati dalla webapp (cartella /webapp).
| Autenticazione via token (Laravel Sanctum) nell'header:
|   Authorization: Bearer <token>
|
*/

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login',    [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',    [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Modulo Ordini cliente
    Route::get('/orders',          [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/catalog',  [OrderController::class, 'catalog'])->name('api.orders.catalog');
    Route::post('/orders',         [OrderController::class, 'store'])->name('api.orders.store');

    // Autocomplete indirizzi (il provider esterno è contattato solo dal backend)
    Route::get('/geocode', [GeocodeController::class, 'search'])->name('api.geocode.search');

    // Pagamenti PayPal
    Route::prefix('payments')->group(function () {
        Route::post('/create', [PaymentController::class, 'create'])->name('api.payments.create');
        Route::post('/capture', [PaymentController::class, 'capture'])->name('api.payments.capture');
        Route::post('/paypal-details', [PaymentController::class, 'getPayPalOrderDetails'])->name('api.payments.paypal-details');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('api.payments.show');
    });
});


