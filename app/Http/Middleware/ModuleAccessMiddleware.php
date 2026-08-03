<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `can-access-module:{moduleKey}` (see $middlewareAliases
 * in app/Http/Kernel.php). Delegates to User::canAccessModule(), which
 * already knows how to treat legacy/full admins and system roles as
 * unrestricted. NOT YET attached to any controller/route — registration only,
 * wiring it up to real admin routes is a separate follow-up task.
 */
class ModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (!$request->user()->canAccessModule($moduleKey)) {
            abort(403, 'No tienes acceso a este módulo.');
        }

        return $next($request);
    }
}
