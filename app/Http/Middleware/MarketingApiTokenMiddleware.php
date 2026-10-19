<?php

namespace App\Http\Middleware;

use App\Models\MarketingApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `marketing.token` (ver $middlewareAliases en
 * app/Http/Kernel.php). Protege las rutas GET /api/marketing/* — n8n debe
 * mandar `Authorization: Bearer {key_id}.{secret}` con un token activo,
 * administrado desde el módulo "Marketing" del panel admin (ver
 * App\Http\Controllers\Backend\MarketingApiTokenController). Sistema
 * totalmente aislado de aspel.token/AspelApiToken — ningún token de un
 * sistema autentica rutas del otro. Dentro de marketing_api_tokens solo
 * valen los de uso (scope) "marketing"; los de "attribution" son para
 * /api/attribution/lookup (ver AttributionApiTokenMiddleware). El secreto nunca se guarda en claro —
 * se busca el registro por key_id (identidad pública) y se valida el
 * secreto contra su hash.
 */
class MarketingApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo vale un token de uso "marketing": uno de uso "attribution" (que se crea en este mismo
        // módulo para /api/attribution/lookup) NO autentica estas rutas.
        $record = MarketingApiToken::findActiveByBearer((string) $request->bearerToken(), MarketingApiToken::SCOPE_MARKETING);

        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido o revocado.'], 401);
        }

        $record->last_used_at = now();
        $record->save();

        return $next($request);
    }
}
