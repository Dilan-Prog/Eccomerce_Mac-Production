<?php

use App\Http\Controllers\AspelSync\AspelClientSyncController;
use App\Http\Controllers\AspelSync\AspelSalesSyncController;
use App\Http\Controllers\AspelSync\AspelSyncController;
use App\Http\Controllers\AspelSync\CotizacionMonedaSyncController;
use App\Http\Controllers\AspelSync\PrecioXProductoController;
use App\Http\Controllers\Api\MarketingCampaignController;
use App\Http\Controllers\Api\MarketingDataController;
use App\Http\Controllers\Api\MarketingSequenceController;
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

Route::middleware(['throttle:api', 'auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/aspel/sync', [AspelSyncController::class, 'syncData']);
// Route::match(['GET', 'POST'], '/aspel/sync', [AspelSyncController::class, 'sync']);

// Requiere `Authorization: Bearer {token}` con un token activo (ver
// App\Http\Middleware\AspelApiTokenMiddleware / módulo "Integración").
// throttle:api explicito (60/min) -- mismo limite de siempre, ya no viene
// gratis del grupo 'api' (ver Kernel.php) porque /marketing/* necesita uno
// distinto y mas alto (ver mas abajo).
Route::middleware(['throttle:api', 'aspel.token'])->group(function () {
    Route::post('/aspel/sync', [AspelSyncController::class, 'sync']);
    Route::post('/aspel/precio-x-producto', [PrecioXProductoController::class, 'precioXProducto']);
    Route::post('/aspel/clientes', [AspelClientSyncController::class, 'sync']);
    Route::post('/aspel/tipo-cambio', [CotizacionMonedaSyncController::class, 'sync']);
    Route::post('/aspel/ventas', [AspelSalesSyncController::class, 'sync']);
});

// Datos de clientes/compras para el flujo de n8n de email marketing (ver
// App\Http\Middleware\MarketingApiTokenMiddleware / módulo "Marketing").
// Sistema de tokens aislado de aspel.token — no comparten universo.
// throttle:marketing-api (600/min, ver RouteServiceProvider::boot()) en vez
// de throttle:api (60/min) -- n8n necesita renderizar el correo de cada
// cliente de una campaña en la misma corrida, lo que facilmente pasa de 60
// llamadas.
Route::middleware(['throttle:marketing-api', 'marketing.token'])->group(function () {
    Route::get('/marketing/customers', [MarketingDataController::class, 'customers']);
    Route::get('/marketing/email/{userId}', [MarketingDataController::class, 'email']);

    // Universo SEPARADO del de arriba: clientes fuente Aspel (facturación
    // real FACTF01/PAR_FACTF01), sin requerir cuenta de usuario en el sitio.
    // Ver MarketingDataController::aspelCustomers()/aspelEmail().
    Route::get('/marketing/aspel-customers', [MarketingDataController::class, 'aspelCustomers']);
    Route::get('/marketing/aspel-email/{clave}', [MarketingDataController::class, 'aspelEmail']);

    // Contenido crudo de una plantilla (marcadores {{...}} sin sustituir) —
    // para cuando n8n prefiere decidir el relleno de variables por su
    // cuenta en vez de pedir el correo ya armado por cliente.
    Route::get('/marketing/templates/{id}', [MarketingDataController::class, 'template']);

    // Cupones activos con su alcance (categoria/sub/hija) -- n8n decide cual
    // le toca a cada cliente comparando contra su clasificacion, el mas
    // especifico que haga match gana. Ver MarketingDataController::coupons().
    Route::get('/marketing/coupons', [MarketingDataController::class, 'coupons']);

    // Campañas: envío masivo de una plantilla a una lista de contactos.
    // n8n manda el ritmo — pregunta qué hay pendiente, reclama, pide el
    // render de cada destinatario y reporta el resultado. Laravel no tiene
    // cron ni reintentos propios (ver MarketingCampaignController).
    Route::get('/marketing/campaigns/due', [MarketingCampaignController::class, 'due']);
    Route::post('/marketing/campaigns/{id}/claim', [MarketingCampaignController::class, 'claim']);
    Route::get('/marketing/campaigns/{id}/recipients', [MarketingCampaignController::class, 'recipients']);
    Route::get('/marketing/campaigns/{id}/recipients/{recipientId}/render', [MarketingCampaignController::class, 'render']);
    Route::post('/marketing/campaigns/{id}/recipients/{recipientId}/report', [MarketingCampaignController::class, 'report']);

    // Secuencias de seguimiento de cotizaciones. `sequences/due` es además
    // el disparador del housekeeping completo (App\Support\SequenceProcessor):
    // inscribir, sacar por compra, vencer pasos y cerrar ocurren justo
    // cuando n8n pregunta. {id} aquí es un email_sequence_step_sends.id.
    Route::get('/marketing/sequences/due', [MarketingSequenceController::class, 'due']);
    Route::post('/marketing/sequences/due/{id}/claim', [MarketingSequenceController::class, 'claim']);
    Route::get('/marketing/sequences/due/{id}/render', [MarketingSequenceController::class, 'render']);
    Route::post('/marketing/sequences/due/{id}/report', [MarketingSequenceController::class, 'report']);
});
