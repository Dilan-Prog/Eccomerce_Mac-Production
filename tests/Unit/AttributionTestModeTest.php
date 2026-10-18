<?php

namespace Tests\Unit;

use App\Http\Middleware\NoStoreInAttributionTestMode;
use App\Models\User;
use App\Support\Attribution\AttributionTestMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * AttributionTestMode (cookie del modo prueba, numero de prueba) y su middleware de cabeceras.
 * La cookie llega ya descifrada a Request::cookie() (EncryptCookies la descifra antes), asi que aqui se
 * construye el Request con el JSON en claro.
 */
class AttributionTestModeTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable(); // guardas de SQLite en memoria + app.url
        $this->prepareAdminUsersTables();
    }

    /** Admin sin restriccion ($modules = null) o con un rol propio que solo permite $modules. */
    private function admin(?array $modules = null, string $role = 'admin'): User
    {
        $roleId = null;
        if ($modules !== null) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Rol ' . uniqid(), 'slug' => 'rol-' . uniqid(), 'is_system' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($modules as $m) {
                DB::table('role_module_permissions')->insert([
                    'role_id' => $roleId, 'module_key' => $m, 'can_access' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        return User::forceCreate([
            'name' => 'Admin Prueba',
            'email' => uniqid('admin') . '@example.test',
            'password' => 'x',
            'role' => $role,
            'role_id' => $roleId,
        ]);
    }

    private function requestWithCookie(?string $raw): Request
    {
        return Request::create('/', 'GET', [], $raw === null ? [] : [AttributionTestMode::cookieName() => $raw]);
    }

    private function cookieJson(User $user, ?int $expires = null, string $nonce = 'nonce-abc'): string
    {
        return AttributionTestMode::encodePayload((int) $user->id, $expires ?? (time() + 3600), $nonce);
    }

    // ---- active() / payload() -----------------------------------------------------------------

    public function test_valid_cookie_activates_and_returns_payload(): void
    {
        $user = $this->admin();
        $req = $this->requestWithCookie($this->cookieJson($user, time() + 600, 'nonce-xyz'));

        $this->assertTrue(AttributionTestMode::active($req));
        $p = AttributionTestMode::payload($req);
        $this->assertSame($user->id, $p['u']);
        $this->assertSame('nonce-xyz', $p['n']);
        $this->assertGreaterThan(time(), $p['e']);
    }

    public function test_no_cookie_is_not_active_and_skips_the_database(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries) { $queries++; });

        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie(null)));
        $this->assertSame(0, $queries);
    }

    public function test_expired_cookie_is_inactive(): void
    {
        $user = $this->admin();
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($this->cookieJson($user, time() - 1))));
    }

    /** @dataProvider corruptValues */
    public function test_corrupt_cookie_is_inactive(string $raw): void
    {
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($raw)));
    }

    public static function corruptValues(): array
    {
        return [
            'no es json' => ['esto-no-es-json'],
            'json escalar' => ['"hola"'],
            'sin nonce' => [json_encode(['u' => 1, 'e' => time() + 600])],
            'nonce vacio' => [json_encode(['u' => 1, 'e' => time() + 600, 'n' => ''])],
            'u no numerico' => [json_encode(['u' => 'abc', 'e' => time() + 600, 'n' => 'n'])],
            'e no numerico' => [json_encode(['u' => 1, 'e' => 'pronto', 'n' => 'n'])],
            'vacio' => [''],
        ];
    }

    public function test_cookie_of_unknown_user_is_inactive(): void
    {
        $raw = json_encode(['u' => 987654, 'e' => time() + 600, 'n' => 'n']);
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($raw)));
    }

    public function test_non_admin_user_is_inactive(): void
    {
        $customer = $this->admin(null, 'user');
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($this->cookieJson($customer))));
    }

    public function test_restricted_admin_without_site_module_is_inactive_and_with_it_is_active(): void
    {
        $noSite = $this->admin(['ads']);
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($this->cookieJson($noSite))));

        $withSite = $this->admin(['ads', 'site']);
        $this->assertTrue(AttributionTestMode::active($this->requestWithCookie($this->cookieJson($withSite))));
    }

    public function test_result_is_memoized_per_request(): void
    {
        $user = $this->admin();
        $req = $this->requestWithCookie($this->cookieJson($user));
        $this->assertTrue(AttributionTestMode::active($req));

        DB::table('users')->where('id', $user->id)->delete(); // una segunda lectura ya no encontraria al usuario
        $this->assertTrue(AttributionTestMode::active($req));
        // otro request (sin memo) si lo ve
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($this->cookieJson($user))));
    }

    // ---- cookie() / forgetCookie() / cookieDomain() ---------------------------------------------

    /** @dataProvider hosts */
    public function test_cookie_domain_matches_attribution_js(string $host, ?string $expected): void
    {
        $this->assertSame($expected, AttributionTestMode::cookieDomain($host));
    }

    public static function hosts(): array
    {
        return [
            'raiz' => ['macdelnorte.com', '.macdelnorte.com'],
            'www' => ['www.macdelnorte.com', '.macdelnorte.com'],
            'subdominio' => ['admin.macdelnorte.com', '.macdelnorte.com'],
            'mayusculas' => ['WWW.MacDelNorte.com', '.macdelnorte.com'],
            '.test' => ['foo.test', null],
            'localhost' => ['localhost', null],
            'ip' => ['127.0.0.1', null],
            'parecido' => ['notmacdelnorte.com', null],
            'macdelnorte.mx' => ['macdelnorte.mx', null],
        ];
    }

    public function test_emitted_cookie_is_httponly_lax_and_scoped(): void
    {
        config(['attribution.test_mode.ttl_minutes' => 30]);
        $user = $this->admin();
        $req = Request::create('https://www.macdelnorte.com/admin/attribution-lab/activate', 'POST');

        $cookie = AttributionTestMode::cookie($req, $user);
        $this->assertInstanceOf(SymfonyCookie::class, $cookie);
        $this->assertSame(AttributionTestMode::cookieName(), $cookie->getName());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));
        $this->assertTrue($cookie->isSecure());
        $this->assertSame('/', $cookie->getPath());
        $this->assertSame('.macdelnorte.com', $cookie->getDomain());
        $this->assertEqualsWithDelta(time() + 1800, $cookie->getExpiresTime(), 5);

        $data = json_decode($cookie->getValue(), true);
        $this->assertSame($user->id, $data['u']);
        $this->assertNotSame('', $data['n']);
        $this->assertEqualsWithDelta(time() + 1800, $data['e'], 5);
    }

    public function test_emitted_cookie_has_no_domain_on_local_hosts_and_is_not_secure_over_http(): void
    {
        $user = $this->admin();
        $cookie = AttributionTestMode::cookie(Request::create('http://foo.test/x', 'POST'), $user);
        $this->assertNull($cookie->getDomain());
        $this->assertFalse($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function test_forget_cookie_uses_same_name_path_and_domain_and_is_expired(): void
    {
        $user = $this->admin();
        foreach (['https://www.macdelnorte.com/x', 'http://foo.test/x', 'http://localhost/x'] as $url) {
            $req = Request::create($url, 'POST');
            $set = AttributionTestMode::cookie($req, $user);
            $gone = AttributionTestMode::forgetCookie($req);

            $this->assertSame($set->getName(), $gone->getName());
            $this->assertSame($set->getPath(), $gone->getPath());
            $this->assertSame($set->getDomain(), $gone->getDomain());
            $this->assertLessThan(time(), $gone->getExpiresTime());
        }
    }

    // ---- numero de WhatsApp -----------------------------------------------------------------------

    /** @dataProvider numbers */
    public function test_configured_number(?string $raw, string $expected, bool $invalid): void
    {
        config(['attribution.test_mode.whatsapp_number' => $raw]);
        $this->assertSame($expected, AttributionTestMode::configuredNumber());
        $this->assertSame($invalid, AttributionTestMode::configuredNumberInvalid());
    }

    public static function numbers(): array
    {
        return [
            'valido' => ['528112345678', '528112345678', false],
            'con formato' => ['+52 (81) 1234-5678', '528112345678', false],
            '10 digitos' => ['8112345678', '8112345678', false],
            '15 digitos' => ['123456789012345', '123456789012345', false],
            'muy corto' => ['12345', '', true],
            'muy largo' => ['1234567890123456', '', true],
            'letras' => ['no-es-numero', '', true],
            'vacio' => ['', '', false],
            'null' => [null, '', false],
            'espacios' => ['   ', '', false],
        ];
    }

    public function test_whatsapp_number_in_and_out_of_test_mode(): void
    {
        config(['contact.whatsapp.number' => '528100000001', 'attribution.test_mode.whatsapp_number' => '528199999999']);
        $user = $this->admin();
        $on = $this->requestWithCookie($this->cookieJson($user));
        $off = $this->requestWithCookie(null);

        $this->assertSame('528199999999', AttributionTestMode::whatsappNumber($on));
        $this->assertSame('528100000001', AttributionTestMode::whatsappNumber($off));

        // en modo prueba SIN numero de prueba: vacio (nunca el real)
        config(['attribution.test_mode.whatsapp_number' => '']);
        $this->assertSame('', AttributionTestMode::whatsappNumber($this->requestWithCookie($this->cookieJson($user))));
        // con numero invalido: tambien vacio
        config(['attribution.test_mode.whatsapp_number' => '123']);
        $this->assertSame('', AttributionTestMode::whatsappNumber($this->requestWithCookie($this->cookieJson($user))));
    }

    // ---- middleware ---------------------------------------------------------------------------------

    private function runMiddleware(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return (new NoStoreInAttributionTestMode())->handle($request, fn () => response('ok'));
    }

    public function test_middleware_adds_no_store_only_with_a_valid_test_cookie(): void
    {
        $user = $this->admin();

        $res = $this->runMiddleware($this->requestWithCookie($this->cookieJson($user)));
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $res->headers->get('Cache-Control'));

        // sin cookie: la respuesta no se toca
        $plain = response('ok');
        $before = $plain->headers->get('Cache-Control');
        $res = (new NoStoreInAttributionTestMode())->handle($this->requestWithCookie(null), fn () => $plain);
        $this->assertSame($before, $res->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('no-store', (string) $res->headers->get('Cache-Control'));

        // cookie expirada / corrupta: tampoco
        foreach ([$this->cookieJson($user, time() - 5), 'basura'] as $raw) {
            $res = $this->runMiddleware($this->requestWithCookie($raw));
            $this->assertStringNotContainsString('no-store', (string) $res->headers->get('Cache-Control'));
        }
    }

    /** Una cookie sin cifrar y SIN firma (la que forjaria cualquier visitante) no activa el modo prueba. */
    public function test_forged_unsigned_cookie_is_inactive(): void
    {
        $user = $this->admin();
        $forged = json_encode(['u' => $user->id, 'e' => time() + 3600, 'n' => 'x']);

        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie($forged)));
    }

    /** Firma incorrecta (otro nonce/expiracion que el firmado, o firma inventada) tampoco. */
    public function test_cookie_with_wrong_signature_is_inactive(): void
    {
        $user = $this->admin();
        $signed = json_decode($this->cookieJson($user), true);

        $tamperedExpiry = $signed;
        $tamperedExpiry['e'] = time() + 999999;
        $tamperedUser = $signed;
        $tamperedUser['u'] = $user->id + 1;
        $madeUp = $signed;
        $madeUp['s'] = str_repeat('a', 64);

        foreach ([$tamperedExpiry, $tamperedUser, $madeUp] as $payload) {
            $this->assertFalse(AttributionTestMode::active($this->requestWithCookie(json_encode($payload))));
        }
    }

    /**
     * En URL sin ruta (404) EncryptCookies no corre: la cookie legitima llega CIFRADA y se descifra a mano;
     * un valor cifrado con otro nombre de cookie (prefijo distinto) o corrupto no vale.
     */
    public function test_encrypted_cookie_is_decrypted_by_hand_when_encrypt_cookies_did_not_run(): void
    {
        $user = $this->admin();
        $encrypted = $this->testModeCookieValue($user);

        $this->assertTrue(AttributionTestMode::active($this->requestWithCookie($encrypted)));

        $otherName = \Illuminate\Cookie\CookieValuePrefix::create('otra_cookie', app('encrypter')->getKey()) . $this->cookieJson($user);
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie(app('encrypter')->encrypt($otherName, false))));
        $this->assertFalse(AttributionTestMode::active($this->requestWithCookie(substr($encrypted, 0, -6))));
    }
}
