<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if($request->user()->role !== $role){
            if($request->user()->role == 'admin'){
                return redirect()->route('admin.dashbaord');
            }elseif($request->user()->role == 'associate'){
                return redirect()->route('associate.dashboard');
            }elseif($request->user()->role == 'technician'){
                return redirect()->route('technician.dashboard');
            }elseif($request->user()->role == 'user') {
                return redirect()->route('user.dashboard');
            }
        }
        return $next($request);
    }
}
