<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\TrackConversion;
use Carbon\Carbon;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\TestCase;

class TrackConversionStoreTest extends TestCase
{
    use CreatesTrackConversionsTable;

    private const GCLID = 'TEST_GCLID_0123456789ABCDEF';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_old_payload_without_new_fields_still_works(): void
    {
        $this->postTrack([
            'type' => 'whatsapp_footer_oficina',
            'gclid' => self::GCLID,
            'gbraid' => null,
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'prueba',
            'landing_page' => '/',
            'referrer' => 'https://www.google.com/',
            'page_url' => 'https://macdelnorte.test/producto/x?a=1',
            'session_id' => 'sess-old-1',
            'event_id' => 'TEST-old-1',
            'captured_at' => '2026-10-01T10:00:00.000Z',
            'meta' => ['placement' => 'footer_oficina', 'href' => 'https://wa.link/f28njw', 'basura' => 'x'],
        ])->assertOk()->assertJson(['success' => true]);

        $row = TrackConversion::where('event_id', 'TEST-old-1')->firstOrFail();
        $this->assertSame('whatsapp_footer_oficina', $row->type);
        $this->assertSame(self::GCLID, $row->gclid);
        $this->assertSame('sess-old-1', $row->session_id);
        $this->assertNull($row->contact_folio);
        $this->assertNull($row->event_at);
        $this->assertSame('2026-10-01 10:00:00', $row->captured_at->format('Y-m-d H:i:s'));
        // meta filtra por META_KEYS (basura fuera)
        $this->assertSame(['placement' => 'footer_oficina', 'href' => 'https://wa.link/f28njw'], $row->meta);
    }

    public function test_minimal_payload_with_only_type_is_stored(): void
    {
        $this->postTrack(['type' => 'page_visit'])->assertOk();
        $this->assertSame(1, TrackConversion::count());
    }

    public function test_missing_or_invalid_type_returns_422_json(): void
    {
        $this->postTrack(['gclid' => self::GCLID])
            ->assertStatus(422)->assertJsonValidationErrors('type');
        $this->postTrack(['type' => ''])->assertStatus(422);
        $this->postTrack(['type' => ['a']])->assertStatus(422);
        $this->postTrack(['type' => str_repeat('x', 300)])->assertStatus(422);
        $this->assertSame(0, TrackConversion::count());
    }

