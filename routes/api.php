<?php

use App\Http\Controllers\AspelSync\AspelSyncController;
use App\Http\Controllers\AspelSync\PrecioXProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them willa
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/aspel/sync', [AspelSyncController::class, 'syncData']);
Route::post('/aspel/sync', [AspelSyncController::class, 'sync']);
Route::post('/aspel/precio-x-producto', [PrecioXProductoController::class, 'precioXProducto']);
// Route::match(['GET', 'POST'], '/aspel/sync', [AspelSyncController::class, 'sync']);
