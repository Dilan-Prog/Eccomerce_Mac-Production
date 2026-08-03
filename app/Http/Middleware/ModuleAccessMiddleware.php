<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `can-access-module:{moduleKey}` (see $middlewareAliases
 * in app/Http/Kernel.php). Delegates to User::canAccessModule(), which
 * already knows how to treat legacy/full admins and system roles as
 * unrestricted. Already attached via `$this->middleware('can-access-module:{key}')`
 * in the constructor of every module controller listed in
 * config/admin-modules.php (dashboard, orders, transactions, cotizaciones,
 * categories, products, aspel, ecommerce, ads, clientes, site, settings) —
 * except StaffUserController and RoleController, which manage the
 * permission system itself and instead use a stricter
 * unrestricted-admin-only guard (see those controllers' constructors).
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