    public function test_new_payload_is_stored_with_folio_event_at_and_meta(): void
    {
        Carbon::setTestNow('2026-10-18 12:00:00');

        $this->postTrack([
            'type' => 'whatsapp_click',
            'contact_folio' => '  mn7k4q2 ',
            'event_at' => '2026-10-18T11:59:30.000Z',
            'gclid' => self::GCLID,
            'utm_source' => 'google',
            'session_id' => 'sess-new-1',
            'event_id' => 'TEST-new-1',
            'captured_at' => '2026-10-18T11:00:00.000Z',
            'meta' => [
                'legacy_type' => 'whatsapp_footer_oficina',
                'placement' => 'footer_oficina',
                'sku' => 'DC1010',
                'product_name' => 'Controlador DC1010',
                'folio' => 'COT-2026-0001',
                'click_id_at' => '2026-10-10T08:00:00.000Z',
                'click_channel' => 'google',
                'touch_conflict' => '0',
                'attribution_at' => '2026-10-18T11:00:00.000Z',
            ],
        ])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-new-1')->firstOrFail();
        $this->assertSame('MN7K4Q2', $row->contact_folio);
        $this->assertSame('2026-10-18 11:59:30', $row->event_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-18 11:00:00', $row->captured_at->format('Y-m-d H:i:s'));
        $this->assertSame('whatsapp_footer_oficina', $row->meta['legacy_type']);
        $this->assertSame('google', $row->meta['click_channel']);
        $this->assertSame('0', $row->meta['touch_conflict']);
        $this->assertSame('2026-10-10T08:00:00.000Z', $row->meta['click_id_at']);
        $this->assertSame('COT-2026-0001', $row->meta['folio']);
    }

    public function test_invalid_folio_is_stored_as_null_without_rejecting_the_event(): void
    {
        foreach (['MN7K4Q', 'MN7K4Q22', 'XX7K4Q2', 'MNAEIOU', 'MN0O1I2', 'MN7K-Q2', '<script>', ['MN7K4Q2']] as $i => $bad) {
            $this->postTrack(['type' => 'whatsapp_click', 'event_id' => 'TEST-f' . $i, 'contact_folio' => $bad])->assertOk();
            $this->assertNull(TrackConversion::where('event_id', 'TEST-f' . $i)->value('contact_folio'), json_encode($bad));
        }
        $this->assertSame(8, TrackConversion::count());

        $this->postTrack(['type' => 'whatsapp_click', 'event_id' => 'TEST-empty', 'contact_folio' => ''])->assertOk();
        $this->assertNull(TrackConversion::where('event_id', 'TEST-empty')->value('contact_folio'));
    }

    public function test_duplicate_contact_folio_keeps_both_events(): void
    {
        foreach (['TEST-d1', 'TEST-d2'] as $eventId) {
            $this->postTrack([
                'type' => 'whatsapp_click', 'contact_folio' => 'MN7K4Q2', 'event_id' => $eventId, 'session_id' => 's-' . $eventId,
            ])->assertOk();
        }

        $this->assertSame(2, TrackConversion::where('contact_folio', 'MN7K4Q2')->count());
    }

    public function test_same_event_id_is_idempotent(): void
    {
        $payload = ['type' => 'whatsapp_click', 'event_id' => 'TEST-idem', 'contact_folio' => 'MN7K4Q2'];
        $this->postTrack($payload)->assertOk();
        $this->postTrack($payload)->assertOk();

        $this->assertSame(1, TrackConversion::where('event_id', 'TEST-idem')->count());
    }

    public function test_invalid_gclid_is_discarded_with_rejection_marker(): void
    {
        $cases = [
            'gtm' => 'gtm_abcdefghijklmnopqrstuvwxyz',
            'upper' => 'TEST123',
            'digits' => '123456789012345678901234',
            'spaces' => 'has space in the gclid value 123',
            'long' => str_repeat('a', 256),
        ];

        foreach ($cases as $name => $bad) {
            $this->postTrack(['type' => 'page_visit', 'event_id' => 'TEST-g-' . $name, 'gclid' => $bad, 'utm_source' => 'google'])->assertOk();

            $row = TrackConversion::where('event_id', 'TEST-g-' . $name)->firstOrFail();
            $this->assertNull($row->gclid, $name);
            $this->assertSame('google', $row->utm_source, $name);
            $this->assertSame('1', $row->meta['click_id_rejected'], $name);
            $this->assertSame(mb_substr($bad, 0, 40), $row->meta['click_id_rejected_val'], $name);
        }
    }

    public function test_gbraid_wbraid_use_the_looser_rule(): void
    {
        $this->postTrack([
            'type' => 'page_visit', 'event_id' => 'TEST-b1', 'gbraid' => '0AbCdEfGhIjK', 'wbraid' => 'short',
        ])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-b1')->firstOrFail();
        $this->assertSame('0AbCdEfGhIjK', $row->gbraid);
        $this->assertNull($row->wbraid);
        $this->assertSame('1', $row->meta['click_id_rejected']);
        $this->assertSame('short', $row->meta['click_id_rejected_val']);
    }

    public function test_valid_click_ids_leave_no_rejection_marker(): void
    {
        $this->postTrack(['type' => 'page_visit', 'event_id' => 'TEST-ok', 'gclid' => self::GCLID])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-ok')->firstOrFail();
        $this->assertSame(self::GCLID, $row->gclid);
        $this->assertNull($row->meta);
    }

    public function test_future_event_at_is_clamped_to_now(): void
    {
        Carbon::setTestNow('2026-10-18 12:00:00');

        $this->postTrack(['type' => 'whatsapp_click', 'event_id' => 'TEST-future', 'event_at' => '2026-10-19T12:00:00Z'])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-future')->firstOrFail();
        $this->assertSame('2026-10-18 12:00:00', $row->event_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-18 12:00:00', $row->created_at->format('Y-m-d H:i:s'));
    }

    public function test_very_old_event_at_is_clamped_to_seven_days(): void
    {
        Carbon::setTestNow('2026-10-18 12:00:00');

        $this->postTrack(['type' => 'whatsapp_click', 'event_id' => 'TEST-old', 'event_at' => '2026-09-01T00:00:00Z'])->assertOk();

        $this->assertSame('2026-10-11 12:00:00', TrackConversion::where('event_id', 'TEST-old')->firstOrFail()->event_at->format('Y-m-d H:i:s'));
    }

    public function test_event_at_with_offset_is_stored_in_utc(): void
    {
        Carbon::setTestNow('2026-10-18 12:00:00');

        $this->postTrack(['type' => 'whatsapp_click', 'event_id' => 'TEST-off', 'event_at' => '2026-10-18T05:30:00-06:00'])->assertOk();

        $this->assertSame('2026-10-18 11:30:00', TrackConversion::where('event_id', 'TEST-off')->firstOrFail()->event_at->format('Y-m-d H:i:s'));
    }

    public function test_invalid_dates_become_null_without_422(): void
    {
        $this->postTrack([
            'type' => 'whatsapp_click', 'event_id' => 'TEST-baddate', 'event_at' => 'ayer por la tarde', 'captured_at' => 'no-es-fecha',
        ])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-baddate')->firstOrFail();
        $this->assertNull($row->event_at);
        $this->assertNull($row->captured_at);
    }

    public function test_long_urls_are_truncated_not_rejected(): void
    {
        $this->postTrack([
            'type' => 'page_visit',
            'event_id' => 'TEST-long',
            'landing_page' => '/' . str_repeat('a', 400),
            'referrer' => 'https://x.test/' . str_repeat('b', 700),
            'page_url' => 'https://x.test/' . str_repeat('c', 700),
        ])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-long')->firstOrFail();
        $this->assertSame(255, mb_strlen($row->landing_page));
        $this->assertSame(500, mb_strlen($row->referrer));
        $this->assertSame(500, mb_strlen($row->page_url));
    }

    public function test_works_without_session_or_csrf_token(): void
    {
        // La ruta se declara sin CSRF, sesion ni errores de sesion compartidos (en pruebas el CSRF se
        // desactiva solo, por eso se comprueba la lista de middleware ademas del comportamiento).
        $route = app('router')->getRoutes()->getByName('track.conversion');
        $middleware = app('router')->gatherRouteMiddleware($route);

        foreach ([VerifyCsrfToken::class, StartSession::class, ShareErrorsFromSession::class] as $excluded) {
            $this->assertNotContains($excluded, $middleware, $excluded . ' no debe aplicar a /track-conversion');
        }
        $this->assertContains(ThrottleRequests::class . ':tracking-ip', $middleware);
        $this->assertContains(ThrottleRequests::class . ':tracking-session', $middleware);

        // POST "crudo": sin cookies, sin X-CSRF-TOKEN ni X-XSRF-TOKEN.
        $response = $this->call('POST', '/track-conversion', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_ORIGIN' => self::TRACK_ORIGIN,
        ], json_encode(['type' => 'page_visit', 'event_id' => 'TEST-nosession']));

        $response->assertOk();
        $this->assertSame(1, TrackConversion::where('event_id', 'TEST-nosession')->count());
        $this->assertEmpty($response->headers->getCookies(), 'No debe crear cookie de sesion');
    }

    public function test_text_plain_json_body_from_send_beacon_is_accepted(): void
    {
        $this->call('POST', '/track-conversion', [], [], [], ['CONTENT_TYPE' => 'text/plain;charset=UTF-8', 'HTTP_ORIGIN' => self::TRACK_ORIGIN], json_encode([
            'type' => 'whatsapp_click', 'event_id' => 'TEST-beacon', 'contact_folio' => 'MN7K4Q2',
        ]))->assertOk();

        $this->assertSame('MN7K4Q2', TrackConversion::where('event_id', 'TEST-beacon')->value('contact_folio'));
    }

    public function test_log_only_contains_type_and_event_id(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Conversion Rastreada', ['type' => 'whatsapp_click', 'event_id' => 'TEST-log']);

        $this->postTrack([
            'type' => 'whatsapp_click', 'event_id' => 'TEST-log', 'gclid' => self::GCLID, 'contact_folio' => 'MN7K4Q2',
        ])->assertOk();
    }

    /** Dos session_id que caen en cubos distintos del limitador (crc32 % 64). */
    private function sessionIdsInDifferentBuckets(): array
    {
        $a = 'sess-rl';
        for ($i = 0; ; $i++) {
            $b = 'sess-otra-' . $i;
            if (crc32($a) % 64 !== crc32($b) % 64) {
                return [$a, $b];
            }
        }
    }

    public function test_session_rate_limit_is_60_per_minute_per_session(): void
    {
        [$a, $b] = $this->sessionIdsInDifferentBuckets();

        for ($i = 0; $i < 60; $i++) {
            $this->postTrack(['type' => 'page_visit', 'session_id' => $a])->assertOk();
        }
        $this->postTrack(['type' => 'page_visit', 'session_id' => $a])->assertStatus(429);

        // Otra sesion (otro cubo) desde la misma IP sigue pasando.
        $this->postTrack(['type' => 'page_visit', 'session_id' => $b])->assertOk();
    }

    public function test_ip_rate_limit_is_300_per_minute_even_when_rotating_session_ids(): void
    {
        // La clave del limitador por sesion esta acotada (IP + 1 de 64 cubos), asi que inventar
        // session_id no crea claves ilimitadas ni es una via libre: manda el limite por IP (300/min).
        for ($i = 0; $i < 300; $i++) {
            $this->postTrack(['type' => 'page_visit', 'session_id' => 'rotado-' . $i])->assertOk();
        }
        $this->postTrack(['type' => 'page_visit', 'session_id' => 'rotado-final'])->assertStatus(429);
        $this->assertSame(300, TrackConversion::count());
    }

    public function test_requests_without_session_id_are_limited_per_ip_at_60_per_minute(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->postTrack(['type' => 'page_visit'])->assertOk();
        }
        $this->postTrack(['type' => 'page_visit'])->assertStatus(429);
    }

