<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthForCart
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'authenticated' => false,
                'status'        => 'error',
                'message'       => 'Inicia sesión para agregar productos al carrito.',
                'redirect'      => route('login'),
            ], 401);
        }

        return redirect()->route('login')
            ->with('info', 'Debes iniciar sesión para continuar con tu compra.');
    }
}
