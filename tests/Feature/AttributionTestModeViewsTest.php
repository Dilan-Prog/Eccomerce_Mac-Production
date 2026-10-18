<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Parciales publicos con y sin modo prueba de atribucion: analytics-tags, attribution-capture, mdn-events,
 * el numero de WhatsApp de contacto/empleos y el middleware de cabeceras dentro del grupo `web`.
 */
class AttributionTestModeViewsTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    private const REAL_NUMBER = '528100000001';
    private const TEST_NUMBER = '528199999999';

    /** Todo lo que NO debe cargarse ni enviarse en modo prueba. */
    private const FORBIDDEN_IN_TEST_MODE = [
        'googletagmanager', 'AW-16512201966', 'G-K8M2ZT5HYK', "fbq('init'", 'connect.facebook.net', 'script.google.com',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        $this->prepareAdminUsersTables();
        config([
            'contact.whatsapp.number' => self::REAL_NUMBER,
            'contact.whatsapp.url' => 'https://wa.link/f28njw',
            'attribution.test_mode.whatsapp_number' => self::TEST_NUMBER,
            'attribution.folio_enabled' => false,
            'attribution.folio_placements' => 'footer_oficina',
        ]);
    }

    private function admin(): User
    {
        return User::forceCreate([
            'name' => 'Admin Prueba', 'email' => uniqid('a') . '@example.test', 'password' => 'x', 'role' => 'admin',
        ]);
    }

    /** Pone en el contenedor un request con la cookie de prueba valida (ya "descifrada"). */
    private function actAsTestMode(?string $nonce = 'nonce-vista-1'): void
    {
        $user = $this->admin();
        $raw = \App\Support\Attribution\AttributionTestMode::encodePayload((int) $user->id, time() + 3600, (string) $nonce);
        $this->app->instance('request', Request::create('/', 'GET', [], ['mdn_attr_test' => $raw]));
    }

    private function actAsVisitor(): void
    {
        $this->app->instance('request', Request::create('/', 'GET'));
    }

    private function headHtml(): string
    {
        return view('frontend.partials.analytics-tags')->render();
    }

    private function bodyHtml(): string
    {
        return view('frontend.partials.analytics-tags', ['part' => 'body'])->render();
    }

    // ---- analytics-tags --------------------------------------------------------------------------

    public function test_analytics_tags_normal_mode_prints_everything_in_the_original_order(): void
    {
        $this->actAsVisitor();
        $head = $this->headHtml();

        $order = [
            'gtag/js?id=AW-16512201966', "gtag('config', 'AW-16512201966')", 'GTM-PX9LXWGR', 'gtag/js?id=G-K8M2ZT5HYK',
            "gtag('config', 'G-K8M2ZT5HYK')", "fbq('init', '1912650302805252')", "fbq('track', 'PageView')",
            'facebook.com/tr?id=1912650302805252', 'End Meta Pixel Code',
        ];
        $last = -1;
        foreach ($order as $needle) {
            $pos = strpos($head, $needle);
            $this->assertNotFalse($pos, "Falta {$needle}");
            $this->assertGreaterThan($last, $pos, "{$needle} fuera de orden");
            $last = $pos;
        }
        $this->assertStringContainsString('googletagmanager', $head);
        $this->assertStringContainsString('connect.facebook.net/en_US/fbevents.js', $head);
        $this->assertStringNotContainsString('window.gtag=function(){}', $head);
        $this->assertStringNotContainsString('ns.html', $head, 'El noscript de GTM va en la parte body.');

        $body = $this->bodyHtml();
        $this->assertStringContainsString('https://www.googletagmanager.com/ns.html?id=GTM-PX9LXWGR', $body);
        $this->assertStringContainsString('<noscript><iframe', $body);
    }

    public function test_analytics_tags_normal_mode_still_prints_the_google_ads_stack(): void
    {
        $this->actAsVisitor();
        $html = view('frontend.partials.analytics-tags')->render();
        // el stack se evalua al renderizar: sin @push no hay nada, pero el marcador existe en el layout
        $this->assertStringContainsString("gtag('config', 'AW-16512201966');", $html);

        // con un @push en una vista hija, el contenido sale justo despues del gtag de Ads
        $factory = $this->app['view'];
        $factory->startPush('Google-Ads', '<script>/*push-ads*/</script>');
        $html = view('frontend.partials.analytics-tags')->render();
        $this->assertStringContainsString('/*push-ads*/', $html);
        $this->assertLessThan(strpos($html, 'GTM-PX9LXWGR'), strpos($html, '/*push-ads*/'));
        $factory->flushState();
    }

    public function test_analytics_tags_in_test_mode_print_only_the_stub(): void
    {
        $this->actAsTestMode();
        $head = $this->headHtml();

        foreach (self::FORBIDDEN_IN_TEST_MODE as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $head, "No debe aparecer {$forbidden} en modo prueba");
        }
        $this->assertStringContainsString(
            '<script>window.dataLayer=window.dataLayer||[];window.gtag=function(){};window.fbq=function(){};</script>',
            $head
        );
        $this->assertSame(0, substr_count($head, '<noscript'));

        $body = $this->bodyHtml();
        $this->assertStringNotContainsString('googletagmanager', $body);
        $this->assertSame('', trim($body));
    }

    public function test_analytics_tags_do_not_print_the_google_ads_stack_in_test_mode(): void
    {
        $this->actAsTestMode();
        $factory = $this->app['view'];
        $factory->startPush('Google-Ads', "<script>gtag('event','conversion',{send_to:'AW-16512201966/x'});</script>");
        $html = $this->headHtml();
        $this->assertStringNotContainsString('AW-16512201966', $html);
        $factory->flushState();
    }

    public function test_analytics_tags_with_an_invalid_cookie_behave_as_normal(): void
    {
        $this->app->instance('request', Request::create('/', 'GET', [], ['mdn_attr_test' => 'basura']));
        $this->assertStringContainsString('googletagmanager', $this->headHtml());
        $this->assertStringNotContainsString('window.gtag=function(){}', $this->headHtml());
    }

    // ---- attribution-capture ---------------------------------------------------------------------

    public function test_attribution_capture_in_test_mode_sets_the_flag_before_capture_and_skips_sheets(): void
    {
        $this->actAsTestMode('nonce-cap-9');
        $html = view('frontend.partials.attribution-capture')->render();

        $this->assertStringContainsString('window.MDN_TEST_MODE = "nonce-cap-9"', $html);
        $this->assertStringContainsString('frontend/js/mdn-test-mode.js', $html);
        $this->assertStringContainsString('bootClear(window.MDN_TEST_MODE || null)', $html);
        // orden: bandera -> mdn-test-mode.js -> attribution.js -> captura
        $flag = strpos($html, 'window.MDN_TEST_MODE = "nonce-cap-9"');
        $lib = strpos($html, 'frontend/js/mdn-test-mode.js');
        $attr = strpos($html, 'frontend/js/attribution.js');
        $capture = strpos($html, 'MDNAttribution.capture()');
        $this->assertTrue($flag < $lib && $lib < $attr && $attr < $capture);
        // el POST de visita lleva test: 1 y el webhook de Sheets queda tras la guarda
        $this->assertStringContainsString('visitPayload.test = 1', $html);
        $this->assertStringContainsString('if (!window.MDN_TEST_MODE && (params.has(\'gclid\')', $html);
    }

    public function test_attribution_capture_in_normal_mode_has_no_flag(): void
    {
        $this->actAsVisitor();
        $html = view('frontend.partials.attribution-capture')->render();

        $this->assertStringNotContainsString('window.MDN_TEST_MODE =', $html);
        $this->assertStringContainsString('bootClear(window.MDN_TEST_MODE || null)', $html, 'En modo normal sirve para limpiar el marcador de prueba.');
        $this->assertStringContainsString('script.google.com', $html);
    }

    // ---- mdn-events ------------------------------------------------------------------------------

    /** Extrae window.MDNEventsConfig.folio del HTML renderizado. */
    private function folioConfig(string $html): array
    {
        $this->assertSame(1, preg_match('/folio:\s*(\{.*\})\s+\};/s', $html, $m), 'No se encontro MDNEventsConfig.folio');

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_mdn_events_in_test_mode_forces_folio_and_test_number(): void
    {
        $this->actAsTestMode('nonce-ev-3');
        $html = view('frontend.partials.mdn-events')->render();
        $folio = $this->folioConfig($html);

        $this->assertTrue($folio['enabled']);
        $this->assertSame('*', $folio['placements']);
        $this->assertSame(self::TEST_NUMBER, $folio['whatsapp']['number']);
        $this->assertTrue($folio['testMode']);
        $this->assertArrayHasKey('url', $folio['whatsapp']);
        $this->assertArrayHasKey('templates', $folio);
        $this->assertArrayHasKey('mail', $folio);
        $this->assertArrayHasKey('invisible', $folio);
        $this->assertStringNotContainsString(self::REAL_NUMBER, $html);
        $this->assertStringContainsString('window.MDN_TEST_MODE = "nonce-ev-3"', $html);
        $this->assertStringContainsString('MDNTestMode.banner(document)', $html);
    }

    public function test_mdn_events_in_test_mode_without_test_number_prints_an_empty_number(): void
    {
        config(['attribution.test_mode.whatsapp_number' => '']);
        $this->actAsTestMode();
        $html = view('frontend.partials.mdn-events')->render();
        $folio = $this->folioConfig($html);

        $this->assertSame('', $folio['whatsapp']['number']);
        $this->assertTrue($folio['testMode']);
        $this->assertStringNotContainsString(self::REAL_NUMBER, $html);
    }

    public function test_mdn_events_in_normal_mode_matches_the_config(): void
    {
        $this->actAsVisitor();
        $html = view('frontend.partials.mdn-events')->render();
        $folio = $this->folioConfig($html);

        $this->assertFalse($folio['enabled']);
        $this->assertSame('footer_oficina', $folio['placements']);
        $this->assertSame(self::REAL_NUMBER, $folio['whatsapp']['number']);
        $this->assertSame('https://wa.link/f28njw', $folio['whatsapp']['url']);
        $this->assertArrayNotHasKey('testMode', $folio);
        $this->assertStringNotContainsString('MDN_TEST_MODE', $html);
        $this->assertStringNotContainsString('MDNTestMode', $html);
        $this->assertStringNotContainsString(self::TEST_NUMBER, $html);

        config(['attribution.folio_enabled' => true]);
        $this->assertTrue($this->folioConfig(view('frontend.partials.mdn-events')->render())['enabled']);
    }

    // ---- numero en contacto / empleos --------------------------------------------------------------

    public function test_contact_whatsapp_number_helper_follows_the_mode(): void
    {
        $this->actAsVisitor();
        $this->assertSame(self::REAL_NUMBER, \App\Support\Attribution\AttributionTestMode::whatsappNumber());

        $this->actAsTestMode();
        $this->assertSame(self::TEST_NUMBER, \App\Support\Attribution\AttributionTestMode::whatsappNumber());

        config(['attribution.test_mode.whatsapp_number' => '']);
        $this->actAsTestMode();
        $this->assertSame('', \App\Support\Attribution\AttributionTestMode::whatsappNumber());
    }

    public function test_empleos_links_use_the_mode_number(): void
    {
        $call = function (): array {
            $controller = $this->app->make(\App\Http\Controllers\Frontend\HomeController::class);
            $view = $controller->empleos();

            return $view->getData();
        };

        $this->actAsVisitor();
        $vacantes = $call()['vacantes'] ?? collect();
        $this->assertNotEmpty($vacantes, 'config/empleos.php debe tener al menos una vacante activa.');
        $this->assertStringStartsWith('https://wa.me/' . self::REAL_NUMBER . '?text=', $vacantes->first()['wa_postular']);

        $this->actAsTestMode();
        $this->assertStringStartsWith('https://wa.me/' . self::TEST_NUMBER . '?text=', $call()['vacantes']->first()['wa_postular']);

        config(['attribution.test_mode.whatsapp_number' => '']);
        $this->actAsTestMode();
        $this->assertStringStartsWith('https://wa.me/?text=', $call()['vacantes']->first()['wa_postular']);
    }

    // ---- middleware dentro del grupo web -----------------------------------------------------------------

    public function test_web_group_adds_no_store_for_an_encrypted_test_cookie(): void
    {
        Route::middleware('web')->get('/__atribucion-prueba', fn () => 'ok');
        $user = $this->admin();

        $this->withTestModeCookie($user)->get('/__atribucion-prueba')
            ->assertOk()
            ->assertHeader('Cache-Control');
        $res = $this->withTestModeCookie($user)->get('/__atribucion-prueba');
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $res->headers->get('Cache-Control'));

        $this->unencryptedCookies = []; $this->defaultCookies = []; // las cookies persisten en esta instancia de prueba
        $res = $this->get('/__atribucion-prueba');
        $this->assertStringNotContainsString('no-store', (string) $res->headers->get('Cache-Control'));

        // cookie expirada: no cambia la respuesta
        $res = $this->withTestModeCookie($user, time() - 10)->get('/__atribucion-prueba');
        $this->assertStringNotContainsString('no-store', (string) $res->headers->get('Cache-Control'));
    }
}
