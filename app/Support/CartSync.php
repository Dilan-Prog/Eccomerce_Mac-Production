<?php

namespace App\Support;

use Cart;
use Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Guarda el carrito en la tabla `shoppingcart` para el usuario dado, usado
 * por SyncCartToDatabase (cada cambio del carrito) y SyncCartOnLogout (para
 * que "entrar, no tocar nada, salir" no pierda el carrito — ver
 * RestoreCartOnLogin, que borra la fila guardada al restaurarla).
 *
 * store() truena si ya existe una fila para ese identifier, por eso primero
 * se borra (erase() no truena si no hay nada) y luego se guarda solo si el
 * carrito no quedó vacío. El try/catch cubre la carrera rara de dos
 * peticiones casi simultáneas del mismo usuario (doble clic) — se ignora esa
 * sincronización puntual en vez de tronarle la respuesta al usuario; el
 * siguiente cambio del carrito sí se guarda bien.
 */
class CartSync
{
    public static function persistForUser(int $identifier): void
    {
        try {
            Cart::erase($identifier);

            if (Cart::content()->isNotEmpty()) {
                Cart::store($identifier);
            }
        } catch (CartAlreadyStoredException | QueryException $e) {
            Log::warning("CartSync: no se pudo sincronizar el carrito del usuario {$identifier}: " . $e->getMessage());
        }
    }
}
