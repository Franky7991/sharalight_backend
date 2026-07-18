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
    Route::post('/users/list/table', [App\Http\Controllers\UserController::class, 'listDataTable'])->name('users.datatable');
    Route::post('/users/delete', [App\Http\Controllers\UserController::class, 'delete'])->name('users.delete');

    // User CRUD
    Route::resource('users', App\Http\Controllers\UserController::class);

});
