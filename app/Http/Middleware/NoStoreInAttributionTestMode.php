<?php

namespace App\Http\Middleware;

use App\Support\Attribution\AttributionTestMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Con el modo prueba de atribucion activo (cookie de prueba valida), el HTML y las respuestas llevan
 * contenido distinto al publico (stubs sin analitica, folio encendido, numero de prueba): se marcan
 * `Cache-Control: private, no-store` para que ningun CDN/hosting/navegador compartido las reutilice.
 *
 * Va en el grupo `web` DESPUES de EncryptCookies (necesita la cookie ya descifrada). Sin la cookie de
 * prueba en el request la respuesta no se toca ni se consulta la base de datos.
 */
class NoStoreInAttributionTestMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Comprobacion barata primero: active() solo se evalua (1 consulta por PK) si la cookie viene.
        $testRequest = $request->cookies->has(AttributionTestMode::cookieName())
            && AttributionTestMode::active($request);

        $response = $next($request);

        if ($testRequest) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
