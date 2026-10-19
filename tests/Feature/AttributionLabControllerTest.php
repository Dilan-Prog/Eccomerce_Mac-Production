<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\TrackConversion;
use App\Models\User;
use App\Support\Attribution\AttributionTestMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Laboratorio de atribucion del admin (AttributionLabController).
 *
 * Sin RefreshDatabase: solo se crean las tablas de track_conversions y de usuarios/roles en SQLite en
 * memoria. En el entorno de pruebas Laravel no verifica CSRF (runningUnitTests), asi que aqui no se
 * prueba el 419; si se verifica que las rutas de escritura no aceptan GET.
 */
class AttributionLabControllerTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    private const TOKEN = 'lab-test-token-9f8e7d6c5b4a39281706f5e4d3c2b1a0';
    private const GCLID = 'TEST_GCLID_0123456789ABCDEF';

    /** @var string[] archivos temporales (vista stub) a borrar */
    private array $tmpFiles = [];

    /** @var string[] carpetas temporales de la vista stub (de la mas profunda a la raiz) */
    private array $tmpDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        $this->prepareAdminUsersTables();
        config(['attribution.api_token' => self::TOKEN]);
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            @unlink($file);
        }
        foreach ($this->tmpDirs as $dir) {
            @rmdir($dir);
        }
        parent::tearDown();
    }

    // ------------------------------------------------------------------ ayudas

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** Admin restringido a los modulos indicados (rol personalizado). */
    private function restrictedAdmin(array $modules): User
    {
        $role = Role::create(['name' => 'Editor ' . uniqid(), 'slug' => 'editor-' . uniqid(), 'is_system' => false]);
        foreach ($modules as $module) {
            RoleModulePermission::create(['role_id' => $role->id, 'module_key' => $module, 'can_access' => true]);
        }

        return User::factory()->create(['role' => 'admin', 'role_id' => $role->id]);
    }

    private function url(string $path = ''): string
    {
        return '/admin/attribution-lab' . $path;
    }

    private function testEvent(array $attributes = [], ?string $createdAt = null): TrackConversion
    {
        return $this->makeEvent(array_merge(['is_test' => true], $attributes), $createdAt);
    }

    /** Sustituye (en el finder, con un archivo temporal) la vista del lab para no depender del layout del admin. */
    private function stubLabView(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lab_views_' . uniqid();
        $dir = $root . DIRECTORY_SEPARATOR . 'admin-ui' . DIRECTORY_SEPARATOR . 'attribution-lab';
        mkdir($dir, 0777, true);
        $file = $dir . DIRECTORY_SEPARATOR . 'index.blade.php';
        file_put_contents($file, '<div id="lab-stub">{{ json_encode($lab) }}</div>');
        $this->tmpFiles[] = $file;
        $this->tmpDirs = [$dir, dirname($dir), $root];
        // prependLocation: gana sobre resources/views, asi no se usa el layout real del admin (que necesita MySQL).
        app('view')->getFinder()->prependLocation($root);
    }

    /** Navega con la cookie de prueba ya cifrada (withCookie() de Laravel la cifraria otra vez). */
    private function withTestCookie(User $user, ?int $expiresAt = null): static
    {
        return $this->withCredentials()->withUnencryptedCookie(AttributionTestMode::cookieName(), $this->testModeCookieValue($user, $expiresAt));
    }

    // ------------------------------------------------------------------ acceso

    public function test_guest_cannot_access(): void
    {
        $this->getJson($this->url('/status'))->assertUnauthorized();
        $this->get($this->url())->assertRedirect();
        $this->postJson($this->url('/activate'))->assertUnauthorized();
        $this->postJson($this->url('/settings'), ['n8n_url' => 'https://93.184.216.34/x'])->assertUnauthorized();
        $this->postJson($this->url('/n8n-test'))->assertUnauthorized();
        $this->deleteJson($this->url('/tests'))->assertUnauthorized();
    }

    public function test_non_admin_user_cannot_access(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // RoleMiddleware redirige al dashboard de su rol: nunca llega al controlador.
        $this->actingAs($user)->get($this->url('/status'))->assertStatus(302);
        $this->actingAs($user)->post($this->url('/activate'))->assertStatus(302);
        $this->assertSame(0, TrackConversion::count());
    }

    public function test_restricted_admin_without_site_gets_403_on_every_route(): void
    {
        $admin = $this->restrictedAdmin(['dashboard']);
        $this->testEvent(['contact_folio' => 'MN7K4Q2']);

        $this->actingAs($admin);
        $this->getJson($this->url('/status'))->assertForbidden();
        $this->getJson($this->url('/events'))->assertForbidden();
        $this->getJson($this->url('/events/1'))->assertForbidden();
        $this->getJson($this->url('/products-search?q=ab'))->assertForbidden();
        $this->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2'])->assertForbidden();
        $this->postJson($this->url('/extract'), ['text' => 'MN7K4Q2'])->assertForbidden();
        $this->postJson($this->url('/activate'))->assertForbidden();
        $this->postJson($this->url('/deactivate'))->assertForbidden();
        $this->postJson($this->url('/settings'), ['test_whatsapp_number' => '5218112345678'])->assertForbidden();
        $this->postJson($this->url('/n8n-test'))->assertForbidden();
        $this->deleteJson($this->url('/tests'))->assertForbidden();
        $this->get($this->url())->assertForbidden();

        // El DELETE prohibido no borro nada.
        $this->assertSame(1, TrackConversion::count());
    }

    public function test_restricted_admin_with_site_can_access(): void
    {
        $admin = $this->restrictedAdmin(['site']);

        $this->actingAs($admin)->getJson($this->url('/status'))->assertOk()->assertJson(['status' => 'success']);
    }

    public function test_write_routes_do_not_accept_get(): void
    {
        $this->actingAs($this->admin());

        foreach (['/activate', '/deactivate', '/lookup', '/extract', '/tests', '/settings', '/n8n-test'] as $path) {
            $this->get($this->url($path))->assertStatus(405);
        }
        $this->post($this->url('/tests'))->assertStatus(405);
        $this->put($this->url('/events/1'))->assertStatus(405);
    }

    // ------------------------------------------------------------------ index

    public function test_index_passes_public_config_and_never_the_token(): void
    {
        $this->stubLabView();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get($this->url());

        $response->assertOk()->assertViewIs('admin-ui.attribution-lab.index');
        $response->assertViewHas('lab', function (array $lab) {
            $this->assertEqualsCanonicalizing(
                ['routes', 'csrf', 'site_url', 'api_lookup_url', 'can_real', 'test_mode_active', 'categorias_url'],
                array_keys($lab)
            );
            $this->assertEqualsCanonicalizing(
                ['status', 'events', 'event', 'lookup', 'extract', 'productsSearch', 'activate', 'deactivate', 'destroyTests', 'settings', 'n8nTest'],
                array_keys($lab['routes'])
            );
            $this->assertStringContainsString('__ID__', $lab['routes']['event']);
            $this->assertTrue($lab['can_real']);
            $this->assertFalse($lab['test_mode_active']);
            $this->assertSame(url('/api/attribution/lookup'), $lab['api_lookup_url']);
            $this->assertSame(url('/categorias'), $lab['categorias_url']);

            return true;
        });

        $this->assertStringNotContainsString(self::TOKEN, $response->getContent());
        $this->assertStringNotContainsString(self::TOKEN, json_encode($response->viewData('lab')));
    }

    public function test_index_can_real_is_false_without_ads_module(): void
    {
        $this->stubLabView();

        $this->actingAs($this->restrictedAdmin(['site']))
            ->get($this->url())
            ->assertOk()
            ->assertViewHas('lab', fn (array $lab) => $lab['can_real'] === false);
    }

    public function test_index_reports_test_mode_active_when_cookie_is_valid(): void
    {
        $this->stubLabView();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->withTestCookie($admin)
            ->get($this->url())
            ->assertViewHas('lab', fn (array $lab) => $lab['test_mode_active'] === true);
    }

    // ------------------------------------------------------------------ activar / desactivar

    public function test_activate_sets_http_only_encrypted_cookie(): void
    {
        $response = $this->actingAs($this->admin())->postJson($this->url('/activate'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('test_mode.active', true)
            ->assertJsonStructure(['message', 'test_mode' => ['active', 'expires_at_mx']]);

        $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === AttributionTestMode::cookieName());
        $this->assertNotNull($cookie, 'activate debe emitir la cookie mdn_attr_test');
        $this->assertSame('mdn_attr_test', $cookie->getName());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));
        $this->assertGreaterThan(time(), $cookie->getExpiresTime());
        // Cifrada por EncryptCookies: el valor no es el JSON en claro.
        $this->assertStringNotContainsString('"u"', (string) $cookie->getValue());
    }

    public function test_deactivate_expires_the_cookie(): void
    {
        $response = $this->actingAs($this->admin())->postJson($this->url('/deactivate'));

        $response->assertOk()->assertJsonPath('test_mode.active', false);

        $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === AttributionTestMode::cookieName());
        $this->assertNotNull($cookie);
        $this->assertLessThan(time(), $cookie->getExpiresTime());
    }

    // ------------------------------------------------------------------ destroyTests

    public function test_destroy_tests_deletes_only_test_rows_and_ignores_request_filters(): void
    {
        $real = $this->makeEvent(['contact_folio' => 'MN7K4Q2']);
        $this->testEvent(['contact_folio' => 'MN8B3C4']);
        $this->testEvent(['contact_folio' => 'MN9D5F6']);

        // Aunque el request intente apuntar a la fila real, el WHERE es fijo (is_test = 1).
        $this->actingAs($this->admin())
            ->deleteJson($this->url('/tests'), ['id' => $real->id, 'is_test' => false, 'ids' => [$real->id]])
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'deleted' => 2]);

        $this->assertSame(1, TrackConversion::count());
        $this->assertNotNull(TrackConversion::find($real->id));
    }

    // ------------------------------------------------------------------ lookup

    public function test_lookup_validates_mode_scope_and_tz(): void
    {
        $this->actingAs($this->admin());

        $this->postJson($this->url('/lookup'), ['mode' => 'otro'])->assertStatus(422)->assertJsonPath('status', 'error');
        $this->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2', 'scope' => 'todo'])->assertStatus(422);
        $this->postJson($this->url('/lookup'), ['mode' => 'window', 'tz' => 'Marte/Olimpo'])->assertStatus(422);
    }

    public function test_lookup_scope_test_never_returns_real_rows(): void
    {
        $this->makeEvent(['contact_folio' => 'MN7K4Q2', 'gclid' => self::GCLID . 'REAL', 'utm_campaign' => 'cliente-real']);
        $this->testEvent(['contact_folio' => 'MN8B3C4', 'gclid' => self::GCLID, 'utm_campaign' => 'prueba']);
        $this->actingAs($this->admin());

        // Folio de una fila REAL: por defecto (scope test) se responde como si no existiera.
        $real = $this->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2']);
        $real->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('http_status', 404)
            ->assertJsonPath('scope', 'test')
            ->assertJsonPath('body.status', 'error')
            ->assertJsonPath('body.message', 'Folio no encontrado.');
        $this->assertStringNotContainsString('cliente-real', $real->getContent());
        $this->assertStringNotContainsString(self::GCLID . 'REAL', $real->getContent());

        // Folio de una fila de prueba: 200 con is_test true.
        $test = $this->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN8B3C4', 'scope' => 'test']);
        $test->assertOk()
            ->assertJsonPath('http_status', 200)
            ->assertJsonPath('body.is_test', true)
            ->assertJsonPath('body.folio', 'MN8B3C4')
            ->assertJsonPath('body.gclid', self::GCLID);
        $this->assertIsInt($test->json('elapsed_ms'));
    }

    public function test_lookup_folio_not_found_passes_through_service_status(): void
    {
        $this->actingAs($this->admin())
            ->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN2B3C4'])
            ->assertOk()
            ->assertJsonPath('http_status', 404);
    }

    public function test_lookup_scope_real_requires_ads_module(): void
    {
        $this->makeEvent(['contact_folio' => 'MN7K4Q2', 'gclid' => self::GCLID]);

        // Solo 'site': 403.
        $this->actingAs($this->restrictedAdmin(['site']))
            ->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2', 'scope' => 'real'])
            ->assertForbidden()
            ->assertJsonPath('status', 'error');

        // 'site' + 'ads': ve la fila real.
        $this->actingAs($this->restrictedAdmin(['site', 'ads']))
            ->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2', 'scope' => 'real'])
            ->assertOk()
            ->assertJsonPath('http_status', 200)
            ->assertJsonPath('scope', 'real')
            ->assertJsonPath('body.is_test', false);

        // Admin completo: tambien.
        $this->actingAs($this->admin())
            ->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2', 'scope' => 'real'])
            ->assertOk()
            ->assertJsonPath('body.gclid', self::GCLID);
    }

    public function test_lookup_window_converts_naive_dates_from_tz_to_utc(): void
    {
        // 16:05 UTC = 10:05 en Ciudad de Mexico (UTC-6, sin horario de verano).
        $this->testEvent(['type' => 'telefono_header', 'event_id' => 'win-test'], '2026-10-10 16:05:00');
        $this->makeEvent(['type' => 'telefono_header', 'event_id' => 'win-real'], '2026-10-10 16:06:00');
        $this->actingAs($this->admin());

        // Sin tz -> America/Mexico_City.
        $mx = $this->postJson($this->url('/lookup'), [
            'mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10T10:00', 'to' => '2026-10-10T10:10',
        ]);
        $mx->assertOk()
            ->assertJsonPath('http_status', 200)
            ->assertJsonPath('body.from', '2026-10-10T16:00:00Z')
            ->assertJsonPath('body.to', '2026-10-10T16:10:00Z')
            // scope test: la fila real se elimina y count se recalcula.
            ->assertJsonPath('body.count', 1)
            ->assertJsonPath('body.events.0.event_id', 'win-test')
            ->assertJsonPath('body.events.0.is_test', true);

        // tz explicita: las mismas horas ingenuas en UTC caen 6 h antes.
        $utc = $this->postJson($this->url('/lookup'), [
            'mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10 16:00:00', 'to' => '2026-10-10 16:10:00', 'tz' => 'UTC',
        ]);
        $utc->assertOk()->assertJsonPath('body.from', '2026-10-10T16:00:00Z')->assertJsonPath('body.count', 1);

        // Con zona explicita (Z / offset) se respeta tal cual, sin aplicar tz.
        $zoned = $this->postJson($this->url('/lookup'), [
            'mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10T10:00:00-06:00', 'to' => '2026-10-10T16:10:00Z', 'tz' => 'Asia/Tokyo',
        ]);
        $zoned->assertOk()->assertJsonPath('body.from', '2026-10-10T16:00:00Z')->assertJsonPath('body.to', '2026-10-10T16:10:00Z');

        // Mismas horas pero en Tokio: ninguna fila.
        $tokyo = $this->postJson($this->url('/lookup'), [
            'mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10T10:00', 'to' => '2026-10-10T10:10', 'tz' => 'Asia/Tokyo',
        ]);
        $tokyo->assertOk()->assertJsonPath('body.count', 0);
    }

    public function test_lookup_window_scope_real_returns_only_real_rows_and_scope_test_only_test_rows(): void
    {
        $this->testEvent(['type' => 'telefono_header', 'event_id' => 'win-test'], '2026-10-10 16:05:00');
        $this->makeEvent(['type' => 'telefono_header', 'event_id' => 'win-real'], '2026-10-10 16:06:00');

        $admin = $this->admin();
        $window = ['mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10T10:00', 'to' => '2026-10-10T10:10'];

        $real = $this->actingAs($admin)->postJson($this->url('/lookup'), $window + ['scope' => 'real'])->assertOk();
        $real->assertJsonPath('body.count', 1)->assertJsonPath('body.events.0.is_test', false);

        $test = $this->actingAs($admin)->postJson($this->url('/lookup'), $window + ['scope' => 'test'])->assertOk();
        $test->assertJsonPath('body.count', 1)->assertJsonPath('body.events.0.is_test', true);
    }

    public function test_lookup_window_invalid_input_returns_service_error_status(): void
    {
        // Ventana mayor a 15 min: el servicio responde 422 y el lab lo transmite en http_status.
        $this->actingAs($this->admin())->postJson($this->url('/lookup'), [
            'mode' => 'window', 'type' => 'phone_click', 'from' => '2026-10-10T10:00', 'to' => '2026-10-10T12:00',
        ])->assertOk()->assertJsonPath('http_status', 422);
    }

    // ------------------------------------------------------------------ extract

    public function test_extract_distinguishes_what_n8n_would_extract(): void
    {
        $this->testEvent(['contact_folio' => 'MN7K4Q2']);
        $this->actingAs($this->admin());

        // Con espacio: la regex tolerante lo ve; la exacta de n8n, no.
        $loose = $this->postJson($this->url('/extract'), ['text' => 'Hola, me interesa. Folio: mn 7k4q2']);
        $loose->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('extraction.tolerant', 'MN7K4Q2')
            ->assertJsonPath('n8n_would_extract', false)
            ->assertJsonPath('lookup.http_status', 200)
            ->assertJsonPath('lookup.body.folio', 'MN7K4Q2');

        $exact = $this->postJson($this->url('/extract'), ['text' => 'Folio: MN7K4Q2']);
        $exact->assertOk()
            ->assertJsonPath('extraction.exact_n8n', 'MN7K4Q2')
            ->assertJsonPath('n8n_would_extract', true)
            ->assertJsonPath('lookup.http_status', 200);

        $none = $this->postJson($this->url('/extract'), ['text' => 'Hola, quiero informacion']);
        $none->assertOk()
            ->assertJsonPath('n8n_would_extract', false)
            ->assertJsonPath('lookup', null);
    }

    public function test_extract_lookup_never_exposes_real_rows(): void
    {
        $this->makeEvent(['contact_folio' => 'MN7K4Q2', 'utm_campaign' => 'cliente-real']);

        $response = $this->actingAs($this->admin())->postJson($this->url('/extract'), ['text' => 'MN7K4Q2']);

        $response->assertOk()->assertJsonPath('lookup.http_status', 404);
        $this->assertStringNotContainsString('cliente-real', $response->getContent());
    }

    public function test_extract_requires_text(): void
    {
        $this->actingAs($this->admin())->postJson($this->url('/extract'), [])->assertStatus(422)->assertJsonPath('status', 'error');
    }

    // ------------------------------------------------------------------ products-search

    private function createProductsTable(): void
    {
        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('sku')->nullable();
            $table->string('productModel')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('is_approved')->default(1);
            $table->timestamps();
        });
    }

    private function product(string $name, array $extra = []): void
    {
        DB::table('products')->insert(array_merge([
            'name' => $name,
            'slug' => strtolower(preg_replace('/\W+/', '-', $name)),
            'sku' => 'SKU-' . uniqid(),
            'status' => 1,
            'is_approved' => 1,
        ], $extra));
    }

    public function test_products_search_requires_two_characters_and_shows_only_public_products(): void
    {
        $this->createProductsTable();
        $this->product('Valvula Honeywell', ['sku' => 'VH-1']);
        $this->product('Valvula oculta', ['status' => 0]);
        $this->product('Valvula sin aprobar', ['is_approved' => 0]);
        $this->actingAs($this->admin());

        $this->getJson($this->url('/products-search?q=v'))->assertOk()->assertExactJson(['status' => 'success', 'results' => []]);
        $this->getJson($this->url('/products-search'))->assertOk()->assertJsonPath('results', []);

        $response = $this->getJson($this->url('/products-search?q=Valv'));
        $response->assertOk()->assertJsonCount(1, 'results');
        $this->assertSame(['id', 'name', 'sku', 'slug'], array_keys($response->json('results.0')));
        $this->assertSame('Valvula Honeywell', $response->json('results.0.name'));

        // Tambien busca por SKU.
        $this->getJson($this->url('/products-search?q=VH-'))->assertJsonCount(1, 'results');
    }

    public function test_products_search_escapes_like_wildcards(): void
    {
        $this->createProductsTable();
        $this->product('Tubo 100% acero');
        $this->product('Tubo 1000 acero');
        $this->product('Pieza a_b');
        $this->product('Pieza axb');
        $this->actingAs($this->admin());

        // Un '%' literal no es comodin.
        $percent = $this->getJson($this->url('/products-search') . '?q=' . urlencode('100%'));
        $percent->assertJsonCount(1, 'results');
        $this->assertSame('Tubo 100% acero', $percent->json('results.0.name'));

        // Un '_' literal tampoco.
        $underscore = $this->getJson($this->url('/products-search') . '?q=' . urlencode('a_b'));
        $underscore->assertJsonCount(1, 'results');
        $this->assertSame('Pieza a_b', $underscore->json('results.0.name'));

        // Solo comodines: no devuelve todo el catalogo.
        $this->getJson($this->url('/products-search') . '?q=' . urlencode('%%'))->assertJsonCount(0, 'results');
    }

    public function test_products_search_limits_to_15(): void
    {
        $this->createProductsTable();
        for ($i = 1; $i <= 20; $i++) {
            $this->product('Termostato ' . $i);
        }

        $this->actingAs($this->admin())->getJson($this->url('/products-search?q=Termostato'))->assertJsonCount(15, 'results');
    }

    // ------------------------------------------------------------------ events

    public function test_events_lists_only_test_rows_newest_first_with_expected_fields(): void
    {
        $this->makeEvent(['contact_folio' => 'MN7K4Q2']);
        $first = $this->testEvent(['type' => 'whatsapp_footer_oficina', 'contact_folio' => 'MN8B3C4', 'gclid' => self::GCLID, 'utm_campaign' => 'prueba', 'session_id' => 'sess-abcdef123456', 'meta' => ['placement' => 'footer_oficina', 'product_name' => 'Termostato X', 'sku' => 'T-1']]);
        $second = $this->testEvent(['type' => 'telefono_header', 'session_id' => 'sess-other', 'meta' => ['sku' => 'SKU-9']]);
        $this->makeEvent(['contact_folio' => 'MN9D5F6']);

        $response = $this->actingAs($this->admin())->getJson($this->url('/events'));

        $response->assertOk()->assertJsonPath('status', 'success')->assertJsonCount(2, 'events');
        $this->assertSame($second->id, $response->json('latest_id'));
        $this->assertSame([$second->id, $first->id], array_column($response->json('events'), 'id'));

        $row = $response->json('events.1');
        $this->assertEqualsCanonicalizing([
            'id', 'created_at', 'created_at_mx', 'event_at', 'event_at_mx', 'type', 'canonical_type', 'placement',
            'contact_folio', 'gclid_short', 'utm_campaign', 'product', 'session_short', 'attribution_source',
        ], array_keys($row));
        $this->assertSame('whatsapp_footer_oficina', $row['type']);
        $this->assertSame('whatsapp_click', $row['canonical_type']);
        $this->assertSame('footer_oficina', $row['placement']);
        $this->assertSame('MN8B3C4', $row['contact_folio']);
        $this->assertSame('TEST_GCL…', $row['gclid_short']);
        $this->assertSame('prueba', $row['utm_campaign']);
        $this->assertSame('Termostato X', $row['product']);
        $this->assertSame('sess-abc…', $row['session_short']);
        $this->assertSame('event', $row['attribution_source']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $row['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row['created_at_mx']);

        // Sin nombre de producto usa el sku.
        $this->assertSame('SKU-9', $response->json('events.0.product'));
        $this->assertSame('none', $response->json('events.0.attribution_source'));
    }

    public function test_events_after_id_returns_only_newer_and_caps_at_50(): void
    {
        $ids = [];
        for ($i = 0; $i < 55; $i++) {
            $ids[] = $this->testEvent(['contact_folio' => null, 'event_id' => 'e-' . $i])->id;
        }
        $this->actingAs($this->admin());

        $all = $this->getJson($this->url('/events'));
        $all->assertJsonCount(50, 'events');
        $this->assertSame(end($ids), $all->json('events.0.id'));
        $this->assertSame(end($ids), $all->json('latest_id'));

        $after = $this->getJson($this->url('/events?after_id=' . $ids[52]));
        $after->assertJsonCount(2, 'events');
        $this->assertSame([$ids[54], $ids[53]], array_column($after->json('events'), 'id'));

        $none = $this->getJson($this->url('/events?after_id=' . $ids[54]));
        $none->assertJsonCount(0, 'events');
        $this->assertSame($ids[54], $none->json('latest_id'));
    }

    public function test_events_with_no_rows(): void
    {
        $this->actingAs($this->admin())->getJson($this->url('/events'))
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'latest_id' => 0, 'events' => []]);
    }

    public function test_event_detail_only_for_test_rows(): void
    {
        $real = $this->makeEvent(['contact_folio' => 'MN7K4Q2', 'gclid' => self::GCLID . 'REAL']);
        $test = $this->testEvent(['contact_folio' => 'MN8B3C4', 'gclid' => self::GCLID]);
        $this->actingAs($this->admin());

        $this->getJson($this->url('/events/' . $test->id))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('event.is_test', true)
            ->assertJsonPath('event.folio', 'MN8B3C4');

        $realResponse = $this->getJson($this->url('/events/' . $real->id));
        $realResponse->assertNotFound()->assertJsonPath('status', 'error');
        $this->assertStringNotContainsString('REAL', $realResponse->getContent());

        $this->getJson($this->url('/events/999999'))->assertNotFound()->assertJsonPath('status', 'error');
        // {id} es numerico: cualquier otra cosa no entra a la ruta.
        $this->getJson($this->url('/events/abc'))->assertNotFound();
    }

    // ------------------------------------------------------------------ status

    public function test_status_shape_without_token_leaks(): void
    {
        config([
            'contact.whatsapp.number' => '5218112345678',
            'attribution.folio_enabled' => true,
            'attribution.folio_placements' => '*',
            'attribution.invisible_folio' => false,
            'attribution.test_mode.whatsapp_number' => '5218100000000',
        ]);
        $this->makeEvent(['type' => 'whatsapp_footer_oficina']);
        $this->makeEvent(['type' => 'whatsapp-flotante']);
        $this->testEvent(['type' => 'telefono_header']);

        $response = $this->actingAs($this->admin())->getJson($this->url('/status'));
        $response->assertOk()->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertEqualsCanonicalizing(
            ['test_mode', 'folio', 'token', 'whatsapp', 'settings', 'assets', 'last_event_at_mx', 'today', 'warnings'],
            array_keys($data)
        );
        $this->assertSame([
            'test_whatsapp_number' => '5218100000000',
            'test_whatsapp_source' => 'env',
            'test_whatsapp_db' => '',
            'n8n_url' => '',
        ], $data['settings']);
        $this->assertSame(['active' => false, 'expires_at_mx' => null, 'minutes_left' => null], $data['test_mode']);
        $this->assertSame(['enabled' => true, 'placements' => '*', 'invisible' => false], $data['folio']);
        $this->assertSame(['configured' => true, 'length_ok' => true], $data['token']);
        $this->assertSame([
            'production_number' => '5218112345678',
            'test_number' => '5218100000000',
            'test_number_invalid' => false,
            'effective_in_test' => '5218100000000',
        ], $data['whatsapp']);
        $this->assertEqualsCanonicalizing(['contact_js_version', 'events_js_version'], array_keys($data['assets']));
        $this->assertNotNull($data['last_event_at_mx']);
        $this->assertSame(2, $data['today']['real']['whatsapp_click'] ?? null);
        $this->assertSame(1, $data['today']['test']['phone_click'] ?? null);
        $this->assertArrayNotHasKey('phone_click', $data['today']['real']);

        // El token ni su longitud exacta aparecen en ninguna parte de la respuesta.
        $this->assertStringNotContainsString(self::TOKEN, $response->getContent());
        $this->assertStringNotContainsString((string) strlen(self::TOKEN), json_encode($data['token']));
        $this->assertArrayNotHasKey('length', $data['token']);
        $this->assertArrayNotHasKey('value', $data['token']);
    }

    public function test_status_warnings(): void
    {
        config([
            'contact.whatsapp.number' => '',
            'attribution.api_token' => 'tok-secreto-xyz',
            'attribution.invisible_folio' => true,
            'attribution.test_mode.whatsapp_number' => '123',
        ]);

        $response = $this->actingAs($this->admin())->getJson($this->url('/status'));

        $data = $response->json('data');
        $this->assertSame(['configured' => true, 'length_ok' => false], $data['token']);
        $this->assertTrue($data['whatsapp']['test_number_invalid']);
        $this->assertSame('', $data['whatsapp']['effective_in_test']);
        $this->assertNull($data['last_event_at_mx']);
        $this->assertStringNotContainsString('tok-secreto-xyz', $response->getContent());

        $messages = implode("\n", array_column($data['warnings'], 'message'));
        $this->assertStringContainsString('de producción', $messages);
        $this->assertStringContainsString('demasiado corto', $messages);
        $this->assertStringContainsString('invisible', $messages);
        $this->assertStringContainsString('ATTRIBUTION_TEST_WHATSAPP_NUMBER', $messages);
        foreach ($data['warnings'] as $warning) {
            $this->assertContains($warning['level'], ['warning', 'danger']);
        }

        // Sin token: configured false.
        config(['attribution.api_token' => null]);
        $this->getJson($this->url('/status'))->assertJsonPath('data.token', ['configured' => false, 'length_ok' => false]);
    }

    public function test_status_reports_active_test_mode_from_cookie(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->withTestCookie($admin, time() + 3000)->getJson($this->url('/status'));

        $response->assertJsonPath('data.test_mode.active', true);
        $this->assertGreaterThan(0, $response->json('data.test_mode.minutes_left'));
        $this->assertLessThanOrEqual(50, $response->json('data.test_mode.minutes_left'));
        $this->assertNotNull($response->json('data.test_mode.expires_at_mx'));
        $this->assertContains('warning', array_column($response->json('data.warnings'), 'level'));
    }

    /** Un editor con solo 'site' no ve metricas ni folios del trafico real (status y probador). */
    public function test_site_only_editor_sees_no_real_traffic_metrics_nor_real_folio_existence(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        $this->makeEvent(['type' => 'whatsapp_click', 'event_id' => 'real-1', 'contact_folio' => 'MN7K4Q2'], $now);
        $this->testEvent(['type' => 'whatsapp_click', 'event_id' => 'test-1', 'contact_folio' => 'MN8B3CD'], $now);
        $editor = $this->restrictedAdmin(['site']);

        $status = $this->actingAs($editor)->getJson($this->url('/status'))->assertOk()->json('data');
        $this->assertSame([], $status['today']['real']);
        $this->assertSame(1, $status['today']['test']['whatsapp_click'] ?? 0);

        // Folio real existente y folio inexistente responden EXACTAMENTE igual (sin oraculo de existencia).
        $existing = $this->actingAs($editor)->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN7K4Q2', 'scope' => 'test'])->json();
        $missing = $this->actingAs($editor)->postJson($this->url('/lookup'), ['mode' => 'folio', 'folio' => 'MN9F5GH', 'scope' => 'test'])->json();
        $this->assertSame($missing['http_status'], $existing['http_status']);
        $this->assertSame($missing['body'], $existing['body']);
    }
}
