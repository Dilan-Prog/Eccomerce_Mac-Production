<?php

namespace App\Http\Middleware;

use App\Models\AspelApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `aspel.token` (ver $middlewareAliases en
 * app/Http/Kernel.php). Protege las rutas POST /api/aspel/* — el script
 * externo de Aspel debe mandar `Authorization: Bearer {key_id}.{secret}`
 * con un token activo, administrado desde el módulo "Integración" del panel
 * admin (ver App\Http\Controllers\Backend\AspelApiTokenController). El
 * secreto nunca se guarda en claro — se busca el registro por key_id
 * (identidad pública) y se valida el secreto contra su hash.
 */
class AspelApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = (string) $request->bearerToken();
        [$keyId, $secret] = str_contains($bearer, '.') ? explode('.', $bearer, 2) : [null, null];

        $record = $keyId ? AspelApiToken::where('key_id', $keyId)->where('status', 1)->first() : null;

        if (!$record || !$secret || !Hash::check($secret, $record->secret_hash)) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido o revocado.'], 401);
        }

        $record->last_used_at = now();
        $record->save();

        return $next($request);
    }
}
