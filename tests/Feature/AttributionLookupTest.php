<?php

namespace Tests\Feature;

use App\Models\TrackConversion;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\TestCase;

class AttributionLookupTest extends TestCase
{
    use CreatesTrackConversionsTable;

    private const TOKEN = 'token-de-prueba-unitaria-con-mas-de-32-caracteres';
    private const GCLID = 'TEST_GCLID_0123456789ABCDEF';

    /** Claves exactas de la respuesta por folio (el orden no importa, el conjunto si). */
    private const FOLIO_KEYS = [
        'folio', 'event_id', 'session_id', 'type', 'placement', 'event_at', 'captured_at', 'received_at',
        'gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'landing_page', 'page_url', 'referrer_host', 'product', 'attribution_source', 'has_google_click_id',
        'google_click_id_type', 'click_id_at', 'click_age_days', 'click_channel', 'touch_conflict',
        'session_has_fresh_click', 'meta', 'ambiguous', 'candidates', 'message_at', 'message_to_event_seconds',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        config(['attribution.api_token' => self::TOKEN]);
    }

    private function lookup(string $query, ?string $token = self::TOKEN)
    {
        return $this->getJson('/api/attribution/lookup?' . $query, $token ? ['Authorization' => 'Bearer ' . $token] : []);
    }

    /** Evento de contacto con click id de Google, listo para consultar por folio. */
    private function contact(array $overrides = [], ?string $createdAt = '2026-10-18 12:00:00'): TrackConversion
    {
        return $this->makeEvent(array_merge([
            'type' => 'whatsapp_click',
            'contact_folio' => 'MN7K4Q2',
            'event_id' => 'TEST-' . uniqid(),
            'session_id' => 'sess-1',
            'gclid' => self::GCLID,
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'Avatra_Pmax',
        ], $overrides), $createdAt);
    }

    // ---------------------------------------------------------------- autenticacion

