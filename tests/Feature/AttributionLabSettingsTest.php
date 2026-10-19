<?php

namespace Tests\Feature;

use App\Models\AttributionLabSetting;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Support\Attribution\AttributionTestMode;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Ajustes del Laboratorio guardados en BD (numero de WhatsApp de prueba y URL de n8n) y el boton
 * "probar n8n". Sin RefreshDatabase: solo las tablas necesarias en SQLite en memoria.
 */
class AttributionLabSettingsTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    private const TOKEN = 'lab-test-token-9f8e7d6c5b4a39281706f5e4d3c2b1a0';
    /** IP publica literal (no requiere DNS en las pruebas). */
    private const N8N_URL = 'https://93.184.216.34/webhook-test/abcSECRETtoken123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        $this->prepareAdminUsersTables();
        config(['attribution.api_token' => self::TOKEN, 'attribution.test_mode.whatsapp_number' => '', 'attribution.test_mode.allow_private_n8n' => false]);
    }

    // ------------------------------------------------------------------ ayudas

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function restrictedAdmin(array $modules): User
    {
        $role = Role::create(['name' => 'Editor ' . uniqid(), 'slug' => 'editor-' . uniqid(), 'is_system' => false]);
        foreach ($modules as $module) {
            RoleModulePermission::create(['role_id' => $role->id, 'module_key' => $module, 'can_access' => true]);
        }

        return User::factory()->create(['role' => 'admin', 'role_id' => $role->id]);
    }

    private function url(string $path): string
    {
        return '/admin/attribution-lab' . $path;
    }

    private function save(array $body)
    {
        return $this->actingAs($this->admin())->postJson($this->url('/settings'), $body);
    }

    private function testEvent(array $attributes = [], ?string $createdAt = null)
    {
        return $this->makeEvent(array_merge(['is_test' => true], $attributes), $createdAt);
    }

    /** Pone en el contenedor un request con la cookie de prueba valida (como AttributionTestModeViewsTest). */
    private function actAsTestMode(): void
    {
        $user = $this->admin();
        $raw = AttributionTestMode::encodePayload((int) $user->id, time() + 3600, 'nonce-ajustes-1');
        $this->app->instance('request', Request::create('/', 'GET', [], ['mdn_attr_test' => $raw]));
    }

    private function asProduction(): void
    {
        // Fuera de 'testing' Laravel ya verifica CSRF: aqui no es lo que se prueba.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->app->detectEnvironment(fn () => 'production');
    }

    // ------------------------------------------------------------------ settings: numero

    public function test_saves_and_normalizes_the_whatsapp_number(): void
    {
        $response = $this->save(['test_whatsapp_number' => '+52 (81) 1234-5678']);

        $response->assertOk()
            ->assertExactJson([
                'status' => 'success',
                'message' => 'Ajustes guardados.',
                'settings' => [
                    'test_whatsapp_number' => '528112345678',
                    'test_whatsapp_source' => 'db',
                    'test_whatsapp_db' => '528112345678',
                    'n8n_url' => '',
                ],
            ]);
        $this->assertSame('528112345678', AttributionLabSetting::get('test_whatsapp_number'));
    }

    /** @dataProvider invalidNumbers */
    public function test_rejects_invalid_numbers(mixed $value): void
    {
        $response = $this->save(['test_whatsapp_number' => $value]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['message', 'errors' => ['test_whatsapp_number']]);
        $this->assertNull(AttributionLabSetting::get('test_whatsapp_number'));
    }

    public static function invalidNumbers(): array
    {
        return [
            'muy corto' => ['12345'],
            'muy largo' => ['1234567890123456'],
            'letras' => ['81-abcd-5678'],
            'mezcla' => ['528112345678x'],
            'arreglo' => [['528112345678']],
            'booleano' => [true],
        ];
    }

    public function test_empty_or_null_number_deletes_the_db_value_and_falls_back_to_env(): void
    {
        config(['attribution.test_mode.whatsapp_number' => '528100000009']);
        $this->save(['test_whatsapp_number' => '528112345678'])->assertJsonPath('settings.test_whatsapp_source', 'db');

        foreach (['', null, '  '] as $empty) {
            AttributionLabSetting::put('test_whatsapp_number', '528112345678');
            $this->save(['test_whatsapp_number' => $empty])
                ->assertOk()
                ->assertJsonPath('settings.test_whatsapp_source', 'env')
                ->assertJsonPath('settings.test_whatsapp_number', '528100000009')
                ->assertJsonPath('settings.test_whatsapp_db', '');
            $this->assertNull(AttributionLabSetting::get('test_whatsapp_number'));
        }
    }

    public function test_only_present_keys_are_touched(): void
    {
        $this->save(['test_whatsapp_number' => '528112345678', 'n8n_url' => self::N8N_URL])->assertOk();

        $this->save(['n8n_url' => null])->assertOk()
            ->assertJsonPath('settings.test_whatsapp_db', '528112345678')
            ->assertJsonPath('settings.n8n_url', '');

        $this->save(['test_whatsapp_number' => '528199999999'])->assertOk()
            ->assertJsonPath('settings.test_whatsapp_db', '528199999999');
        $this->assertNull(AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL));
    }

    public function test_empty_body_is_rejected(): void
    {
        $this->save([])->assertStatus(422)->assertJsonPath('status', 'error');
    }

    public function test_nothing_is_saved_when_one_field_is_invalid(): void
    {
        $response = $this->save(['test_whatsapp_number' => '528112345678', 'n8n_url' => 'ftp://93.184.216.34/x']);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['n8n_url']]);
        $this->assertArrayNotHasKey('test_whatsapp_number', $response->json('errors'));
        $this->assertNull(AttributionLabSetting::get('test_whatsapp_number'));
    }

    // ------------------------------------------------------------------ settings: URL de n8n

    public function test_saves_a_valid_n8n_url_trimmed(): void
    {
        $this->save(['n8n_url' => '  ' . self::N8N_URL . '  '])
            ->assertOk()
            ->assertJsonPath('settings.n8n_url', self::N8N_URL);
        $this->assertSame(self::N8N_URL, AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL));

        $this->save(['n8n_url' => 'https://n8n.example.com:5678/webhook/x?a=1'])->assertOk();
        $this->save(['n8n_url' => ''])->assertOk()->assertJsonPath('settings.n8n_url', '');
        $this->assertNull(AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL));
    }

    /** @dataProvider invalidUrls */
    public function test_rejects_invalid_n8n_urls(mixed $value): void
    {
        $response = $this->save(['n8n_url' => $value]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['message', 'errors' => ['n8n_url']]);
        $this->assertNull(AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL));
    }

    public static function invalidUrls(): array
    {
        return [
            'ftp' => ['ftp://n8n.example.com/x'],
            'javascript' => ['javascript:alert(1)'],
            'sin esquema' => ['n8n.example.com/webhook'],
            'sin host' => ['https:///webhook'],
            'credenciales' => ['https://user:pass@n8n.example.com/x'],
            'solo usuario' => ['https://user@n8n.example.com/x'],
            'fragmento' => ['https://n8n.example.com/x#frag'],
            'espacio' => ['https://n8n.example.com/mi webhook'],
            'demasiado larga' => ['https://n8n.example.com/' . str_repeat('a', 500)],
            'host numerico disfrazado' => ['https://2130706433/x'],
            'host invalido' => ['https://exa_mple!.com/x'],
            'no es texto' => [['https://n8n.example.com/x']],
        ];
    }

    public function test_http_is_only_accepted_in_local_and_testing(): void
    {
        // testing: http permitido (n8n local).
        $this->save(['n8n_url' => 'http://93.184.216.34/webhook/x'])->assertOk();
        AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, null);

        // produccion: solo https.
        $this->asProduction();
        $this->save(['n8n_url' => 'http://93.184.216.34/webhook/x'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['n8n_url']]);
        $this->assertNull(AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL));
        $this->save(['n8n_url' => self::N8N_URL])->assertOk();
    }

    public function test_settings_log_only_the_key_never_the_url(): void
    {
        $logged = [];
        Event::listen(MessageLogged::class, function (MessageLogged $e) use (&$logged) {
            $logged[] = $e->message . ' ' . json_encode($e->context);
        });

        $this->save(['n8n_url' => self::N8N_URL, 'test_whatsapp_number' => '528112345678'])->assertOk();

        $all = implode("\n", $logged);
        $this->assertStringContainsString('n8n_test_url', $all);
        $this->assertStringContainsString('test_whatsapp_number', $all);
        $this->assertStringNotContainsString('SECRET', $all);
        $this->assertStringNotContainsString('93.184.216.34', $all);
        $this->assertStringNotContainsString('528112345678', $all);
    }

    // ------------------------------------------------------------------ status y efectos

    public function test_status_includes_settings_and_keeps_whatsapp_block_coherent(): void
    {
        config(['attribution.test_mode.whatsapp_number' => '528100000009']);
        $admin = $this->admin();

        $env = $this->actingAs($admin)->getJson($this->url('/status'))->assertOk();
        $env->assertJsonPath('data.settings', [
            'test_whatsapp_number' => '528100000009',
            'test_whatsapp_source' => 'env',
            'test_whatsapp_db' => '',
            'n8n_url' => '',
        ]);
        $env->assertJsonPath('data.whatsapp.test_number', '528100000009');

        $this->save(['test_whatsapp_number' => '528112345678', 'n8n_url' => self::N8N_URL])->assertOk();
        $db = $this->actingAs($admin)->getJson($this->url('/status'))->assertOk();
        $db->assertJsonPath('data.settings.test_whatsapp_number', '528112345678')
            ->assertJsonPath('data.settings.test_whatsapp_source', 'db')
            ->assertJsonPath('data.settings.test_whatsapp_db', '528112345678')
            ->assertJsonPath('data.settings.n8n_url', self::N8N_URL)
            ->assertJsonPath('data.whatsapp.test_number', '528112345678')
            ->assertJsonPath('data.whatsapp.effective_in_test', '528112345678')
            ->assertJsonPath('data.whatsapp.test_number_invalid', false);

        $this->assertStringNotContainsString(self::TOKEN, $db->getContent());
    }

    public function test_invalid_db_number_gives_empty_effective_number_and_a_warning_without_using_env(): void
    {
        config(['attribution.test_mode.whatsapp_number' => '528100000009']);
        AttributionLabSetting::put('test_whatsapp_number', '123');

        $response = $this->actingAs($this->admin())->getJson($this->url('/status'))->assertOk();

        $response->assertJsonPath('data.settings.test_whatsapp_number', '')
            ->assertJsonPath('data.settings.test_whatsapp_source', 'db')
            ->assertJsonPath('data.settings.test_whatsapp_db', '123')
            ->assertJsonPath('data.whatsapp.test_number_invalid', true)
            ->assertJsonPath('data.whatsapp.effective_in_test', '');
        $this->assertStringContainsString('no es válido', implode("\n", array_column($response->json('data.warnings'), 'message')));
    }

    public function test_db_number_is_used_by_the_test_mode_links_and_the_mdn_events_partial(): void
    {
        config([
            'contact.whatsapp.number' => '528100000001',
            'contact.whatsapp.url' => 'https://wa.link/f28njw',
            'attribution.test_mode.whatsapp_number' => '528100000009',
        ]);
        $this->save(['test_whatsapp_number' => '528112345678'])->assertOk();

        $this->actAsTestMode();
        $this->assertSame('528112345678', AttributionTestMode::whatsappNumber());

        $html = view('frontend.partials.mdn-events')->render();
        $this->assertSame(1, preg_match('/folio:\s*(\{.*\})\s+\};/s', $html, $m));
        $folio = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('528112345678', $folio['whatsapp']['number']);
        $this->assertStringNotContainsString('528100000009', $html);
        $this->assertStringNotContainsString('528100000001', $html);

        // Fuera del modo prueba sigue el numero real.
        $this->app->instance('request', Request::create('/', 'GET'));
        $this->assertSame('528100000001', AttributionTestMode::whatsappNumber());
    }

    public function test_missing_table_does_not_break_status_and_settings_fail_with_a_clear_message(): void
    {
        config(['attribution.test_mode.whatsapp_number' => '528100000009']);
        Schema::drop('attribution_lab_settings');
        $admin = $this->admin();

        $this->actingAs($admin)->getJson($this->url('/status'))
            ->assertOk()
            ->assertJsonPath('data.settings.test_whatsapp_source', 'env')
            ->assertJsonPath('data.settings.test_whatsapp_number', '528100000009')
            ->assertJsonPath('data.settings.n8n_url', '');

        $this->actingAs($admin)->postJson($this->url('/settings'), ['test_whatsapp_number' => '528112345678'])
            ->assertStatus(500)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'php artisan migrate'));

        $this->actingAs($admin)->postJson($this->url('/n8n-test'))->assertStatus(422);
    }

    public function test_site_only_editor_can_use_the_settings(): void
    {
        $editor = $this->restrictedAdmin(['site']);

        $this->actingAs($editor)->postJson($this->url('/settings'), ['test_whatsapp_number' => '528112345678'])->assertOk();
        $this->actingAs($editor)->postJson($this->url('/n8n-test'))->assertStatus(422); // sin URL
    }

    // ------------------------------------------------------------------ n8n-test

    private function prepareN8n(string $url = self::N8N_URL): string
    {
        AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, $url);
        $this->makeEvent(['contact_folio' => 'MN7K4Q2']); // real, mas reciente que ninguno de prueba: se ignora
        $this->testEvent(['contact_folio' => 'MN8B3C4']);
        $this->testEvent(['contact_folio' => 'MN9D5F6']);
        $this->makeEvent(['contact_folio' => 'MN2B3C4']); // real, la ultima fila de la tabla

        return $url;
    }

    private function postN8n()
    {
        return $this->actingAs($this->admin())->postJson($this->url('/n8n-test'));
    }

    public function test_n8n_test_requires_a_saved_url(): void
    {
        Http::fake();
        $this->testEvent(['contact_folio' => 'MN8B3C4']);

        $this->postN8n()->assertStatus(422)->assertJsonPath('status', 'error');
        Http::assertNothingSent();
    }

    public function test_n8n_test_requires_a_test_event_with_folio(): void
    {
        Http::fake();
        AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, self::N8N_URL);
        $this->makeEvent(['contact_folio' => 'MN7K4Q2']);          // real: no cuenta
        $this->testEvent(['contact_folio' => null]);                // prueba sin folio: no cuenta
        $this->testEvent(['contact_folio' => '']);

        $this->postN8n()->assertStatus(422)
            ->assertExactJson([
                'status' => 'error',
                'message' => 'Aún no hay un evento de prueba con folio: haz clic en un botón de WhatsApp en modo prueba primero.',
            ]);
        Http::assertNothingSent();
    }

    public function test_n8n_test_sends_the_expected_payload_without_token_or_real_data(): void
    {
        Http::fake(['*' => Http::response('{"ok":true,"msg":"recibido"}', 200)]);
        $this->prepareN8n();

        $response = $this->postN8n();

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('http_status', 200)
            ->assertJsonPath('response_excerpt', '{"ok":true,"msg":"recibido"}')
            ->assertJsonPath('sent.folio', 'MN9D5F6')
            ->assertJsonPath('sent.text', 'Hola, quiero más información. Folio: MN9D5F6')
            ->assertJsonStructure(['message', 'elapsed_ms']);
        $this->assertIsInt($response->json('elapsed_ms'));
        $this->assertStringNotContainsString(self::TOKEN, $response->getContent());
        $this->assertStringNotContainsString('SECRET', $response->getContent());

        Http::assertSentCount(1);
        Http::assertSent(function (HttpRequest $request) {
            $data = $request->data();

            $this->assertSame('POST', $request->method());
            $this->assertSame(self::N8N_URL, $request->url());
            $this->assertTrue($request->isJson());
            $this->assertEqualsCanonicalizing(['source', 'is_test', 'folio', 'text', 'message_at', 'lookup_url'], array_keys($data));
            $this->assertSame('attribution-lab', $data['source']);
            $this->assertTrue($data['is_test']);
            $this->assertSame('MN9D5F6', $data['folio']);
            $this->assertSame('Hola, quiero más información. Folio: MN9D5F6', $data['text']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $data['message_at']);
            $this->assertSame(url('/api/attribution/lookup') . '?folio=MN9D5F6', $data['lookup_url']);
            // Jamas viaja el token ni un folio de cliente real.
            $this->assertStringNotContainsString(self::TOKEN, json_encode($data) . json_encode($request->headers()));
            $this->assertStringNotContainsString('MN7K4Q2', json_encode($data));
            $this->assertStringNotContainsString('MN2B3C4', json_encode($data));

            return true;
        });
    }

    public function test_n8n_test_truncates_the_response_excerpt_to_500_chars(): void
    {
        Http::fake(['*' => Http::response(str_repeat('é', 800), 200)]);
        $this->prepareN8n();

        $excerpt = $this->postN8n()->assertOk()->json('response_excerpt');

        $this->assertSame(500, mb_strlen($excerpt));
    }

    public function test_n8n_test_reports_http_errors_from_n8n(): void
    {
        Http::fake(['*' => Http::response('workflow not active', 404)]);
        $this->prepareN8n();

        $this->postN8n()->assertOk()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('http_status', 404)
            ->assertJsonPath('response_excerpt', 'workflow not active');
    }

    public function test_n8n_test_reports_network_failures_without_leaking_the_url(): void
    {
        Http::fake(fn () => throw new ConnectionException(
            'cURL error 28: Operation timed out after 10001 milliseconds (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for ' . self::N8N_URL
        ));
        $this->prepareN8n();

        $response = $this->postN8n();

        $response->assertOk()->assertJsonPath('status', 'error');
        $this->assertStringStartsWith('No se pudo conectar con n8n: ', $response->json('message'));
        $this->assertStringContainsString('timed out', $response->json('message'));
        $this->assertStringNotContainsString('SECRET', $response->getContent());
        $this->assertStringNotContainsString('https://', $response->getContent());
        $this->assertStringNotContainsString('93.184.216.34', $response->getContent());
    }

    public function test_n8n_test_does_not_follow_redirects(): void
    {
        Http::fake([
            '93.184.216.34/*' => Http::response('', 302, ['Location' => 'https://10.0.0.5/internal']),
            '*' => Http::response('SEGUIDA', 200),
        ]);
        $this->prepareN8n();

        $this->postN8n()->assertOk()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('http_status', 302);
        Http::assertSentCount(1);
    }

    public function test_n8n_test_ssrf_guard_rejects_non_public_addresses(): void
    {
        Http::fake();
        $this->testEvent(['contact_folio' => 'MN8B3C4']);

        foreach ([
            'https://127.0.0.1/x', 'https://10.0.0.5/x', 'https://192.168.1.1/x', 'https://172.16.0.9/x',
            'https://169.254.169.254/latest/meta-data', 'https://0.0.0.0/x', 'https://100.64.0.1/x',
            'https://[::1]/x', 'https://[fd00::1]/x', 'https://[::ffff:127.0.0.1]/x', 'https://[::ffff:10.0.0.5]/x',
        ] as $url) {
            AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, $url);
            $this->postN8n()->assertStatus(422)->assertJsonPath('status', 'error');
        }
        Http::assertNothingSent();
    }

    public function test_n8n_test_ssrf_guard_rejects_a_saved_http_url_in_production(): void
    {
        Http::fake();
        // Guardada cuando el entorno aun admitia http; ya en produccion se revalida y se rechaza.
        AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, 'http://93.184.216.34/webhook/x');
        $this->testEvent(['contact_folio' => 'MN8B3C4']);
        $this->asProduction();

        $this->postN8n()->assertStatus(422)->assertJsonPath('status', 'error');
        Http::assertNothingSent();
    }

    public function test_n8n_test_private_hosts_need_the_explicit_local_flag(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        $this->prepareN8n('https://127.0.0.1:5678/webhook-test/x');

        // Sin bandera: bloqueado.
        $this->postN8n()->assertStatus(422);
        Http::assertNothingSent();

        // local/testing + bandera: permitido.
        config(['attribution.test_mode.allow_private_n8n' => true]);
        $this->postN8n()->assertOk()->assertJsonPath('status', 'success');
        Http::assertSentCount(1);

        // En produccion la bandera se ignora.
        $this->asProduction();
        config(['attribution.test_mode.allow_private_n8n' => true]);
        $this->postN8n()->assertStatus(422);
        Http::assertSentCount(1);
    }

    public function test_n8n_test_rejects_a_host_that_does_not_resolve(): void
    {
        Http::fake();
        $this->prepareN8n('https://host-que-no-existe.invalid/webhook/x');

        $this->postN8n()->assertStatus(422)->assertJsonPath('status', 'error');
        Http::assertNothingSent();
    }
}
