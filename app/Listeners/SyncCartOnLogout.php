<?php

namespace App\Listeners;

use App\Support\CartSync;
use Illuminate\Auth\Events\Logout;

/**
 * Cierra un hueco real: si el usuario inicia sesión en el navegador B (eso
 * saca el carrito guardado de BD y lo borra — ver RestoreCartOnLogin) y
 * luego cierra sesión en B sin agregar/quitar nada, sin este listener el
 * carrito se perdería para siempre en un tercer navegador. Se dispara antes
 * de que la sesión se invalide, así que el carrito todavía está disponible.
 */
class SyncCartOnLogout
{
    public function handle(Logout $event): void
    {
        if (!$event->user) {
            return;
        }

        CartSync::persistForUser($event->user->id);
    }
}