    public function test_401_without_token(): void
    {
        $this->contact();

        $this->lookup('folio=MN7K4Q2', null)
            ->assertStatus(401)
            ->assertExactJson(['status' => 'error', 'message' => 'Token inválido o revocado.'])
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_401_with_wrong_token_or_token_in_query_string(): void
    {
        $this->contact();

        $this->lookup('folio=MN7K4Q2', 'otro-token')->assertStatus(401);
        // Solo Authorization: Bearer; el token por query string no se acepta.
        $this->getJson('/api/attribution/lookup?folio=MN7K4Q2&token=' . self::TOKEN)->assertStatus(401);
        // Bearer con esquema distinto tampoco.
        $this->getJson('/api/attribution/lookup?folio=MN7K4Q2', ['Authorization' => 'Basic ' . self::TOKEN])->assertStatus(401);
    }

    public function test_503_when_token_is_not_configured(): void
    {
        config(['attribution.api_token' => null]);
        $this->contact();

        $this->lookup('folio=MN7K4Q2', 'cualquier-cosa')->assertStatus(503)->assertHeader('Cache-Control', 'no-store, private');

        config(['attribution.api_token' => '']);
        $this->lookup('folio=MN7K4Q2', '')->assertStatus(503);
        $this->lookup('folio=MN7K4Q2', null)->assertStatus(503);
    }

    public function test_503_when_token_is_shorter_than_32_characters(): void
    {
        $this->contact();
        $short = str_repeat('a', 31);
        config(['attribution.api_token' => $short]);

        $this->lookup('folio=MN7K4Q2', $short)->assertStatus(503);

        $ok = str_repeat('a', 32);
        config(['attribution.api_token' => $ok]);
        $this->lookup('folio=MN7K4Q2', $ok)->assertOk();
    }

    public function test_empty_bearer_is_401(): void
    {
        $this->contact();

        $this->getJson('/api/attribution/lookup?folio=MN7K4Q2', ['Authorization' => 'Bearer '])->assertStatus(401);
        $this->getJson('/api/attribution/lookup?folio=MN7K4Q2', ['Authorization' => 'Bearer'])->assertStatus(401);
    }

    public function test_responses_are_never_cacheable(): void
    {
        $this->contact();

        $this->lookup('folio=MN7K4Q2')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->lookup('folio=MN999ZZ')->assertNotFound()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_throttle_runs_before_the_token_check(): void
    {
        // 120 por minuto por IP; la 121 responde 429 aunque el token sea invalido (el limitador va primero).
        for ($i = 0; $i < 120; $i++) {
            $this->lookup('folio=MN7K4Q2', 'mal-token')->assertStatus(401);
        }
        $this->lookup('folio=MN7K4Q2', 'mal-token')->assertStatus(429);
        $this->lookup('folio=MN7K4Q2')->assertStatus(429);
    }

    // ---------------------------------------------------------------- por folio

    public function test_404_when_folio_does_not_exist(): void
    {
        $this->contact();

        $this->lookup('folio=MN999ZZ')
            ->assertStatus(404)
            ->assertExactJson(['status' => 'error', 'message' => 'Folio no encontrado.']);
    }

    public function test_422_when_nothing_to_look_up_is_given(): void
    {
        $this->lookup('')->assertStatus(422);
        $this->lookup('folio=')->assertStatus(422);
    }

    public function test_folio_found_returns_the_exact_key_set_and_values(): void
    {
        $this->contact([
            'session_id' => 'sess-1',
            'referrer' => 'https://www.google.com/search?q=controlador+dc1010&secreto=1',
            'landing_page' => '/producto/dc1010?gclid=' . self::GCLID . '&utm_source=google',
            'page_url' => 'https://macdelnorte.test/producto/dc1010?gclid=' . self::GCLID . '#galeria',
            'captured_at' => '2026-10-18 11:00:00',
            'event_at' => '2026-10-18 11:59:50',
            'meta' => [
                'sku' => 'DC1010', 'product_name' => 'Controlador DC1010', 'placement' => 'flotante',
                'legacy_type' => 'whatsapp-flotante', 'folio' => 'COT-2026-0001',
                'click_id_at' => '2026-10-08T11:00:00.000Z', 'click_channel' => 'google', 'touch_conflict' => '0',
            ],
        ]);

        $response = $this->lookup('folio=mn7k4q2')->assertOk();
        $json = $response->json();

        $expected = self::FOLIO_KEYS;
        $actual = array_keys($json);
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);

        $this->assertSame(['sku', 'name'], array_keys($json['product']));
        $this->assertSame(['sku', 'product_name', 'placement', 'legacy_type', 'folio'], array_keys($json['meta']));

        $this->assertSame('MN7K4Q2', $json['folio']);
        $this->assertSame('whatsapp_click', $json['type']);
        $this->assertSame('flotante', $json['placement']);
        $this->assertSame('2026-10-18T11:59:50Z', $json['event_at']);
        $this->assertSame('2026-10-18T11:00:00Z', $json['captured_at']);
        $this->assertSame('2026-10-18T12:00:00Z', $json['received_at']);
        $this->assertSame(self::GCLID, $json['gclid']);
        $this->assertNull($json['gbraid']);
        $this->assertSame('google', $json['utm_source']);
        $this->assertSame('Avatra_Pmax', $json['utm_campaign']);
        $this->assertSame('/producto/dc1010', $json['landing_page']);
        $this->assertSame('https://macdelnorte.test/producto/dc1010', $json['page_url']);
        $this->assertSame('www.google.com', $json['referrer_host']);
        $this->assertSame(['sku' => 'DC1010', 'name' => 'Controlador DC1010'], $json['product']);
        $this->assertSame('event', $json['attribution_source']);
        $this->assertTrue($json['has_google_click_id']);
        $this->assertSame('gclid', $json['google_click_id_type']);
        $this->assertSame('2026-10-08T11:00:00Z', $json['click_id_at']);
        $this->assertSame(11, $json['click_age_days'], 'ceil: 10 dias y casi 1 hora -> 11');
        $this->assertSame('google', $json['click_channel']);
        $this->assertFalse($json['touch_conflict']);
        $this->assertFalse($json['session_has_fresh_click']);
        $this->assertFalse($json['ambiguous']);
        $this->assertSame(1, $json['candidates']);
        $this->assertNull($json['message_at']);
        $this->assertNull($json['message_to_event_seconds']);
        $this->assertSame('whatsapp-flotante', $json['meta']['legacy_type']);
    }

    public function test_event_at_falls_back_to_created_at_and_missing_meta_defaults(): void
    {
        $this->contact(['gbraid' => null, 'gclid' => null, 'wbraid' => '0AbCdEfGhIjK'], '2026-10-18 12:00:00');

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('2026-10-18T12:00:00Z', $json['event_at']);
        $this->assertNull($json['captured_at']);
        $this->assertSame('wbraid', $json['google_click_id_type']);
        $this->assertNull($json['click_id_at']);
        $this->assertNull($json['click_age_days']);
        $this->assertNull($json['click_channel']);
        $this->assertFalse($json['touch_conflict']);
    }

    public function test_touch_conflict_comes_from_meta(): void
    {
        $this->contact(['meta' => ['touch_conflict' => '1', 'click_channel' => 'meta']]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();
        $this->assertTrue($json['touch_conflict']);
        $this->assertSame('meta', $json['click_channel']);
    }

    public function test_message_at_adds_message_to_event_seconds(): void
    {
        $this->contact(['event_at' => '2026-10-18 11:59:30']);

        $json = $this->lookup('folio=MN7K4Q2&message_at=' . urlencode('2026-10-18T06:01:00-06:00'))->assertOk()->json();

        $this->assertSame('2026-10-18T12:01:00Z', $json['message_at']);
        $this->assertSame(90, $json['message_to_event_seconds']);

        $this->lookup('folio=MN7K4Q2&message_at=2026-10-18T12:01:00')->assertStatus(422);
    }

    public function test_cot_folio_resolves_through_meta_folio(): void
    {
        $this->contact(['type' => 'quote_submit', 'contact_folio' => null, 'meta' => ['folio' => 'COT-2026-0042']]);
        $this->contact(['type' => 'whatsapp_click', 'contact_folio' => 'MN2222B', 'meta' => ['folio' => 'COT-2026-0099']]);

        $json = $this->lookup('folio=cot-2026-0042')->assertOk()->json();

        $this->assertSame('COT-2026-0042', $json['folio']);
        $this->assertSame('quote_submit', $json['type']);
        $this->assertSame('COT-2026-0042', $json['meta']['folio']);
        $this->assertSame('event', $json['attribution_source']);

        $this->lookup('folio=COT-2026-0001')->assertStatus(404);
    }

    // ---------------------------------------------------------------- resolucion de atribucion

    public function test_session_fallback_uses_an_earlier_event_of_the_same_session(): void
    {
        // Visita previa con el click id; el contacto no trae nada.
        $this->makeEvent([
            'type' => 'page_visit', 'session_id' => 'sess-9', 'gclid' => self::GCLID,
            'utm_source' => 'google', 'utm_campaign' => 'McDonnell-Miller-Pmax',
            'meta' => ['click_id_at' => '2026-10-18T10:59:00.000Z', 'click_channel' => 'google'],
        ], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-9', 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('session', $json['attribution_source']);
        $this->assertSame(self::GCLID, $json['gclid']);
        $this->assertSame('McDonnell-Miller-Pmax', $json['utm_campaign']);
        $this->assertTrue($json['has_google_click_id']);
        $this->assertTrue($json['session_has_fresh_click']);
        $this->assertSame('2026-10-18T10:59:00Z', $json['click_id_at']);
        $this->assertSame(1, $json['click_age_days']);
    }

    public function test_session_fallback_ignores_later_events_and_other_sessions(): void
    {
        $contact = $this->contact(['session_id' => 'sess-9', 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);
        // Posterior al contacto (id mayor) y de otra sesion: no cuentan.
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-9', 'gclid' => self::GCLID, 'utm_source' => 'google']);
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-otra', 'gclid' => self::GCLID . 'X']);

        $this->assertLessThan(TrackConversion::max('id'), $contact->id);
        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('none', $json['attribution_source']);
        $this->assertNull($json['gclid']);
    }

    public function test_none_when_there_is_no_attribution_anywhere(): void
    {
        $this->contact(['session_id' => null, 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('none', $json['attribution_source']);
        $this->assertNull($json['gclid']);
        $this->assertNull($json['utm_source']);
        $this->assertFalse($json['has_google_click_id']);
        $this->assertNull($json['google_click_id_type']);
        $this->assertFalse($json['session_has_fresh_click']);
        $expected = self::FOLIO_KEYS;
        $actual = array_keys($json);
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual, 'Con atribucion none la respuesta conserva las mismas claves');
    }

    public function test_utm_only_counts_as_event_attribution(): void
    {
        $this->contact(['gclid' => null, 'utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();
        $this->assertSame('event', $json['attribution_source']);
        $this->assertFalse($json['has_google_click_id']);
        $this->assertSame('newsletter', $json['utm_source']);
    }

    public function test_invalid_click_ids_are_treated_as_absent(): void
    {
        // Datos viejos: gclid de GTM / numerico / de prueba. Sin UTM -> no hay atribucion propia.
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-bad', 'gclid' => 'gtm_abcdefghijklmnopqrstuvwxyz'], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-bad', 'gclid' => 'TEST123', 'gbraid' => 'short', 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('none', $json['attribution_source']);
        $this->assertNull($json['gclid']);
        $this->assertNull($json['gbraid']);
        $this->assertFalse($json['has_google_click_id']);
    }

    public function test_invalid_gclid_with_utm_keeps_utm_but_not_the_click_id(): void
    {
        $this->contact(['gclid' => '1234567890123456789012', 'utm_source' => 'google', 'utm_campaign' => 'x']);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('event', $json['attribution_source']);
        $this->assertNull($json['gclid']);
        $this->assertFalse($json['has_google_click_id']);
        $this->assertSame('google', $json['utm_source']);
    }

    // ---------------------------------------------------------------- ambiguedad

    public function test_same_folio_in_two_different_sessions_is_ambiguous(): void
    {
        $this->contact(['session_id' => 'sess-a', 'event_id' => 'TEST-a'], '2026-10-18 12:00:00');
        $this->contact(['session_id' => 'sess-b', 'event_id' => 'TEST-b'], '2026-10-18 12:05:00');

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();
        $this->assertTrue($json['ambiguous']);
        $this->assertSame(2, $json['candidates']);
        $this->assertSame('TEST-b', $json['event_id'], 'Sin message_at devuelve el mas reciente');

        // Con message_at devuelve el evento mas cercano al mensaje.
        $near = $this->lookup('folio=MN7K4Q2&message_at=' . urlencode('2026-10-18T12:00:20Z'))->assertOk()->json();
        $this->assertSame('TEST-a', $near['event_id']);
        $this->assertSame(20, $near['message_to_event_seconds']);
        $this->assertTrue($near['ambiguous']);
    }

    public function test_same_folio_twice_in_the_same_session_is_not_ambiguous(): void
    {
        $this->contact(['session_id' => 'sess-a', 'event_id' => 'TEST-a'], '2026-10-18 12:00:00');
        $this->contact(['session_id' => 'sess-a', 'event_id' => 'TEST-b'], '2026-10-18 12:00:05');

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();
        $this->assertFalse($json['ambiguous']);
        $this->assertSame(1, $json['candidates']);
    }

    // ---------------------------------------------------------------- por hora

    public function test_time_mode_requires_zone_and_a_window_of_at_most_15_minutes(): void
    {
        $this->lookup('type=phone_click&from=2026-10-18T12:00:00&to=2026-10-18T12:05:00')->assertStatus(422);
        $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:05:00')->assertStatus(422);
        $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:15:01Z')->assertStatus(422);
        $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:15:00Z')->assertOk();
        // Falta algun parametro
        $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z')->assertStatus(422);
        $this->lookup('from=2026-10-18T12:00:00Z&to=2026-10-18T12:05:00Z')->assertStatus(422);
        $this->lookup('type=phone_click&to=2026-10-18T12:05:00Z')->assertStatus(422);
        // to antes que from
        $this->lookup('type=phone_click&from=2026-10-18T12:05:00Z&to=2026-10-18T12:00:00Z')->assertStatus(422);
        // basura
        $this->lookup('type=phone_click&from=ayer&to=hoy')->assertStatus(422);
    }

    public function test_time_mode_accepts_offsets_including_an_unencoded_plus(): void
    {
        $this->contact(['type' => 'telefono_contactos', 'contact_folio' => null], '2026-10-18 12:00:00');

        $this->lookup('type=phone_click&from=' . urlencode('2026-10-18T06:55:00-06:00') . '&to=' . urlencode('2026-10-18T06:05:00-06:00'))->assertStatus(422);

        $ok = $this->lookup('type=phone_click&from=' . urlencode('2026-10-18T05:55:00-06:00') . '&to=' . urlencode('2026-10-18T06:05:00-06:00'))->assertOk()->json();
        $this->assertSame(1, $ok['count']);

        // "+" sin codificar llega como espacio al servidor.
        $plus = $this->lookup('type=phone_click&from=2026-10-18T16:55:00+05:00&to=2026-10-18T17:05:00+05:00')->assertOk()->json();
        $this->assertSame(1, $plus['count']);
    }

    public function test_time_mode_returns_events_of_the_canonical_type_ordered_by_closeness(): void
    {
        $this->contact(['type' => 'telefono_contactos', 'contact_folio' => null, 'event_id' => 'TEST-p1', 'session_id' => 's1'], '2026-10-18 12:00:00');
        $this->contact(['type' => 'phone_click', 'contact_folio' => null, 'event_id' => 'TEST-p2', 'session_id' => 's2'], '2026-10-18 12:04:00');
        $this->contact(['type' => 'telefono_servicios', 'contact_folio' => null, 'event_id' => 'TEST-p3', 'session_id' => 's3'], '2026-10-18 12:02:00');
        // Otros tipos o fuera de la ventana: no salen.
        $this->contact(['type' => 'whatsapp_click', 'contact_folio' => null, 'event_id' => 'TEST-w'], '2026-10-18 12:03:00');
        $this->contact(['type' => 'phone_click', 'contact_folio' => null, 'event_id' => 'TEST-late'], '2026-10-18 12:10:00');
        $this->contact(['type' => 'phone_click', 'contact_folio' => null, 'event_id' => 'TEST-early'], '2026-10-18 11:50:00');

        $json = $this->lookup('type=phone_click&from=2026-10-18T11:58:00Z&to=2026-10-18T12:05:00Z')->assertOk()->json();

        $this->assertSame('phone_click', $json['type']);
        $this->assertSame(3, $json['count']);
        $this->assertSame(['TEST-p2', 'TEST-p3', 'TEST-p1'], array_column($json['events'], 'event_id'));
        $this->assertSame([60, 180, 300], array_column($json['events'], 'seconds_before_call'));
        $this->assertSame(['time_window', 'time_window', 'time_window'], array_column($json['events'], 'match_type'));
        $this->assertSame('phone_click', $json['events'][0]['type']);
        $this->assertSame('phone_click', $json['events'][2]['type'], 'telefono_contactos se devuelve como tipo canonico');

        $keys = array_merge(array_diff(self::FOLIO_KEYS, ['ambiguous', 'candidates', 'message_at', 'message_to_event_seconds']), ['seconds_before_call', 'match_type']);
        $actual = array_keys($json['events'][0]);
        sort($keys);
        sort($actual);
        $this->assertSame($keys, $actual);
    }

    public function test_time_mode_returns_at_most_20_events(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->contact(['type' => 'phone_click', 'contact_folio' => null, 'event_id' => 'TEST-m' . $i, 'session_id' => 's' . $i], '2026-10-18 12:' . sprintf('%02d', $i % 10) . ':' . sprintf('%02d', $i));
        }

        $json = $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:10:00Z')->assertOk()->json();

        $this->assertSame(20, $json['count']);
        $this->assertCount(20, $json['events']);
    }

    public function test_time_mode_uses_the_browser_event_time_when_the_event_arrived_late(): void
    {
        // Reenviado desde la cola del navegador: created_at es horas despues del clic real.
        $this->contact(['type' => 'phone_click', 'contact_folio' => null, 'event_id' => 'TEST-q', 'event_at' => '2026-10-18 12:01:00'], '2026-10-18 15:00:00');

        $json = $this->lookup('type=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:05:00Z')->assertOk()->json();

        $this->assertSame(1, $json['count']);
        $this->assertSame(240, $json['events'][0]['seconds_before_call']);
    }

    // ---------------------------------------------------------------- resolucion de sesion (casos extra)

    public function test_session_fallback_picks_the_most_recent_earlier_event_with_attribution(): void
    {
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-r', 'gclid' => 'OLD_GCLID_0123456789ABCDEFG', 'utm_source' => 'google'], '2026-10-18 10:00:00');
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-r', 'gclid' => 'NEW_GCLID_0123456789ABCDEFG', 'utm_source' => 'google'], '2026-10-18 10:30:00');
        // Posterior sin atribucion util (click id invalido y sin utm): se salta.
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-r', 'gclid' => 'TEST123'], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-r', 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('session', $json['attribution_source']);
        $this->assertSame('NEW_GCLID_0123456789ABCDEFG', $json['gclid']);
    }

    public function test_session_fallback_ignores_events_with_null_session_id(): void
    {
        // Filas legacy sin sesion no se pueden unir entre si.
        $this->makeEvent(['type' => 'page_visit', 'session_id' => null, 'gclid' => self::GCLID, 'utm_source' => 'google'], '2026-10-18 10:00:00');
        $this->contact(['session_id' => null, 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('none', $json['attribution_source']);
        $this->assertNull($json['gclid']);
    }

    public function test_touch_conflict_and_channel_are_inherited_from_the_earlier_event(): void
    {
        $this->makeEvent([
            'type' => 'page_visit', 'session_id' => 'sess-t', 'gclid' => self::GCLID, 'utm_source' => 'google',
            'meta' => ['touch_conflict' => '1', 'click_channel' => 'google', 'click_id_at' => '2026-10-18T10:00:00.000Z'],
        ], '2026-10-18 10:00:00');
        $this->contact(['session_id' => 'sess-t', 'gclid' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null, 'meta' => ['placement' => 'flotante']]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('session', $json['attribution_source']);
        $this->assertTrue($json['touch_conflict']);
        $this->assertSame('google', $json['click_channel']);
        $this->assertSame('flotante', $json['placement'], 'El placement es del evento, no de la fila previa');
    }

    public function test_session_has_fresh_click_is_false_for_a_sticky_gclid_from_an_old_click(): void
    {
        // El visitante volvio a los 60 dias: attribution-capture manda un page_visit con el gclid pegajoso
        // al inicio de la sesion, pero el click id entro hace 60 dias, no en esta sesion.
        $this->makeEvent([
            'type' => 'page_visit', 'session_id' => 'sess-s', 'gclid' => self::GCLID, 'utm_source' => 'google',
            'meta' => ['click_id_at' => '2026-08-19T10:00:00.000Z', 'click_channel' => 'google'],
        ], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-s', 'meta' => ['click_id_at' => '2026-08-19T10:00:00.000Z']]);

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertTrue($json['has_google_click_id']);
        $this->assertFalse($json['session_has_fresh_click']);
        $this->assertSame(61, $json['click_age_days']);
    }

    public function test_session_has_fresh_click_is_true_when_the_click_id_entered_this_session(): void
    {
        // click_id_at 3 min antes de la primera fila de la sesion (margen de 5 min) -> fresco.
        $this->makeEvent([
            'type' => 'page_visit', 'session_id' => 'sess-f', 'gclid' => self::GCLID, 'utm_source' => 'google',
            'meta' => ['click_id_at' => '2026-10-18T10:57:00.000Z'],
        ], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-f', 'meta' => ['click_id_at' => '2026-10-18T10:57:00.000Z']]);

        $this->assertTrue($this->lookup('folio=MN7K4Q2')->assertOk()->json('session_has_fresh_click'));
    }

    public function test_session_has_fresh_click_needs_click_id_at_and_a_previous_page_visit(): void
    {
        // Sin click_id_at en meta -> false aunque haya page_visit previo con el mismo gclid.
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'sess-n', 'gclid' => self::GCLID, 'utm_source' => 'google'], '2026-10-18 11:00:00');
        $this->contact(['session_id' => 'sess-n']);
        $this->assertFalse($this->lookup('folio=MN7K4Q2')->assertOk()->json('session_has_fresh_click'));

        // Con click_id_at reciente pero sin page_visit previo del mismo gclid -> false.
        $this->contact(['session_id' => 'sess-m', 'contact_folio' => 'MN2222B', 'meta' => ['click_id_at' => '2026-10-18T11:59:00.000Z']]);
        $this->assertFalse($this->lookup('folio=MN2222B')->assertOk()->json('session_has_fresh_click'));
    }

    public function test_click_age_days_rounds_up_and_ignores_a_click_after_the_event(): void
    {
        $this->contact(['meta' => ['click_id_at' => '2026-10-18T11:59:00.000Z']]);
        $this->assertSame(1, $this->lookup('folio=MN7K4Q2')->json('click_age_days'), '1 minuto -> 1 dia (ceil)');

        // Hasta 5 min de adelanto se toma como 0 dias.
        $this->contact(['contact_folio' => 'MN3333B', 'meta' => ['click_id_at' => '2026-10-18T12:03:00.000Z']]);
        $this->assertSame(0, $this->lookup('folio=MN3333B')->json('click_age_days'));

        // Mas de 5 min posterior al evento: reloj forjado -> null.
        $this->contact(['contact_folio' => 'MN4444B', 'meta' => ['click_id_at' => '2026-10-18T12:06:00.000Z']]);
        $this->assertNull($this->lookup('folio=MN4444B')->json('click_age_days'));

        // Exactamente N dias -> N.
        $this->contact(['contact_folio' => 'MN5555B', 'meta' => ['click_id_at' => '2026-09-18T12:00:00.000Z']]);
        $this->assertSame(30, $this->lookup('folio=MN5555B')->json('click_age_days'));
    }

    public function test_message_to_event_seconds_is_negative_when_the_message_precedes_the_event(): void
    {
        $this->contact(['event_at' => '2026-10-18 12:00:00']);

        $json = $this->lookup('folio=MN7K4Q2&message_at=' . urlencode('2026-10-18T11:59:00Z'))->assertOk()->json();

        $this->assertSame(-60, $json['message_to_event_seconds']);
    }

    public function test_cot_folio_in_several_sessions_is_ambiguous(): void
    {
        $this->contact(['type' => 'quote_submit', 'contact_folio' => null, 'session_id' => 'sess-a', 'event_id' => 'TEST-ca', 'meta' => ['folio' => 'COT-2026-0777']], '2026-10-18 12:00:00');
        $this->contact(['type' => 'quote_submit', 'contact_folio' => null, 'session_id' => 'sess-b', 'event_id' => 'TEST-cb', 'meta' => ['folio' => 'COT-2026-0777']], '2026-10-18 12:30:00');

        $json = $this->lookup('folio=COT-2026-0777')->assertOk()->json();

        $this->assertTrue($json['ambiguous']);
        $this->assertSame(2, $json['candidates']);
    }

    // ---------------------------------------------------------------- normalizacion y entradas raras

    public function test_folio_is_normalized_before_lookup(): void
    {
        $this->contact();

        foreach (['mn-7k4q2', 'MN 7K4Q2', '%20mn7k4q2%20', "MN7K4Q2" . '%E2%80%8B', '%E2%81%A0MN7K4Q2%E2%81%A0', 'MN%C2%A07K4Q2'] as $variant) {
            $this->lookup('folio=' . $variant)->assertOk()->assertJsonPath('folio', 'MN7K4Q2');
        }
    }

    public function test_folio_with_invalid_format_is_404_without_touching_the_database(): void
    {
        $this->contact();
        \Illuminate\Support\Facades\DB::flushQueryLog();
        \Illuminate\Support\Facades\DB::enableQueryLog();

        foreach (['MN7K4Q', 'MN7K4Q22', 'XX7K4Q2', 'MNAEIOU', 'MN0O1I2', "' OR 1=1 --", '%00'] as $bad) {
            $this->lookup('folio=' . urlencode($bad))->assertStatus(404)->assertExactJson(['status' => 'error', 'message' => 'Folio no encontrado.']);
        }
        $this->assertSame([], \Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();
    }

    public function test_array_parameters_do_not_cause_a_500(): void
    {
        $this->contact();

        $this->lookup('folio[]=MN7K4Q2')->assertStatus(422);
        $this->lookup('folio[a]=MN7K4Q2&type=phone_click')->assertStatus(422);
        $this->lookup('type[]=phone_click&from=2026-10-18T12:00:00Z&to=2026-10-18T12:05:00Z')->assertStatus(422);
        $this->lookup('type=phone_click&from[]=2026-10-18T12:00:00Z&to=2026-10-18T12:05:00Z')->assertStatus(422);
        $this->lookup('folio=MN7K4Q2&message_at[]=2026-10-18T12:00:00Z')->assertStatus(422);
    }

    // ---------------------------------------------------------------- privacidad

    public function test_no_personal_or_raw_data_is_exposed(): void
    {
        $this->contact([
            'referrer' => 'https://www.google.com/search?q=secreto-query',
            'page_url' => 'https://macdelnorte.test/producto/x?email=ana@example.com&tel=8112345678',
            'landing_page' => '/producto/x?email=ana@example.com',
            'meta' => [
                'href' => 'https://wa.me/528135825559?text=HOLA-TEXTO-PRIVADO',
                'link_text' => 'TEXTO-DEL-BOTON-PRIVADO',
                'sku' => 'DC1010',
                'qty' => '5',
                'value' => '9999',
            ],
        ]);

        $body = $this->lookup('folio=MN7K4Q2')->assertOk()->getContent();

        foreach (['ana@example.com', '8112345678', 'secreto-query', 'HOLA-TEXTO-PRIVADO', 'TEXTO-DEL-BOTON-PRIVADO', 'wa.me', '9999', 'ip_address', 'user_agent', 'link_text', 'href', 'mailto', 'tel:'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $body, $forbidden);
        }
        $this->assertStringContainsString('www.google.com', $body);
        $this->assertStringNotContainsString('https://www.google.com', $body, 'Solo el host del referrer');
    }

    // ---------------------------------------------------------------- cotizacion formal (MN + COT-)

    /** quote_start, quote_submit y quote_dismiss de la misma cotizacion (mismo MN; COT- en submit y dismiss). */
    private function seedQuote(string $session = 'sess-q', string $mn = 'MN7K4Q2', string $cot = 'COT-2026-0042'): void
    {
        $base = ['session_id' => $session, 'gclid' => self::GCLID, 'utm_source' => 'google'];
        $this->contact($base + ['type' => 'quote_start', 'contact_folio' => $mn, 'event_id' => 'TEST-qstart-' . $session], '2026-10-18 12:00:00');
        $this->contact($base + ['type' => 'quote_submit', 'contact_folio' => $mn, 'event_id' => 'TEST-qsubmit-' . $session, 'meta' => ['folio' => $cot]], '2026-10-18 12:05:00');
        $this->contact($base + ['type' => 'quote_dismiss', 'contact_folio' => null, 'event_id' => 'TEST-qdismiss-' . $session, 'meta' => ['folio' => $cot]], '2026-10-18 12:10:00');
    }

    public function test_mn_and_cot_resolve_to_the_same_quote_submit_row(): void
    {
        $this->seedQuote();

        $byMn = $this->lookup('folio=MN7K4Q2')->assertOk()->json();
        $byCot = $this->lookup('folio=COT-2026-0042')->assertOk()->json();

        $this->assertSame('TEST-qsubmit-sess-q', $byMn['event_id'], 'Prefiere quote_submit aunque quote_start sea anterior y quote_dismiss posterior');
        $this->assertSame($byMn['event_id'], $byCot['event_id']);
        $this->assertSame('quote_submit', $byMn['type']);
        $this->assertSame('quote_submit', $byCot['type']);
        $this->assertSame('MN7K4Q2', $byMn['folio']);
        $this->assertSame('COT-2026-0042', $byCot['folio']);
        // Varias filas de la misma sesion: no es ambiguo.
        $this->assertFalse($byMn['ambiguous']);
        $this->assertSame(1, $byMn['candidates']);
        $this->assertFalse($byCot['ambiguous']);
        $this->assertSame(1, $byCot['candidates']);
    }

    public function test_cot_without_quote_submit_falls_back_to_the_most_recent_row(): void
    {
        $this->contact(['type' => 'quote_start', 'contact_folio' => 'MN7K4Q2', 'session_id' => 'sess-q', 'event_id' => 'TEST-a', 'meta' => ['folio' => 'COT-2026-0050']], '2026-10-18 12:00:00');
        $this->contact(['type' => 'quote_dismiss', 'contact_folio' => null, 'session_id' => 'sess-q', 'event_id' => 'TEST-b', 'meta' => ['folio' => 'COT-2026-0050']], '2026-10-18 12:10:00');

        $this->assertSame('TEST-b', $this->lookup('folio=COT-2026-0050')->assertOk()->json('event_id'));
        // Con message_at, la mas cercana.
        $this->assertSame('TEST-a', $this->lookup('folio=COT-2026-0050&message_at=' . urlencode('2026-10-18T12:01:00Z'))->assertOk()->json('event_id'));
    }

    public function test_message_at_picks_the_closest_row_within_the_preferred_type(): void
    {
        // Dos quote_submit con el mismo MN en sesiones distintas + un quote_start MUY cercano al mensaje.
        $this->contact(['type' => 'quote_submit', 'session_id' => 'sess-a', 'event_id' => 'TEST-sa'], '2026-10-18 12:00:00');
        $this->contact(['type' => 'quote_submit', 'session_id' => 'sess-b', 'event_id' => 'TEST-sb'], '2026-10-18 12:30:00');
        $this->contact(['type' => 'quote_start', 'session_id' => 'sess-b', 'event_id' => 'TEST-start'], '2026-10-18 12:19:50');

        $json = $this->lookup('folio=MN7K4Q2&message_at=' . urlencode('2026-10-18T12:20:00Z'))->assertOk()->json();

        $this->assertSame('TEST-sb', $json['event_id'], 'El quote_start (mas cercano) no se elige: manda el tipo preferido');
        $this->assertTrue($json['ambiguous']);
        $this->assertSame(2, $json['candidates']);

        $near = $this->lookup('folio=MN7K4Q2&message_at=' . urlencode('2026-10-18T12:01:00Z'))->assertOk()->json();
        $this->assertSame('TEST-sa', $near['event_id']);
    }

    public function test_quote_start_alone_is_returned_when_there_is_no_submit(): void
    {
        $this->contact(['type' => 'quote_start', 'event_id' => 'TEST-only-start']);

        $this->assertSame('TEST-only-start', $this->lookup('folio=MN7K4Q2')->assertOk()->json('event_id'));
    }

    public function test_quote_submit_preference_does_not_hide_a_collision_with_another_session(): void
    {
        $this->seedQuote('sess-q1');
        // Mismo MN en otra sesion (WhatsApp): sigue contando como ambiguo.
        $this->contact(['type' => 'whatsapp_click', 'session_id' => 'sess-other', 'event_id' => 'TEST-w'], '2026-10-18 13:00:00');

        $json = $this->lookup('folio=MN7K4Q2')->assertOk()->json();

        $this->assertSame('TEST-qsubmit-sess-q1', $json['event_id']);
        $this->assertTrue($json['ambiguous']);
        $this->assertSame(2, $json['candidates']);
    }
}
