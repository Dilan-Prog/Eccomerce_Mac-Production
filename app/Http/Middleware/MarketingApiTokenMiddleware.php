<?php

namespace App\Http\Middleware;

use App\Models\MarketingApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `marketing.token` (ver $middlewareAliases en
 * app/Http/Kernel.php). Protege las rutas GET /api/marketing/* — n8n debe
 * mandar `Authorization: Bearer {key_id}.{secret}` con un token activo,
 * administrado desde el módulo "Marketing" del panel admin (ver
 * App\Http\Controllers\Backend\MarketingApiTokenController). Sistema
 * totalmente aislado de aspel.token/AspelApiToken — ningún token de un
 * sistema autentica rutas del otro. El secreto nunca se guarda en claro —
 * se busca el registro por key_id (identidad pública) y se valida el
 * secreto contra su hash.
 */
class MarketingApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = (string) $request->bearerToken();
        [$keyId, $secret] = str_contains($bearer, '.') ? explode('.', $bearer, 2) : [null, null];

        $record = $keyId ? MarketingApiToken::where('key_id', $keyId)->where('status', 1)->first() : null;

        if (!$record || !$secret || !Hash::check($secret, $record->secret_hash)) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido o revocado.'], 401);
        }

        $record->last_used_at = now();
        $record->save();

        return $next($request);
    }
}
