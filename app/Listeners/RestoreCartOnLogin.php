<?php

namespace App\Listeners;

use Cart;
use Illuminate\Auth\Events\Login;

/**
 * Trae de vuelta el carrito guardado (ver SyncCartToDatabase/SyncCartOnLogout)
 * cuando el usuario inicia sesión en cualquier navegador — incluye el login
 * automático por "Recordarme" (SessionGuard también dispara Login ahí).
 * Cart::restore() no truena si no hay nada guardado, y borra la fila de BD
 * al restaurarla (es de un solo uso, no una copia).
 */
class RestoreCartOnLogin
{
    public function handle(Login $event): void
    {
        Cart::restore($event->user->id);
    }
}
