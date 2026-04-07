<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

// Routes de l'API de Tarification
Route::post('/orders/simulate', [OrderController::class, 'simulate']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/promo/validate', [OrderController::class, 'validatePromo']);

