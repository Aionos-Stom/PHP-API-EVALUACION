<?php

declare(strict_types=1);

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    // aqui se comprueba la aplicacion y la tabla de la aplciacion
    \App\Models\Product::query()->limit(1)->get(['id']);

    return response()->json(['status' => 'ok']);
});
Route::pattern('product', '[1-9][0-9]{0,9}');

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::get('/products/{product}/prices', [ProductPriceController::class, 'index']);
Route::post('/products/{product}/prices', [ProductPriceController::class, 'store']);
