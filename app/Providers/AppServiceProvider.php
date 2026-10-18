<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\GeneralSetting;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use PDOException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Directiva: muestra el contenido SOLO a usuarios autenticados (para precios)
        Blade::directive('guestprice', function () {
            return "<?php if(auth()->check()): ?>";
        });
        Blade::directive('endguestprice', function () {
            return "<?php endif; ?>";
        });

        // Directiva: muestra acciones SOLO a guests (cotizar, whatsapp)
        Blade::directive('showguestactions', function () {
            return "<?php if(!auth()->check()): ?>";
        });
        Blade::directive('endshowguestactions', function () {
            return "<?php endif; ?>";
        });

        /**
         * Set Timezone.
         * En consola (artisan migrate sobre una BD vacia, php artisan test con SQLite en memoria) o
         * en el entorno `testing`, si la tabla general_settings aun no existe o la BD no responde, la
         * app arranca igual con UTC y un $settings vacio. En una peticion web real se reporta y se
         * relanza (comportamiento previo: 500 ante un fallo de BD), nunca se sirve el sitio a medias.
         * Se evita Schema::hasTable() a proposito: consultaria information_schema en cada request.
         */
        try {
            $generalSettings = GeneralSetting::first();
        } catch (QueryException|PDOException $e) {
            if (!($this->app->runningInConsole() || $this->app->environment('testing'))) {
                report($e);
                throw $e;
            }
            $generalSettings = null;
        }
        Config::set('app.timezone', $generalSettings?->time_zone ?: 'UTC');

        /** Share $settings con todas las vistas (nunca null: un modelo vacio si no hay fila) */
        $sharedSettings = $generalSettings ?? new GeneralSetting();
        View::composer('*', function ($view) use ($sharedSettings) {
            $view->with('settings', $sharedSettings);
        });

        /**
         * Share $navCategories con el partial del menú y el header (drawer móvil).
         * Cache de 1 hora — invalida con Cache::forget('nav_categories')
         * cuando se actualicen categorías desde el admin.
         */
        View::composer(['frontend.layouts.menu', 'frontend.layouts.header'], function ($view) {
            $navCategories = Cache::remember('nav_categories', 3600, function () {
                return Category::forMenu()->get();
            });
            $view->with('navCategories', $navCategories);
        });
    }
}
