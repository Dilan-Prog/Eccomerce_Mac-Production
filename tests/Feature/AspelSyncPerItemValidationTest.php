<?php

namespace Tests\Feature;

use App\Models\AspelSync;
use App\Models\PrecioXProductAspel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * POST /api/aspel/sync y /api/aspel/precio-x-producto: la validacion es POR REGISTRO. Un registro invalido se
 * rechaza y se reporta, pero los demas del lote SI se guardan (antes un solo registro malo hacia que el sitio
 * rechazara el lote completo con 422 y no se guardara ninguno).
 *
 * Sin RefreshDatabase: las migraciones de las tablas de Aspel se cargan a mano en la SQLite en memoria.
 */
class AspelSyncPerItemValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Las migraciones reales de estas tablas usan ALTER TABLE ... COMMENT (solo MySQL), asi que en la SQLite de
        // pruebas se crean a mano con las mismas columnas (todas las del $fillable del modelo, texto nullable).
        foreach (['aspel_products', 'precio_x_product_aspel', 'precios'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::create('aspel_products', function ($table) {
            $table->id();
            foreach ((new AspelSync())->getFillable() as $column) {
                if ($column === 'cve_art') {
                    $table->string('cve_art', 50)->unique();
                } elseif ($column === 'remote_updated_at') {
                    $table->timestamp('remote_updated_at')->nullable();
                } else {
                    $table->string($column)->nullable();
                }
            }
            $table->timestamps();
        });
        Schema::create('precios', function ($table) {
            $table->id();
            $table->integer('cve_precio')->unique();
            $table->string('descripcion')->nullable();
            $table->string('cve_bita')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('precio_x_product_aspel', function ($table) {
            $table->id();
            $table->string('cve_art');
            $table->integer('cve_precio');
            $table->decimal('precio', 15, 4)->nullable();
            $table->timestamps();
            $table->unique(['cve_art', 'cve_precio']);
        });

        // Estas pruebas son del comportamiento del controlador; el token se prueba aparte (ultimo test).
        $this->withoutMiddleware(\App\Http\Middleware\AspelApiTokenMiddleware::class);
    }

    private function product(string $sku, array $over = []): array
    {
        return array_merge(['cve_art' => $sku, 'descr' => 'Producto ' . $sku, 'exist' => 5, 'ult_costo' => 10.5, 'status' => 'A'], $over);
    }

    // ------------------------------------------------------------------ /api/aspel/sync (productos)

    public function test_one_invalid_product_does_not_reject_the_rest_of_the_batch(): void
    {
        $items = [
            $this->product('SKU-1'),
            $this->product('SKU-2', ['descr' => str_repeat('x', 300)]),   // descr max 255 -> invalido
            $this->product('SKU-3'),
        ];

        $r = $this->postJson('/api/aspel/sync', ['items' => $items])->assertOk();

        $r->assertJsonPath('status', 'PARTIAL')
            ->assertJsonPath('synced', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('rejected', 1);
        $this->assertStringContainsString('SKU-2', $r->json('errors.0'));
        $this->assertStringContainsString('datos invalidos', $r->json('errors.0'));

        $this->assertSame(['SKU-1', 'SKU-3'], AspelSync::orderBy('cve_art')->pluck('cve_art')->all());
    }

    public function test_several_kinds_of_invalid_data_are_each_rejected_individually(): void
    {
        $items = [
            $this->product('OK-1'),
            $this->product('BAD-FECHA', ['fch_ultcom' => '31/12/2026']),            // date_format Y-m-d H:i:s
            $this->product('BAD-NUM', ['exist' => 'muchos']),                         // numeric
            $this->product('BAD-CHAR', ['con_serie' => 'SI']),                        // size:1
            ['descr' => 'sin clave'],                                                 // cve_art requerido
            $this->product(str_repeat('K', 60)),                                      // cve_art max 50
            $this->product('OK-2', ['fch_ultcom' => '2026-10-30 12:00:00', 'con_serie' => 'S']),
        ];

        $r = $this->postJson('/api/aspel/sync', ['items' => $items])->assertOk();

        $r->assertJsonPath('status', 'PARTIAL')->assertJsonPath('synced', 2)->assertJsonPath('rejected', 5);
        $this->assertSame(['OK-1', 'OK-2'], AspelSync::orderBy('cve_art')->pluck('cve_art')->all());
    }

    public function test_a_fully_valid_batch_is_ok_and_reports_zero_rejected(): void
    {
        $r = $this->postJson('/api/aspel/sync', ['items' => [$this->product('A'), $this->product('B')]])->assertOk();

        $r->assertJsonPath('status', 'OK')->assertJsonPath('synced', 2)->assertJsonPath('rejected', 0)->assertJsonPath('errors', []);
        $this->assertSame(2, AspelSync::count());
    }

    public function test_a_batch_where_every_product_is_invalid_keeps_the_422_and_saves_nothing(): void
    {
        $items = [$this->product('X', ['exist' => 'abc']), $this->product('Y', ['descr' => str_repeat('d', 999)])];

        $r = $this->postJson('/api/aspel/sync', ['items' => $items])->assertStatus(422);

        $r->assertJsonPath('synced', 0)->assertJsonPath('rejected', 2);
        $this->assertSame(0, AspelSync::count());
    }

    public function test_missing_or_empty_items_is_still_422(): void
    {
        $this->postJson('/api/aspel/sync', [])->assertStatus(422);
        $this->postJson('/api/aspel/sync', ['items' => []])->assertStatus(422);
        $this->postJson('/api/aspel/sync', ['items' => 'texto'])->assertStatus(422);
    }

    public function test_items_that_are_not_objects_are_rejected_without_breaking_the_batch(): void
    {
        $r = $this->postJson('/api/aspel/sync', ['items' => ['texto', $this->product('OK-1'), 123]])->assertOk();

        $r->assertJsonPath('synced', 1)->assertJsonPath('rejected', 2);
        $this->assertSame(['OK-1'], AspelSync::pluck('cve_art')->all());
    }

    public function test_nested_batches_are_still_flattened(): void
    {
        $r = $this->postJson('/api/aspel/sync', ['items' => [[$this->product('N-1'), $this->product('N-2')]]])->assertOk();

        $r->assertJsonPath('synced', 2)->assertJsonPath('status', 'OK');
    }

    public function test_existing_products_are_updated_in_place(): void
    {
        $this->postJson('/api/aspel/sync', ['items' => [$this->product('U-1', ['exist' => 5])]])->assertOk();
        $this->postJson('/api/aspel/sync', ['items' => [$this->product('U-1', ['exist' => 9]), $this->product('U-2', ['exist' => 'x'])]])->assertOk();

        $this->assertSame(1, AspelSync::where('cve_art', 'U-1')->count());
        $this->assertEquals(9, AspelSync::where('cve_art', 'U-1')->value('exist'));
        $this->assertFalse(AspelSync::where('cve_art', 'U-2')->exists());
    }

    public function test_rejections_are_logged_once_per_request_without_spamming(): void
    {
        Log::spy();
        $items = [];
        for ($i = 0; $i < 30; $i++) {
            $items[] = $this->product('B' . $i, ['exist' => 'abc']);
        }
        $items[] = $this->product('GOOD');

        $this->postJson('/api/aspel/sync', ['items' => $items])->assertOk();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message, $ctx = []) => str_contains($message, 'rechazados por validacion') && $ctx['rejected'] === 30 && count($ctx['muestra']) === 5)
            ->once();
    }

    // ------------------------------------------------------------------ /api/aspel/precio-x-producto

    private function seedPriceCatalog(): void
    {
        AspelSync::create(['cve_art' => 'P-1', 'descr' => 'p1']);
        AspelSync::create(['cve_art' => 'P-2', 'descr' => 'p2']);
        DB::table('precios')->insert(['cve_precio' => 1, 'descripcion' => 'Lista 1', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_price_batch_one_invalid_record_does_not_reject_the_rest(): void
    {
        $this->seedPriceCatalog();
        $items = [
            ['cve_art' => 'P-1', 'cve_precio' => 1, 'precio' => 100.5],
            ['cve_art' => 'P-2', 'cve_precio' => 'uno', 'precio' => 5],      // cve_precio no entero
            ['cve_art' => 'P-2', 'cve_precio' => 1, 'precio' => 'caro'],     // precio no numerico
            ['cve_art' => 'P-2', 'cve_precio' => 1, 'precio' => 77],
        ];

        $r = $this->postJson('/api/aspel/precio-x-producto', ['items' => $items])->assertOk();

        $r->assertJsonPath('status', 'PARTIAL')->assertJsonPath('synced', 2)->assertJsonPath('rejected', 2)->assertJsonPath('total', 4);
        $this->assertSame(2, PrecioXProductAspel::count());
    }

    public function test_price_missing_product_is_still_a_partial_200_and_not_counted_as_rejected(): void
    {
        $this->seedPriceCatalog();
        $items = [
            ['cve_art' => 'NO-EXISTE', 'cve_precio' => 1, 'precio' => 1],
            ['cve_art' => 'P-1', 'cve_precio' => 99, 'precio' => 1],         // precio (lista) inexistente
            ['cve_art' => 'P-1', 'cve_precio' => 1, 'precio' => 10],
        ];

        $r = $this->postJson('/api/aspel/precio-x-producto', ['items' => $items])->assertOk();

        $r->assertJsonPath('status', 'PARTIAL')->assertJsonPath('synced', 1)->assertJsonPath('rejected', 0);
        $this->assertStringContainsString("Producto con cve_art 'NO-EXISTE' no existe", $r->json('errors.0'));
        $this->assertStringContainsString("Precio con cve_precio '99' no existe", $r->json('errors.1'));
    }

    public function test_price_batch_all_invalid_keeps_the_422(): void
    {
        $this->seedPriceCatalog();

        $this->postJson('/api/aspel/precio-x-producto', ['items' => [['cve_art' => 'P-1', 'cve_precio' => 'x', 'precio' => 1]]])
            ->assertStatus(422)->assertJsonPath('synced', 0)->assertJsonPath('rejected', 1);
        $this->postJson('/api/aspel/precio-x-producto', [])->assertStatus(422);
    }

    // ------------------------------------------------------------------ token

    public function test_the_aspel_routes_still_require_the_token(): void
    {
        $this->withMiddleware(\App\Http\Middleware\AspelApiTokenMiddleware::class);   // anula el withoutMiddleware del setUp

        $this->postJson('/api/aspel/sync', ['items' => [$this->product('Z')]])->assertStatus(401);
        $this->postJson('/api/aspel/precio-x-producto', ['items' => []])->assertStatus(401);
        $this->assertSame(0, AspelSync::count());
    }
}
