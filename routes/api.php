<?php

use App\Http\Controllers\AspelSync\AspelClientSyncController;
use App\Http\Controllers\AspelSync\AspelSyncController;
use App\Http\Controllers\AspelSync\CotizacionMonedaSyncController;
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
// Route::match(['GET', 'POST'], '/aspel/sync', [AspelSyncController::class, 'sync']);

// Requiere `Authorization: Bearer {token}` con un token activo (ver
// App\Http\Middleware\AspelApiTokenMiddleware / módulo "Integración").
Route::middleware('aspel.token')->group(function () {
    Route::post('/aspel/sync', [AspelSyncController::class, 'sync']);
    Route::post('/aspel/precio-x-producto', [PrecioXProductoController::class, 'precioXProducto']);
    Route::post('/aspel/clientes', [AspelClientSyncController::class, 'sync']);
    Route::post('/aspel/tipo-cambio', [CotizacionMonedaSyncController::class, 'sync']);
});