    public function test_session_limiter_reads_session_id_from_a_text_plain_body_with_query_string(): void
    {
        [$a, $b] = $this->sessionIdsInDifferentBuckets();
        $send = fn (string $sid) => $this->call('POST', '/track-conversion?x=1', [], [], [], ['CONTENT_TYPE' => 'text/plain;charset=UTF-8', 'HTTP_ORIGIN' => self::TRACK_ORIGIN], json_encode(['type' => 'page_visit', 'session_id' => $sid]));

        for ($i = 0; $i < 60; $i++) {
            $send($a)->assertOk();
        }
        $send($a)->assertStatus(429);
        $send($b)->assertOk();
    }

    public function test_text_plain_body_with_query_string_is_accepted(): void
    {
        $this->call('POST', '/track-conversion?utm=1', [], [], [], ['CONTENT_TYPE' => 'text/plain', 'HTTP_ORIGIN' => self::TRACK_ORIGIN], '  ' . json_encode([
            'type' => 'whatsapp_click', 'event_id' => 'TEST-beacon-q', 'contact_folio' => 'MN7K4Q2',
        ]))->assertOk();

        $this->assertSame('MN7K4Q2', TrackConversion::where('event_id', 'TEST-beacon-q')->value('contact_folio'));
    }

    public function test_far_or_relative_dates_never_cause_a_500(): void
    {
        $this->postTrack(['type' => 'x', 'event_id' => 'TEST-2099', 'captured_at' => '2099-01-01', 'event_at' => '2099-01-01T00:00:00Z'])->assertOk();
        $row = TrackConversion::where('event_id', 'TEST-2099')->firstOrFail();
        $this->assertNull($row->captured_at);
        $this->assertNull($row->event_at);

        foreach (['tomorrow', '+1 year', 'next monday', 'now', '@4102444800', '1999-12-31', '2038-01-20'] as $i => $bad) {
            $this->postTrack(['type' => 'x', 'event_id' => 'TEST-rel-' . $i, 'captured_at' => $bad, 'event_at' => $bad])->assertOk();
            $row = TrackConversion::where('event_id', 'TEST-rel-' . $i)->firstOrFail();
            $this->assertNull($row->captured_at, $bad);
            $this->assertNull($row->event_at, $bad);
        }
    }

