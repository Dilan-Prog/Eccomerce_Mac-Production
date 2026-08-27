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
 *
 * Optional second argument — `can-access-module:{moduleKey},{action}` —
 * delegates instead to User::canPerform() for granular Ver/Crear/Editar/
 * Borrar/Exportar checks. Only meaningful for the 3 modules in
 * RoleModulePermission::GRANULAR_MODULE_KEYS (aspel, aspel-integracion,
 * cotizaciones); every other existing single-argument call site is
 * unaffected ($action stays null, same behavior as before).
 */
class ModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey, ?string $action = null): Response
    {
        $user = $request->user();
        $allowed = $action === null
            ? $user->canAccessModule($moduleKey)
            : $user->canPerform($moduleKey, $action);

        if (!$allowed) {
            abort(403, 'No tienes acceso a este módulo.');
        }

        return $next($request);
    }
}
