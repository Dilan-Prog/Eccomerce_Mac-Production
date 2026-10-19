<?php

namespace App\Http\Middleware;

use App\Models\MarketingApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `attribution.token` (ver $middlewareAliases en app/Http/Kernel.php).
 * Protege GET /api/attribution/lookup, que consulta n8n para saber de que anuncio vino un contacto.
 *
 * Credenciales aceptadas (solo `Authorization: Bearer ...`, nunca por query string: quedaria en logs
 * y en historial), en este orden:
 *  (a) Token creado en el panel (Marketing > Integracion) con uso "Atribucion Google Ads":
 *      `Bearer {key_id}.{secret}`, activo y de scope=attribution. Se guarda solo su hash; al usarlo
 *      se actualiza last_used_at. Un token de uso "marketing" NO entra aqui (y uno de "attribution"
 *      tampoco entra en /api/marketing/*).
 *  (b) Respaldo: token unico de config('attribution.api_token') (env ATTRIBUTION_API_TOKEN), de al
 *      menos 32 caracteres, comparado en tiempo constante (hash_equals). Se rota editando el .env.
 *
 * Respuestas:
 *  - 503 si NO existe ninguna credencial posible (el .env esta vacio/corto Y no hay un token activo
 *    de atribucion en la BD): falla cerrado, nadie entra.
 *  - 401 en cualquier otro caso sin credencial valida (falta, incorrecta, revocada o de otro uso).
 *  - Todas las respuestas del endpoint llevan `Cache-Control: no-store`.
 *
 * Si la tabla o la columna `scope` aun no existen (codigo desplegado antes de `php artisan migrate`),
 * las consultas a la BD fallan en silencio y el endpoint se comporta solo con el .env, sin 500.
 */
class AttributionApiTokenMiddleware
{
    /** Longitud minima del token del .env; menos que esto = ese token no existe (respaldo cerrado). */
    public const MIN_TOKEN_LENGTH = 32;

    public function handle(Request $request, Closure $next): Response
    {
        $given = (string) $request->bearerToken();

        // (a) Token del panel: {key_id}.{secret}.
        if ($given !== '') {
            $record = MarketingApiToken::findActiveByBearer($given, MarketingApiToken::SCOPE_ATTRIBUTION);
            if ($record) {
                $record->touchLastUsed();

                return $this->noStore($next($request));
            }
        }

        // (b) Respaldo del .env. Un token corto es adivinable: se trata como NO configurado.
        $expected = (string) config('attribution.api_token');
        $envUsable = strlen($expected) >= self::MIN_TOKEN_LENGTH;

        if ($envUsable && $given !== '' && hash_equals($expected, $given)) {
            return $this->noStore($next($request));
        }

        // (c) Ninguna credencial posible: endpoint cerrado.
        if (!$envUsable && MarketingApiToken::activeAttributionCount() === 0) {
            return $this->noStore(response()->json([
                'status' => 'error',
                'message' => 'Endpoint de atribución no configurado.',
            ], 503));
        }

        // (d) Hay credenciales, pero esta no es valida.
        return $this->noStore(response()->json([
            'status' => 'error',
            'message' => 'Token inválido o revocado.',
        ], 401));
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
