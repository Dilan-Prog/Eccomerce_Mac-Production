<?php

namespace App\Listeners;

use App\Support\CartSync;

/**
 * Enganchado a los eventos de texto que el paquete de carrito dispara en
 * cada cambio (cart.added/cart.updated/cart.removed — ver
 * EventServiceProvider::boot()), para que el carrito quede guardado en BD y
 * el usuario lo vea igual al entrar desde otro navegador (RestoreCartOnLogin).
 * `Cart::destroy()` (vaciar carrito) no dispara ningún evento — ese caso se
 * maneja aparte, directo en CartController::clearCart().
 */
class SyncCartToDatabase
{
    public function handle($payload = null): void
    {
        if (!auth()->check()) {
            return;
        }

        CartSync::persistForUser(auth()->id());
    }
}
