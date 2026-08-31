<?php

use App\Http\Controllers\Api\AuthController;
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
});


