<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `attribution.token` (ver $middlewareAliases en app/Http/Kernel.php).
 * Protege GET /api/attribution/lookup, que consulta n8n para saber de que anuncio vino un contacto.
 *
 * - Un unico token compartido, leido de config('attribution.api_token') (env ATTRIBUTION_API_TOKEN).
 *   Aislado del token de email marketing y del de Aspel. Se rota editando el .env.
 * - Solo `Authorization: Bearer <token>` (nunca por query string: quedaria en logs y en historial).
 * - Comparacion en tiempo constante (hash_equals).
 * - Token NO configurado, o de menos de 32 caracteres -> 503 (falla cerrado: nadie entra).
 * - Todas las respuestas del endpoint llevan `Cache-Control: no-store`.
 */
class AttributionApiTokenMiddleware
{
    /** Longitud minima del token configurado; menos que esto = endpoint cerrado (503). */
    public const MIN_TOKEN_LENGTH = 32;

    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('attribution.api_token');

        // Un token corto es adivinable: se trata como NO configurado (falla cerrado).
        if (strlen($expected) < self::MIN_TOKEN_LENGTH) {
            return $this->noStore(response()->json([
                'status' => 'error',
                'message' => 'Endpoint de atribución no configurado.',
            ], 503));
        }

        $given = (string) $request->bearerToken();

        if ($given === '' || !hash_equals($expected, $given)) {
            return $this->noStore(response()->json([
                'status' => 'error',
                'message' => 'Token inválido o revocado.',
            ], 401));
        }

        return $this->noStore($next($request));
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