    public function test_future_captured_at_is_clamped_to_now(): void
    {
        Carbon::setTestNow('2026-10-18 12:00:00');

        $this->postTrack(['type' => 'x', 'event_id' => 'TEST-capfuture', 'captured_at' => '2030-05-01T00:00:00Z'])->assertOk();

        $this->assertSame('2026-10-18 12:00:00', TrackConversion::where('event_id', 'TEST-capfuture')->firstOrFail()->captured_at->format('Y-m-d H:i:s'));
    }

    public function test_contact_folio_is_only_kept_for_contact_event_types(): void
    {
        $cases = [
            'whatsapp_click' => 'MN7K4Q2', 'whatsapp_footer_oficina' => 'MN7K4Q2', 'email_click' => 'MN7K4Q2',
            'correo_footer_ventas' => 'MN7K4Q2', 'phone_click' => 'MN7K4Q2', 'telefono_servicios' => 'MN7K4Q2',
            'page_visit' => null, 'quote_dismiss' => null, 'add_to_cart' => null, 'job_apply' => null, 'algo_nuevo' => null,
        ];

        foreach ($cases as $type => $expected) {
            $this->postTrack(['type' => $type, 'event_id' => 'TEST-ct-' . $type, 'contact_folio' => 'MN7K4Q2'])->assertOk();
            $this->assertSame($expected, TrackConversion::where('event_id', 'TEST-ct-' . $type)->value('contact_folio'), $type);
        }
    }

