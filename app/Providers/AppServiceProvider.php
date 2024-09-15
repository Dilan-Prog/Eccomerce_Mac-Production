<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use Illuminate\Pagination\Paginator;
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

        /**Set Timezone */

        $generalSettings = GeneralSetting::first();
        Config::set('app.timezone', $generalSettings->time_zone);

        /**Share variable at all view */

        View::composer('*', function($view) use ($generalSettings){
            $view->with('settings', $generalSettings);
        });

    }
}
