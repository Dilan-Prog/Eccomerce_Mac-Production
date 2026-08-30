<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/user/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Limite propio y mas generoso para /api/marketing/* -- n8n necesita
        // llamar el render de correo una vez por cada cliente de una campana
        // (potencialmente cientos en una sola corrida), lo que choca con el
        // limite general de 60/min pensado para trafico normal de API. Se
        // deja como un limitador CON nombre distinto (no se toca el de
        // arriba) para no afectar /api/aspel/* ni el resto. Ver
        // app/Http/Kernel.php (throttle removido del grupo 'api') y
        // routes/api.php (aplicado explicito por grupo).
        RateLimiter::for('marketing-api', function (Request $request) {
            return Limit::perMinute(600)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

                //new rutes
            Route::middleware(['web','auth','role:admin'])//admin
                ->prefix('admin')
                ->as('admin.')
                ->group(base_path('routes/admin.php'));

                Route::middleware(['web','auth','role:associate'])//associate
                ->prefix('associate')
                ->as('associate.')
                ->group(base_path('routes/associate.php'));

            Route::middleware(['web','auth','role:technician|admin'])//technician
                ->prefix('technician')
                ->as('technician.')
                ->group(base_path('routes/technician.php'));

        });
    }
}