    public function test_false_meta_values_are_dropped(): void
    {
        $this->postTrack([
            'type' => 'whatsapp_click', 'event_id' => 'TEST-false',
            'meta' => ['touch_conflict' => false, 'placement' => 'flotante', 'qty' => 0, 'intent' => true],
        ])->assertOk();

        $this->assertSame(['placement' => 'flotante', 'qty' => '0', 'intent' => '1'], TrackConversion::where('event_id', 'TEST-false')->firstOrFail()->meta);
    }

    public function test_duplicate_event_id_is_logged_only_once(): void
    {
        Log::shouldReceive('info')->once()->with('Conversion Rastreada', ['type' => 'whatsapp_click', 'event_id' => 'TEST-dup-log']);

        $payload = ['type' => 'whatsapp_click', 'event_id' => 'TEST-dup-log'];
        $this->postTrack($payload)->assertOk();
        $this->postTrack($payload)->assertOk();
    }

    public function test_contact_folio_is_kept_in_quote_start_and_quote_submit_only(): void
    {
        foreach (['quote_start' => 'MN7K4Q2', 'quote_submit' => 'MN7K4Q2', 'quote_dismiss' => null, 'add_to_cart' => null, 'add_to_cart_blocked' => null, 'page_visit' => null] as $type => $expected) {
            $this->postTrack(['type' => $type, 'event_id' => 'TEST-qf-' . $type, 'contact_folio' => 'mn7k4q2'])->assertOk();
            $this->assertSame($expected, TrackConversion::where('event_id', 'TEST-qf-' . $type)->value('contact_folio'), $type);
        }
    }

    public function test_quote_submit_keeps_both_the_mn_folio_and_the_cot_folio(): void
    {
        $this->postTrack([
            'type' => 'quote_submit', 'event_id' => 'TEST-qs', 'contact_folio' => 'MN7K4Q2', 'meta' => ['folio' => 'COT-2026-0042'],
        ])->assertOk();

        $row = TrackConversion::where('event_id', 'TEST-qs')->firstOrFail();
        $this->assertSame('MN7K4Q2', $row->contact_folio);
        $this->assertSame('COT-2026-0042', $row->meta['folio']);
    }
}
