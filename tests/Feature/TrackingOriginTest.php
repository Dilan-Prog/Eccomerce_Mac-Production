<?php

namespace Tests\Feature;

use App\Models\TrackConversion;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\TestCase;

/**
 * Middleware tracking.origin en POST /track-conversion: solo se guardan eventos del propio sitio.
 */
class TrackingOriginTest extends TestCase
{
    use CreatesTrackConversionsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable(); // app.url = https://macdelnorte.com
    }

    private function send(array $headers, string $eventId = 'TEST-origin')
    {
        // Sin el helper postTrack: aqui se controla Origin/Referer a mano.
        return $this->postJson('/track-conversion', ['type' => 'page_visit', 'event_id' => $eventId], $headers);
    }

    private function assertStored(string $eventId = 'TEST-origin'): void
    {
        $this->assertSame(1, TrackConversion::where('event_id', $eventId)->count());
    }

    private function assertNotStored(): void
    {
        $this->assertSame(0, TrackConversion::count());
    }

    public function test_site_origin_with_and_without_www_is_stored(): void
    {
        $this->send(['Origin' => 'https://www.macdelnorte.com'], 'TEST-o1')->assertOk()->assertJson(['success' => true]);
        $this->send(['Origin' => 'https://macdelnorte.com'], 'TEST-o2')->assertOk();
        $this->send(['Origin' => 'HTTPS://MacDelNorte.COM'], 'TEST-o3')->assertOk();

        $this->assertSame(3, TrackConversion::count());
    }

    public function test_br_pages_share_the_site_host(): void
    {
        // /br/* vive en el mismo dominio: el Referer de esas paginas pasa por el mismo host.
        $this->send(['Referer' => 'https://macdelnorte.com/br/honeywell-dc1010?gclid=abc'])->assertOk();
        $this->assertStored();
    }

    public function test_referer_is_the_fallback_when_there_is_no_origin(): void
    {
        $this->send(['Referer' => 'https://www.macdelnorte.com/producto/dc1010?x=1'])->assertOk();
        $this->assertStored();
    }

    public function test_origin_takes_precedence_over_referer(): void
    {
        $this->send(['Origin' => 'https://evil.com', 'Referer' => 'https://macdelnorte.com/'])->assertNoContent();
        $this->assertNotStored();
    }

    public function test_foreign_origin_gets_204_and_nothing_is_stored(): void
    {
        $response = $this->send(['Origin' => 'https://evil.com']);

        $response->assertNoContent();
        $this->assertSame('', $response->getContent());
        $this->assertNotStored();
    }

    public function test_lookalike_hosts_are_rejected(): void
    {
        foreach ([
            'https://macdelnorte.com.evil.com',
            'https://evilmacdelnorte.com',
            'https://macdelnorte.com.mx',
            'https://sub.macdelnorte.com',
            'https://xwww.macdelnorte.com',
            'https://macdelnorte.com@evil.com',
        ] as $origin) {
            $this->send(['Origin' => $origin])->assertNoContent();
        }
        $this->assertNotStored();
    }

    public function test_missing_or_unparseable_origin_and_referer_gets_204(): void
    {
        $this->send([])->assertNoContent();
        $this->send(['Origin' => 'null'])->assertNoContent();
        $this->send(['Origin' => ''])->assertNoContent();
        $this->send(['Referer' => 'no-es-una-url'])->assertNoContent();
        $this->assertNotStored();
    }

    public function test_extra_host_in_allowed_hosts_is_permitted(): void
    {
        config(['attribution.allowed_hosts' => ['Landing.Ejemplo.com']]);

        $this->send(['Origin' => 'https://landing.ejemplo.com'])->assertOk();
        $this->assertStored();

        // Exacto, no por sufijo.
        $this->send(['Origin' => 'https://otra.landing.ejemplo.com'], 'TEST-x')->assertNoContent();
        $this->send(['Origin' => 'https://landing.ejemplo.com.evil.com'], 'TEST-y')->assertNoContent();
        $this->assertSame(1, TrackConversion::count());
    }

    public function test_allowed_hosts_may_come_as_a_comma_separated_string(): void
    {
        config(['attribution.allowed_hosts' => 'a.ejemplo.com, b.ejemplo.com']);

        $this->send(['Origin' => 'https://b.ejemplo.com'])->assertOk();
    }

    public function test_switch_off_allows_everything(): void
    {
        config(['attribution.origin_check' => false]);

        $this->send([], 'TEST-s1')->assertOk();
        $this->send(['Origin' => 'https://evil.com'], 'TEST-s2')->assertOk();
        $this->assertSame(2, TrackConversion::count());
    }

    public function test_localhost_is_allowed_only_in_local_and_testing(): void
    {
        $local = ['http://localhost', 'http://localhost:8000', 'http://127.0.0.1:8080', 'http://[::1]:8000', 'http://mac.test', 'http://tienda.localhost'];

        // testing (el entorno real de esta prueba)
        foreach ($local as $i => $origin) {
            $this->send(['Origin' => $origin], 'TEST-l' . $i)->assertOk();
        }
        $this->assertSame(count($local), TrackConversion::count());

        // production simulado: nada de lo anterior pasa
        $this->app['env'] = 'production';
        foreach ($local as $i => $origin) {
            $this->send(['Origin' => $origin], 'TEST-p' . $i)->assertNoContent();
        }
        $this->assertSame(count($local), TrackConversion::count());
    }

    public function test_port_is_ignored_only_in_local_and_testing(): void
    {
        $this->send(['Origin' => 'https://macdelnorte.com:8443'], 'TEST-port1')->assertOk();

        $this->app['env'] = 'production';
        $this->send(['Origin' => 'https://macdelnorte.com:8443'], 'TEST-port2')->assertNoContent();
        $this->send(['Origin' => 'https://macdelnorte.com:443'], 'TEST-port3')->assertOk();
        $this->send(['Origin' => 'https://macdelnorte.com'], 'TEST-port4')->assertOk();
    }

    public function test_origin_check_runs_before_the_throttles(): void
    {
        // 70 peticiones ajenas con la misma sesion no consumen el limite de 60/min de la sesion.
        for ($i = 0; $i < 70; $i++) {
            $this->postJson('/track-conversion', ['type' => 'page_visit', 'session_id' => 'sess-bot'], ['Origin' => 'https://evil.com'])->assertNoContent();
        }

        $this->postTrack(['type' => 'page_visit', 'session_id' => 'sess-bot'])->assertOk();
    }
}
