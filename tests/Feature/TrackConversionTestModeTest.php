<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\TrackConversionController;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\TrackConversion;
use App\Models\User;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\TrackConversionTableQuery;
use App\Support\AdminTable\Queries\TrackConversionTestTableQuery;
use App\Support\Attribution\AttributionTestMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Regla de is_test en POST /track-conversion (decidida SOLO en el servidor) y exclusion de las filas
 * de prueba en summary(), detailsFragment() y la tabla del admin.
 */
class TrackConversionTestModeTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    private const GCLID = 'REAL_GCLID_0123456789ABCDEF';
    private const TEST_GCLID = 'TEST_GCLID_0123456789ABCDEF';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        $this->prepareAdminUsersTables();
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Navega con la cookie de prueba ya cifrada. Se usa withUnencryptedCookie porque withCookie() de Laravel
     * vuelve a cifrar el valor (y el trait lo entrega ya cifrado). withCredentials() es obligatorio: en peticiones JSON
     * Laravel no envia cookies sin el.
     */
    private function withTestCookie(User $user, ?int $expiresAt = null): static
    {
        return $this->withCredentials()->withUnencryptedCookie(AttributionTestMode::cookieName(), $this->testModeCookieValue($user, $expiresAt));
    }

    private function makeAdmin(string $email = 'admin@test.test'): User
    {
        if ($existing = User::where('email', $email)->first()) {
            return $existing;
        }
        $user = new User();
        $user->forceFill(['name' => 'Admin', 'email' => $email, 'password' => 'secret-123', 'role' => 'admin'])->save();

        return $user;
    }

    /** Admin restringido a un rol propio con los modulos dados. */
    private function makeRestrictedAdmin(array $modules, string $email = 'restringido@test.test'): User
    {
        if ($existing = User::where('email', $email)->first()) {
            return $existing;
        }
        $role = Role::create(['name' => 'Rol ' . $email, 'slug' => 'rol-' . md5($email), 'is_system' => false]);
        foreach ($modules as $module) {
            RoleModulePermission::forceCreate(['role_id' => $role->id, 'module_key' => $module, 'can_access' => true]);
        }
        $user = new User();
        $user->forceFill(['name' => 'Staff', 'email' => $email, 'password' => 'secret-123', 'role' => 'admin', 'role_id' => $role->id])->save();

        return $user;
    }

    private function makeCustomer(): User
    {
        if ($existing = User::where('email', 'cliente@test.test')->first()) {
            return $existing;
        }
        $user = new User();
        $user->forceFill(['name' => 'Cliente', 'email' => 'cliente@test.test', 'password' => 'secret-123', 'role' => 'user'])->save();

        return $user;
    }

    private function postTrackAs(?User $user, array $payload)
    {
        $test = $user ? $this->withTestCookie($user) : $this;

        return $test->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
    }

    private function isTest(string $eventId): bool
    {
        return (bool) TrackConversion::where('event_id', $eventId)->firstOrFail()->is_test;
    }

    // ------------------------------------------------------------------ store(): regla de is_test

    public function test_cookie_and_test_flag_store_a_test_row(): void
    {
        $admin = $this->makeAdmin();

        $this->postTrackAs($admin, ['type' => 'whatsapp_click', 'event_id' => 'T-1', 'test' => 1, 'gclid' => self::GCLID])
            ->assertOk()->assertJson(['success' => true]);

        $row = TrackConversion::where('event_id', 'T-1')->firstOrFail();
        $this->assertTrue($row->is_test);
        $this->assertSame(self::GCLID, $row->gclid);
        // `test` del payload no termina en meta ni en columnas.
        $this->assertNull($row->meta);
        $this->assertArrayNotHasKey('test', $row->getAttributes());
    }

    public function test_truthy_flag_variants_are_accepted(): void
    {
        $admin = $this->makeAdmin();

        foreach ([1, true, '1', 'true'] as $i => $flag) {
            $this->postTrackAs($admin, ['type' => 'page_visit', 'event_id' => 'T-flag-' . $i, 'test' => $flag])->assertOk();
            $this->assertTrue($this->isTest('T-flag-' . $i), json_encode($flag));
        }
    }

    public function test_cookie_without_test_flag_stores_a_real_row(): void
    {
        $admin = $this->makeAdmin();

        // El admin navego una pagina publica REAL con la cookie puesta: ese evento es real.
        foreach ([[], ['test' => 0], ['test' => '0'], ['test' => false], ['test' => 'no'], ['test' => ''], ['test' => [1]]] as $i => $extra) {
            $this->postTrackAs($admin, array_merge(['type' => 'whatsapp_click', 'event_id' => 'T-real-' . $i, 'gclid' => self::GCLID], $extra))->assertOk();
            $this->assertFalse($this->isTest('T-real-' . $i), json_encode($extra));
        }
    }

    public function test_test_flag_without_cookie_returns_204_and_stores_nothing(): void
    {
        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-nocookie', 'test' => 1])
            ->assertNoContent();

        $this->assertSame(0, TrackConversion::count());
    }

    public function test_test_gclid_prefix_marks_is_test_even_without_cookie(): void
    {
        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-prefix', 'gclid' => self::TEST_GCLID])->assertOk();

        $row = TrackConversion::where('event_id', 'T-prefix')->firstOrFail();
        $this->assertTrue($row->is_test);
        $this->assertSame(self::TEST_GCLID, $row->gclid);
    }

    public function test_test_gclid_prefix_is_case_sensitive(): void
    {
        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-lower', 'gclid' => 'test_gclid_0123456789ABCDEF'])->assertOk();
        $this->assertFalse($this->isTest('T-lower'));

        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-mixed', 'gclid' => 'Test_Gclid_0123456789ABCDEF'])->assertOk();
        $this->assertFalse($this->isTest('T-mixed'));

        // Solo cuenta al inicio, no en medio del valor.
        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-middle', 'gclid' => 'XX_TEST_GCLID_0123456789ABCDEF'])->assertOk();
        $this->assertFalse($this->isTest('T-middle'));
    }

    public function test_test_gclid_prefix_with_cookie_but_without_flag_is_still_a_test_row(): void
    {
        $this->postTrackAs($this->makeAdmin(), ['type' => 'whatsapp_click', 'event_id' => 'T-prefix-cookie', 'gclid' => self::TEST_GCLID])->assertOk();

        $this->assertTrue($this->isTest('T-prefix-cookie'));
    }

    public function test_test_flag_with_real_looking_gclid_and_no_cookie_is_dropped(): void
    {
        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-spoof', 'test' => '1', 'gclid' => self::GCLID])
            ->assertNoContent();

        $this->assertSame(0, TrackConversion::count());
    }

    /** @return array<string, array{0: string}> */
    public static function invalidCookieProvider(): array
    {
        return [
            'usuario no admin' => ['customer'],
            'cookie expirada' => ['expired'],
            'usuario inexistente' => ['missing_user'],
            'admin restringido sin site' => ['no_site'],
            'cookie corrupta (texto plano)' => ['garbage'],
            'cookie cifrada pero no JSON' => ['not_json'],
            'cookie cifrada sin campos' => ['missing_fields'],
        ];
    }

    private function requestWithInvalidCookie(string $case, array $payload)
    {
        $name = AttributionTestMode::cookieName();

        switch ($case) {
            case 'customer':
                return $this->withTestCookie($this->makeCustomer())->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'expired':
                return $this->withTestCookie($this->makeAdmin(), time() - 60)->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'missing_user':
                $ghost = new User();
                $ghost->id = 987654;

                return $this->withTestCookie($ghost)->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'no_site':
                return $this->withTestCookie($this->makeRestrictedAdmin(['ads']))->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'garbage':
                return $this->withCredentials()->withUnencryptedCookie($name, 'esto-no-esta-cifrado')->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'not_json':
                $prefix = \Illuminate\Cookie\CookieValuePrefix::create($name, app('encrypter')->getKey());
                $value = app('encrypter')->encrypt($prefix . 'no es json', false);

                return $this->withCredentials()->withUnencryptedCookie($name, $value)->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
            case 'missing_fields':
                $prefix = \Illuminate\Cookie\CookieValuePrefix::create($name, app('encrypter')->getKey());
                $value = app('encrypter')->encrypt($prefix . json_encode(['u' => 1]), false);

                return $this->withCredentials()->withUnencryptedCookie($name, $value)->postJson('/track-conversion', $payload, ['Origin' => self::TRACK_ORIGIN]);
        }
        $this->fail('Caso desconocido ' . $case);
    }

    /** @dataProvider invalidCookieProvider */
    public function test_invalid_cookie_does_not_mark_test_and_flag_returns_204(string $case): void
    {
        // Sin test:1 -> fila real (la cookie invalida se ignora).
        $this->requestWithInvalidCookie($case, ['type' => 'whatsapp_click', 'event_id' => 'T-inv-real', 'gclid' => self::GCLID])->assertOk();
        $this->assertFalse($this->isTest('T-inv-real'), $case);

        // Con test:1 -> 204 sin guardar.
        $before = TrackConversion::count();
        $this->requestWithInvalidCookie($case, ['type' => 'whatsapp_click', 'event_id' => 'T-inv-flag', 'test' => 1])->assertNoContent();
        $this->assertSame($before, TrackConversion::count(), $case);
        $this->assertSame(0, TrackConversion::where('event_id', 'T-inv-flag')->count(), $case);
    }

    public function test_restricted_admin_with_site_module_activates_test_mode(): void
    {
        $staff = $this->makeRestrictedAdmin(['site']);

        $this->postTrackAs($staff, ['type' => 'whatsapp_click', 'event_id' => 'T-staff', 'test' => 1])->assertOk();

        $this->assertTrue($this->isTest('T-staff'));
    }

    // ------------------------------------------------------------------ store(): log e idempotencia

    public function test_log_only_contains_type_and_event_id_for_test_rows(): void
    {
        $admin = $this->makeAdmin();

        Log::shouldReceive('info')
            ->once()
            ->with('Conversion Rastreada', ['type' => 'whatsapp_click', 'event_id' => 'T-log']);

        $this->postTrackAs($admin, [
            'type' => 'whatsapp_click', 'event_id' => 'T-log', 'test' => 1, 'gclid' => self::TEST_GCLID, 'contact_folio' => 'MN7K4Q2',
        ])->assertOk();

        $this->assertTrue($this->isTest('T-log'));
    }

    public function test_same_event_id_is_idempotent_for_test_rows(): void
    {
        $admin = $this->makeAdmin();
        $payload = ['type' => 'whatsapp_click', 'event_id' => 'T-idem', 'test' => 1, 'contact_folio' => 'MN7K4Q2'];

        $this->postTrackAs($admin, $payload)->assertOk();
        $this->postTrackAs($admin, $payload)->assertOk();

        $this->assertSame(1, TrackConversion::where('event_id', 'T-idem')->count());
        $this->assertTrue($this->isTest('T-idem'));
    }

    public function test_retry_with_same_event_id_does_not_duplicate_nor_flip_is_test(): void
    {
        $admin = $this->makeAdmin();

        $this->postTrackAs(null, ['type' => 'whatsapp_click', 'event_id' => 'T-flip'])->assertOk();
        $this->postTrackAs($admin, ['type' => 'whatsapp_click', 'event_id' => 'T-flip', 'test' => 1])->assertOk();

        $this->assertSame(1, TrackConversion::where('event_id', 'T-flip')->count());
        $this->assertFalse($this->isTest('T-flip'));
    }

    // ------------------------------------------------------------------ summary()

    private function summary(string $range = 'all'): array
    {
        $response = app(TrackConversionController::class)->summary(Request::create('/admin/x', 'GET', ['range' => $range]));

        return $response->getData(true);
    }

    public function test_summary_does_not_count_test_rows(): void
    {
        // Reales
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'R1', 'gclid' => self::GCLID, 'utm_source' => 'google', 'utm_campaign' => 'real', 'landing_page' => '/real']);
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'R1', 'gclid' => self::GCLID, 'utm_source' => 'google', 'utm_campaign' => 'real', 'landing_page' => '/real']);
        // De prueba
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'T1', 'is_test' => true, 'gclid' => self::TEST_GCLID, 'utm_source' => 'prueba', 'utm_campaign' => 'prueba', 'landing_page' => '/prueba']);
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'T1', 'is_test' => true, 'utm_source' => 'prueba', 'utm_campaign' => 'prueba', 'landing_page' => '/prueba']);
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'T2', 'is_test' => true]);

        $data = $this->summary();

        $this->assertSame(1, $data['totals']['visits']);
        $this->assertSame(1, $data['totals']['conversions']);
        $this->assertSame(1, $data['totals']['sessions']);
        $this->assertSame(1, $data['totals']['google_visits']);
        $this->assertSame(1, $data['totals']['google_conversions']);
        $this->assertSame(['page_visit', 'whatsapp_click'], collect($data['by_type'])->pluck('type')->sort()->values()->all());
        $this->assertSame([1, 1], collect($data['by_type'])->pluck('total')->all());
        $this->assertSame(['google'], collect($data['by_source'])->pluck('source')->all());
        $this->assertSame(['real'], collect($data['by_campaign'])->pluck('campaign')->all());
        $this->assertSame(['/real'], collect($data['by_landing'])->pluck('landing_page')->all());
        $this->assertSame(1, $data['daily'][0]['visits'] ?? null);
        $this->assertCount(1, $data['daily']);
    }

    public function test_summary_quote_abandonment_ignores_test_rows_in_both_directions(): void
    {
        // Sesion real que inicio y NO genero cotizacion real, pero hay un quote_submit de PRUEBA con el mismo
        // session_id: no debe taparla (sigue abandonada).
        $this->makeEvent(['type' => 'quote_start', 'session_id' => 'SAME']);
        $this->makeEvent(['type' => 'quote_submit', 'session_id' => 'SAME', 'is_test' => true]);

        // Sesion real que si genero: no abandonada.
        $this->makeEvent(['type' => 'quote_start', 'session_id' => 'DONE']);
        $this->makeEvent(['type' => 'quote_submit', 'session_id' => 'DONE']);

        // Sesion de prueba que inicio y no genero: no cuenta como abandono.
        $this->makeEvent(['type' => 'quote_start', 'session_id' => 'TESTONLY', 'is_test' => true]);

        $totals = $this->summary()['totals'];

        $this->assertSame(2, $totals['quote_started']);
        $this->assertSame(1, $totals['quote_submitted']);
        $this->assertSame(1, $totals['quote_abandoned']);
    }

    // ------------------------------------------------------------------ detailsFragment()

    public function test_details_fragment_does_not_mix_real_and_test_sessions(): void
    {
        $realVisit = $this->makeEvent(['type' => 'page_visit', 'session_id' => 'MIX'], '2026-10-18 10:00:00');
        $realClick = $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'MIX'], '2026-10-18 10:01:00');
        $testClick = $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'MIX', 'is_test' => true], '2026-10-18 10:02:00');
        $testVisit = $this->makeEvent(['type' => 'page_visit', 'session_id' => 'MIX', 'is_test' => true], '2026-10-18 09:59:00');

        $controller = app(TrackConversionController::class);

        $forReal = $controller->detailsFragment((string) $realClick->id)->getData()['sessionEvents'];
        $this->assertSame([$realVisit->id, $realClick->id], $forReal->pluck('id')->all());

        $forTest = $controller->detailsFragment((string) $testClick->id)->getData()['sessionEvents'];
        $this->assertSame([$testVisit->id, $testClick->id], $forTest->pluck('id')->all());
    }

    public function test_details_fragment_without_session_has_no_session_events(): void
    {
        $event = $this->makeEvent(['type' => 'whatsapp_click']);

        $events = app(TrackConversionController::class)->detailsFragment((string) $event->id)->getData()['sessionEvents'];

        $this->assertCount(0, $events);
    }

    // ------------------------------------------------------------------ tabla del admin

    public function test_admin_table_excludes_test_rows_in_listing_tabs_search_and_export(): void
    {
        $this->makeEvent(['type' => 'whatsapp_click', 'event_id' => 'REAL-1', 'gclid' => self::GCLID, 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'page_visit', 'event_id' => 'REAL-2', 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'whatsapp_click', 'event_id' => 'TEST-1', 'is_test' => true, 'gclid' => self::TEST_GCLID, 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'page_visit', 'event_id' => 'TEST-2', 'is_test' => true, 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'quote_submit', 'event_id' => 'TEST-3', 'is_test' => true]);

        $table = new TrackConversionTableQuery();

        // Listado completo y pestañas
        $all = $table->paginate(new AdminTableRequest(perPage: 100));
        $this->assertSame(2, $all['total']);
        $counts = collect($all['filters'])->pluck('count', 'key');
        $this->assertSame(2, $counts['todas']);
        $this->assertSame(1, $counts['visitas']);
        $this->assertSame(1, $counts['whatsapp']);
        $this->assertSame(1, $counts['conversiones']);
        $this->assertSame(1, $counts['google']);
        $this->assertSame(0, $counts['cotizaciones']);
        $this->assertSame(1, $counts['sin_click_id']);

        // Pestaña activa + busqueda (el OR de la busqueda no debe escapar del filtro is_test)
        $searched = $table->paginate(new AdminTableRequest(perPage: 100, search: 'marca'));
        $this->assertSame(2, $searched['total']);
        $byTab = $table->paginate(new AdminTableRequest(perPage: 100, filter: 'whatsapp', search: 'marca'));
        $this->assertSame(1, $byTab['total']);

        $rowIds = collect($all['rows'])->pluck('cells.event_id')->sort()->values()->all();
        $this->assertSame(['REAL-1', 'REAL-2'], $rowIds);

        // Exportacion
        $exported = $table->exportRows(new AdminTableRequest(search: 'marca'))->pluck('event_id')->sort()->values()->all();
        $this->assertSame(['REAL-1', 'REAL-2'], $exported);
    }

    public function test_test_table_query_lists_only_test_rows(): void
    {
        $this->makeEvent(['type' => 'whatsapp_click', 'event_id' => 'REAL-1', 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'whatsapp_click', 'event_id' => 'TEST-1', 'is_test' => true, 'utm_source' => 'marca']);
        $this->makeEvent(['type' => 'page_visit', 'event_id' => 'TEST-2', 'is_test' => true]);

        $table = new TrackConversionTestTableQuery();

        $this->assertInstanceOf(TrackConversionTableQuery::class, $table);
        $this->assertSame(array_column((new TrackConversionTableQuery())->columns(), 'key'), array_column($table->columns(), 'key'));

        $all = $table->paginate(new AdminTableRequest(perPage: 100));
        $this->assertSame(2, $all['total']);
        $this->assertSame(['TEST-1', 'TEST-2'], collect($all['rows'])->pluck('cells.event_id')->sort()->values()->all());

        $searched = $table->paginate(new AdminTableRequest(perPage: 100, search: 'marca'));
        $this->assertSame(['TEST-1'], collect($searched['rows'])->pluck('cells.event_id')->all());
        $this->assertSame(1, collect($table->paginate(new AdminTableRequest(filter: 'whatsapp'))['filters'])->pluck('count', 'key')['whatsapp']);
    }
}
