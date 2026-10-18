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

        // Limite para el endpoint publico POST /track-conversion (web.php).
        // Se conserva el nombre 'tracking' (60/min por IP) por compatibilidad, pero la ruta ya
        // no lo usa: ahora aplica 'tracking-ip' y 'tracking-session' (abajo).
        RateLimiter::for('tracking', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // POST /track-conversion: 300/min por IP (oficinas y redes moviles comparten IP por NAT).
        RateLimiter::for('tracking-ip', function (Request $request) {
            return Limit::perMinute(300)->by('tracking-ip:' . $request->ip());
        });

        // POST /track-conversion: 60/min por session_id (si no viene, por IP). Frena un navegador en
        // bucle sin castigar a los demas visitantes de la misma IP. La clave esta ACOTADA (IP + uno de
        // 64 cubos segun el hash del session_id) para que un atacante que invente session_id no pueda
        // crear claves ilimitadas en la cache. El session_id se lee igual que en store().
        RateLimiter::for('tracking-session', function (Request $request) {
            $sessionId = \App\Http\Controllers\Backend\TrackConversionController::payload($request)['session_id'] ?? null;
            $sessionId = is_string($sessionId) ? trim($sessionId) : '';

            return Limit::perMinute(60)->by(
                $sessionId !== ''
                    ? 'tracking-session:' . $request->ip() . ':' . (crc32($sessionId) % 64)
                    : 'tracking-session-ip:' . $request->ip()
            );
        });

        // GET /api/attribution/lookup (n8n): 120/min por IP. Va ANTES del token en la ruta,
        // asi tambien frena a quien pruebe tokens a ciegas.
        RateLimiter::for('attribution-api', function (Request $request) {
            return Limit::perMinute(120)->by('attribution-api:' . $request->ip());
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
