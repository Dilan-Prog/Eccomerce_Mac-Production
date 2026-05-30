<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\GeneralSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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

        /** Set Timezone */
        $generalSettings = GeneralSetting::first();
        Config::set('app.timezone', $generalSettings->time_zone);

        /** Share $settings con todas las vistas */
        View::composer('*', function ($view) use ($generalSettings) {
            $view->with('settings', $generalSettings);
        });

        /**
         * Share $navCategories solo con el partial del menú.
         * Cache de 1 hora — invalida con Cache::forget('nav_categories')
         * cuando se actualicen categorías desde el admin.
         */
        View::composer('frontend.layouts.menu', function ($view) {
            $navCategories = Cache::remember('nav_categories', 3600, function () {
                return Category::forMenu()->get();
            });
            $view->with('navCategories', $navCategories);
        });
    }
}
