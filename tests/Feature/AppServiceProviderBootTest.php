<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Providers\AppServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Guarda de AppServiceProvider::boot() ante la ausencia de la tabla general_settings.
 */
class AppServiceProviderBootTest extends TestCase
{
    private function bootProvider(): void
    {
        (new AppServiceProvider($this->app))->boot();
    }

    /** Simula una peticion web (no consola) en un entorno distinto de testing. */
    private function pretendWebRequestInProduction(): void
    {
        $console = new ReflectionProperty($this->app, 'isRunningInConsole');
        $console->setAccessible(true);
        $console->setValue($this->app, false);
        $this->app['env'] = 'production';
    }

    /** $settings que el composer '*' entrega a una vista cualquiera. */
    private function sharedSettings(): mixed
    {
        $path = tempnam(sys_get_temp_dir(), 'vw') . '.blade.php';
        file_put_contents($path, 'x');
        try {
            $view = view()->file($path);
            $this->app['view']->callComposer($view);

            return $view->getData()['settings'] ?? null;
        } finally {
            @unlink($path);
        }
    }

    public function test_missing_table_in_console_falls_back_to_utc_and_an_empty_settings_model(): void
    {
        $this->assertFalse(Schema::hasTable('general_settings'));
        config(['app.timezone' => 'America/Mexico_City']);

        $this->bootProvider();

        $this->assertSame('UTC', config('app.timezone'));
        // El composer comparte un modelo vacio (nunca null): $settings->campo no revienta.
        $settings = $this->sharedSettings();
        $this->assertInstanceOf(GeneralSetting::class, $settings);
        $this->assertNull($settings->time_zone);
    }

    public function test_existing_table_sets_the_timezone_and_shares_the_row(): void
    {
        Schema::create('general_settings', function ($table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });
        DB::table('general_settings')->insert(['site_name' => 'Mac del Norte', 'time_zone' => 'America/Mexico_City']);

        $this->bootProvider();

        $this->assertSame('America/Mexico_City', config('app.timezone'));
        $this->assertSame('Mac del Norte', $this->sharedSettings()->site_name);
    }

    public function test_row_without_time_zone_defaults_to_utc(): void
    {
        Schema::create('general_settings', function ($table) {
            $table->id();
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });
        DB::table('general_settings')->insert(['time_zone' => null]);
        config(['app.timezone' => 'America/Mexico_City']);

        $this->bootProvider();

        $this->assertSame('UTC', config('app.timezone'));
    }

    public function test_missing_table_in_a_production_web_request_is_rethrown(): void
    {
        $this->assertFalse(Schema::hasTable('general_settings'));
        $this->pretendWebRequestInProduction();

        $this->expectException(QueryException::class);

        $this->bootProvider();
    }
}
