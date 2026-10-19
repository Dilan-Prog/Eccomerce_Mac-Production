<?php

namespace Tests\Feature;

use App\Models\MarketingApiToken;
use App\Models\TrackConversion;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\TestCase;

/**
 * Camino HTTP COMPLETO de GET /api/attribution/lookup (kernel global -> grupo `api` -> throttle ->
 * attribution.token -> controlador -> servicio -> BD) con el caso reportado en produccion:
 *
 *   GET /api/attribution/lookup?folio=MND9HNC  (token de BD, scope attribution)  ->  404 "Folio no encontrado."
 *   aunque en phpMyAdmin existe: id 8381, whatsapp_click, contact_folio MND9HNC,
 *   gclid TEST_GCLID_meiOMDq7m0S9uvOw, created_at 2026-10-19 02:37:37 (UTC).
 *
 * Sin RefreshDatabase: SQLite en memoria, migraciones cargadas a mano. Todos los secretos son FALSOS.
 */
class AttributionLookupHttpPathTest extends TestCase
{
    use CreatesTrackConversionsTable;

    private const ENV_TOKEN = 'token-env-falso-de-prueba-con-mas-de-32-caracteres';
    private const FAKE_SECRET = 'FAKE-SECRET-abcdefghijklmnopqrstuvwxyz0123456789';
    private const FOLIO = 'MND9HNC';
    private const GCLID = 'TEST_GCLID_meiOMDq7m0S9uvOw';
    private const CREATED_AT = '2026-10-19 02:37:37';

    /** Claves que n8n lee de la respuesta. */
    private const N8N_KEYS = [
        'folio', 'gclid', 'gbraid', 'wbraid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term',
        'utm_content', 'attribution_source', 'is_test', 'ambiguous', 'touch_conflict', 'click_age_days', 'event_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        config(['attribution.api_token' => self::ENV_TOKEN]);
    }

    // ------------------------------------------------------------------ ayudas

    private function prepareTokensTable(bool $withScope = true): void
    {
        $files = ['2026_08_26_140000_create_marketing_api_tokens_table.php'];
        if ($withScope) {
            $files[] = '2026_10_22_100000_add_scope_to_marketing_api_tokens_table.php';
        }
        foreach ($files as $file) {
            (require database_path('migrations/' . $file))->up();
        }
    }

    /**
     * Crea el token IGUAL que MarketingApiTokenController::store(): key_id 'mk_' + 16 caracteres,
     * secret_hash = Hash::make(secreto), secret_last_four, status 1, scope. Devuelve "key_id.secret".
     */
    private function createDbToken(string $scope = 'attribution'): string
    {
        $keyId = 'mk_' . substr(md5(uniqid('', true)), 0, 16);
        $secret = self::FAKE_SECRET;

        $token = new MarketingApiToken();
        $token->name = 'Token de prueba';
        if (Schema::hasColumn('marketing_api_tokens', 'scope')) {
            $token->scope = $scope;
        }
        $token->key_id = $keyId;
        $token->secret_hash = Hash::make($secret);
        $token->secret_last_four = substr($secret, -4);
        $token->status = 1;
        $token->save();

        return "{$keyId}.{$secret}";
    }

    /** El evento exacto de la captura de phpMyAdmin (id 8381). */
    private function makeReportedRow(array $over = [], ?string $createdAt = self::CREATED_AT)
    {
        return $this->makeEvent(array_merge([
            'type' => 'whatsapp_click',
            'contact_folio' => self::FOLIO,
            'is_test' => true,
            'event_id' => 'TEST-' . uniqid(),
            'session_id' => 'sess-md9hnc',
            'gclid' => self::GCLID,
        ], $over), $createdAt);
    }

    /** Peticion real por la pila HTTP completa. $folioRaw va TAL CUAL en la query string. */
    private function lookupRaw(string $folioRaw, ?string $bearer)
    {
        return $this->getJson(
            '/api/attribution/lookup?folio=' . $folioRaw,
            $bearer ? ['Authorization' => 'Bearer ' . $bearer] : []
        );
    }

    private function assertN8nShape($response): void
    {
        $body = $response->json();
        foreach (self::N8N_KEYS as $key) {
            $this->assertArrayHasKey($key, $body, "Falta la clave {$key} en la respuesta");
        }
    }

    // ------------------------------------------------------------------ caso exacto reportado

    public function test_reported_case_db_token_returns_200_with_all_keys(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        $res = $this->getJson('/api/attribution/lookup?folio=MND9HNC', ['Authorization' => 'Bearer ' . $bearer]);

        $res->assertStatus(200)
                        ->assertJsonPath('folio', 'MND9HNC')
            ->assertJsonPath('gclid', self::GCLID)
            ->assertJsonPath('gbraid', null)
            ->assertJsonPath('wbraid', null)
            ->assertJsonPath('utm_source', null)
            ->assertJsonPath('utm_medium', null)
            ->assertJsonPath('utm_campaign', null)
            ->assertJsonPath('utm_term', null)
            ->assertJsonPath('utm_content', null)
            ->assertJsonPath('attribution_source', 'event')
            ->assertJsonPath('is_test', true)
            ->assertJsonPath('ambiguous', false)
            ->assertJsonPath('touch_conflict', false)
            ->assertJsonPath('click_age_days', null)
            ->assertJsonPath('event_at', '2026-10-19T02:37:37Z')
            ->assertJsonPath('type', 'whatsapp_click');
        $this->assertN8nShape($res);
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));

        // El token de BD se registro como usado (se autentico por la rama (a), no por el .env).
        $this->assertNotNull(DB::table('marketing_api_tokens')->value('last_used_at'));
    }

    public function test_reported_case_env_token_returns_200(): void
    {
        $this->makeReportedRow();

        $res = $this->lookupRaw('MND9HNC', self::ENV_TOKEN);

        $res->assertStatus(200)->assertJsonPath('folio', 'MND9HNC')->assertJsonPath('gclid', self::GCLID);
        $this->assertN8nShape($res);
    }

    public function test_reported_case_consulted_minutes_after_creation(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        // ~5 minutos despues de la fila (el momento exacto de la consulta no debe importar).
        $this->travelTo(\Carbon\Carbon::parse('2026-10-19 02:42:40', 'UTC'));
        $this->lookupRaw('MND9HNC', $bearer)->assertStatus(200)->assertJsonPath('folio', 'MND9HNC');
    }

    // ------------------------------------------------------------------ variantes del folio

    /** @return array<string,array{0:string}> valor tal cual va en la query string (ya codificado) */
    public static function folioVariants(): array
    {
        return [
            'exacto' => ['MND9HNC'],
            'minusculas' => ['mnd9hnc'],
            'mixto' => ['Mnd9Hnc'],
            'espacios codificados' => ['%20MND9HNC%20'],
            'espacios como +' => ['+MND9HNC+'],
            'guion MN-D9HNC' => ['MN-D9HNC'],
            'guion minusculas' => ['mn-d9hnc'],
            'espacio interno' => ['MN%20D9HNC'],
            'cero ancho al final' => ['MND9HNC%E2%80%8B'],
            'word joiner al inicio' => ['%E2%81%A0MND9HNC'],
            'nbsp al final' => ['MND9HNC%C2%A0'],
            'BOM al final' => ['MND9HNC%EF%BB%BF'],
            'salto de linea al final' => ['MND9HNC%0A'],
            'tabulador' => ['%09MND9HNC'],
        ];
    }

    /**
     * @dataProvider folioVariants
     */
    public function test_folio_variants_resolve_with_db_token(string $folioRaw): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        $res = $this->lookupRaw($folioRaw, $bearer);

        $res->assertStatus(200)->assertJsonPath('folio', 'MND9HNC')->assertJsonPath('gclid', self::GCLID);
        $this->assertN8nShape($res);
    }

    /**
     * @dataProvider folioVariants
     */
    public function test_folio_variants_resolve_with_env_token(string $folioRaw): void
    {
        $this->makeReportedRow();

        $this->lookupRaw($folioRaw, self::ENV_TOKEN)->assertStatus(200)->assertJsonPath('folio', 'MND9HNC');
    }

    // ------------------------------------------------------------------ los 4 folios de la captura

    public function test_four_folios_from_capture_with_shared_sessions(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');

        // MND9HNC: gclid propio, sesion propia.
        $this->makeReportedRow([], '2026-10-19 02:37:37');

        // Las otras tres comparten UNA sesion que ya tenia un page_visit con el gclid; los clics de WhatsApp
        // no traen click id propio: la atribucion sale de la sesion.
        $shared = 'sess-compartida-3-folios';
        $this->makeEvent([
            'type' => 'page_visit', 'session_id' => $shared, 'is_test' => true,
            'gclid' => 'TEST_GCLID_sharedSessionClick01', 'event_id' => 'TEST-pv-' . uniqid(),
        ], '2026-10-19 02:30:00');
        foreach ([
            'MN6CW85' => '2026-10-19 02:31:00',
            'MNR5W2S' => '2026-10-19 02:32:00',
            'MNMWSFF' => '2026-10-19 02:33:00',
        ] as $folio => $at) {
            $this->makeEvent([
                'type' => 'whatsapp_click', 'contact_folio' => $folio, 'session_id' => $shared,
                'is_test' => true, 'event_id' => 'TEST-' . $folio,
            ], $at);
        }

        foreach (['MND9HNC', 'MN6CW85', 'MNR5W2S', 'MNMWSFF'] as $folio) {
            $res = $this->lookupRaw($folio, $bearer);
            $res->assertStatus(200)->assertJsonPath('folio', $folio)->assertJsonPath('is_test', true)->assertJsonPath('ambiguous', false);
            $this->assertN8nShape($res);
        }

        $this->lookupRaw('MND9HNC', $bearer)->assertJsonPath('attribution_source', 'event')->assertJsonPath('gclid', self::GCLID);
        foreach (['MN6CW85', 'MNR5W2S', 'MNMWSFF'] as $folio) {
            $this->lookupRaw($folio, $bearer)
                ->assertJsonPath('attribution_source', 'session')
                ->assertJsonPath('gclid', 'TEST_GCLID_sharedSessionClick01');
        }
    }

    public function test_same_folio_in_two_sessions_is_ambiguous_but_still_200(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow(['session_id' => 'sess-a'], '2026-10-19 02:30:00');
        $this->makeReportedRow(['session_id' => 'sess-b'], '2026-10-19 02:37:37');

        $this->lookupRaw('MND9HNC', $bearer)->assertStatus(200)->assertJsonPath('ambiguous', true)->assertJsonPath('candidates', 2);
    }

    // ------------------------------------------------------------------ 404 legitimos

    public function test_nonexistent_folio_is_404(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        $this->lookupRaw('MNZZZZZ', $bearer)
            ->assertStatus(404)
            ->assertExactJson(['status' => 'error', 'message' => 'Folio no encontrado.']);
    }

    public function test_malformed_folios_are_404_without_touching_the_database(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        // Fuera del alfabeto (vocal A, 0, 1, I, O, L), longitud distinta, sin prefijo MN.
        foreach (['MNA9HNC', 'MN09HNC', 'MND9HNL', 'MND9HN', 'MND9HNCC', 'XXD9HNC', 'D9HNC'] as $bad) {
            $this->lookupRaw($bad, $bearer)->assertStatus(404)->assertJsonPath('message', 'Folio no encontrado.');
        }
    }

    public function test_real_and_test_rows_with_same_folio_do_not_mix(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        // La fila de prueba es la mas reciente; la real, mas vieja y de otra sesion.
        $this->makeReportedRow(['is_test' => false, 'session_id' => 'real', 'gclid' => 'REAL_GCLID_abcdefghijklmnop'], '2026-10-19 02:00:00');
        $this->makeReportedRow(['is_test' => true, 'session_id' => 'test'], '2026-10-19 02:37:37');

        $this->lookupRaw('MND9HNC', $bearer)
            ->assertStatus(200)
            ->assertJsonPath('is_test', true)
            ->assertJsonPath('gclid', self::GCLID)
            ->assertJsonPath('ambiguous', false);
    }

    // ------------------------------------------------------------------ autenticacion (no deben dar 404)

    public function test_auth_failures_are_401_not_404(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $marketing = $this->createDbToken('marketing');
        $this->makeReportedRow();

        $this->lookupRaw('MND9HNC', null)->assertStatus(401);
        $this->lookupRaw('MND9HNC', 'mk_inexistente.' . self::FAKE_SECRET)->assertStatus(401);
        $this->lookupRaw('MND9HNC', explode('.', $bearer)[0] . '.secreto-incorrecto')->assertStatus(401);
        $this->lookupRaw('MND9HNC', $marketing)->assertStatus(401);
    }

    public function test_db_token_in_query_string_is_not_accepted(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->makeReportedRow();

        $this->getJson('/api/attribution/lookup?folio=MND9HNC&token=' . urlencode($bearer))->assertStatus(401);
    }

    public function test_db_without_scope_column_token_cannot_authenticate_attribution(): void
    {
        // BD con la tabla de tokens SIN migrar el scope: todo token cuenta como marketing -> 401 (no 404).
        $this->prepareTokensTable(false);
        $bearer = $this->createDbToken('attribution'); // sin columna scope: se guarda como marketing
        $this->makeReportedRow();

        $this->lookupRaw('MND9HNC', $bearer)->assertStatus(401);
        // ... y el .env sigue valiendo.
        $this->lookupRaw('MND9HNC', self::ENV_TOKEN)->assertStatus(200);
    }

    // ------------------------------------------------------------------ BD migrada parcialmente

    /** Quita una columna de track_conversions en SQLite (primero sus indices). */
    private function dropTrackColumn(string $column, array $indexes): void
    {
        Schema::table('track_conversions', function ($table) use ($indexes) {
            foreach ($indexes as $index) {
                $table->dropIndex($index);
            }
        });
        Schema::table('track_conversions', function ($table) use ($column) {
            $table->dropColumn($column);
        });
        $this->assertFalse(Schema::hasColumn('track_conversions', $column));
    }

    public function test_db_without_is_test_column_documented_behaviour(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->dropTrackColumn('is_test', ['track_conversions_is_test_id_index']);

        // Fila con click id propio: byFolio() no consulta is_test y present() no necesita sesion.
        DB::table('track_conversions')->insert([
            'type' => 'whatsapp_click', 'contact_folio' => self::FOLIO, 'session_id' => 'sess-md9hnc',
            'gclid' => self::GCLID, 'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);

        $res = $this->lookupRaw('MND9HNC', $bearer);
        // Sin is_test la consulta por folio sigue funcionando: is_test sale como false (atributo ausente).
        $res->assertStatus(200)->assertJsonPath('folio', 'MND9HNC')->assertJsonPath('is_test', false);
    }

    /**
     * ADVERTENCIA (SQLite != MySQL): en SQLite un identificador entre comillas dobles que NO es una columna
     * se interpreta como CADENA literal ("is_test" = 0 -> 'is_test' = 0 -> falso), asi que una columna
     * faltante NO lanza error; en MySQL (`is_test`) lanza QueryException 1054 "Unknown column" -> 500.
     * Estas pruebas documentan lo que se ve en SQLite; no pueden probar el 500 de MySQL (ver
     * test_missing_column_compiles_differently_on_mysql, que si lo demuestra a nivel de SQL).
     */
    public function test_sqlite_db_without_is_test_column_row_without_own_click_id_still_200(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->dropTrackColumn('is_test', ['track_conversions_is_test_id_index']);

        // Sin atribucion propia, resolveAttribution() consulta la sesion con where('is_test', ...).
        DB::table('track_conversions')->insert([
            'type' => 'whatsapp_click', 'contact_folio' => self::FOLIO, 'session_id' => 'sess-md9hnc',
            'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);

        // SQLite: 200 con attribution_source none (en MySQL esta consulta daria 500, jamas 404).
        $this->lookupRaw('MND9HNC', $bearer)->assertStatus(200)->assertJsonPath('attribution_source', 'none');
    }

    public function test_sqlite_db_without_contact_folio_column_is_404_but_mysql_would_be_500(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->dropTrackColumn('contact_folio', ['track_conversions_contact_folio_index']);

        DB::table('track_conversions')->insert([
            'type' => 'whatsapp_click', 'session_id' => 'sess-md9hnc', 'gclid' => self::GCLID,
            'is_test' => true, 'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);

        // SQLite: where "contact_folio" = 'MND9HNC' compara la CADENA 'contact_folio' -> 0 filas -> 404.
        // Es un artefacto de SQLite: en MySQL la misma consulta es "Unknown column" -> 500.
        $this->lookupRaw('MND9HNC', $bearer)
            ->assertStatus(404)
            ->assertExactJson(['status' => 'error', 'message' => 'Folio no encontrado.']);
    }

    public function test_missing_column_compiles_differently_on_mysql(): void
    {
        $sqlite = TrackConversion::query()->where('contact_folio', 'MND9HNC')->toSql();
        $this->assertSame('select * from "track_conversions" where "contact_folio" = ?', $sqlite);

        // Gramatica de MySQL sin abrir conexion: identificadores con backticks => columna inexistente = error 1054.
        $mysql = new MySqlConnection(static fn () => null, 'prueba');
        $this->assertSame(
            'select * from `track_conversions` where `contact_folio` = ?',
            $mysql->table('track_conversions')->where('contact_folio', 'MND9HNC')->toSql()
        );
    }

    public function test_db_without_event_at_column_still_200_because_event_time_falls_back_to_created_at(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        $this->dropTrackColumn('event_at', []);

        DB::table('track_conversions')->insert([
            'type' => 'whatsapp_click', 'contact_folio' => self::FOLIO, 'session_id' => 'sess-md9hnc',
            'gclid' => self::GCLID, 'is_test' => true, 'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);

        $res = $this->lookupRaw('MND9HNC', $bearer);
        $res->assertStatus(200)->assertJsonPath('event_at', '2026-10-19T02:37:37Z');
    }

    public function test_table_missing_entirely_is_500_not_404(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');
        Schema::drop('track_conversions');

        $res = $this->lookupRaw('MND9HNC', $bearer);
        $this->assertSame(500, $res->getStatusCode());
    }

    // ------------------------------------------------------------------ BD distinta (el candidato "otra base")

    public function test_empty_table_in_the_connected_database_gives_404_even_if_row_exists_in_another_database(): void
    {
        // Documenta la unica forma real de "existe en phpMyAdmin pero 404": la app consulta OTRA base/tabla.
        // Aqui la tabla de la conexion por defecto esta vacia; la fila vive en una segunda conexion.
        $this->prepareTokensTable();
        $bearer = $this->createDbToken('attribution');

        $this->lookupRaw('MND9HNC', $bearer)
            ->assertStatus(404)
            ->assertExactJson(['status' => 'error', 'message' => 'Folio no encontrado.']);
    }

    /**
     * n8n puede mandar el folio con comillas, "Folio: " delante o el mensaje completo: el endpoint extrae el
     * folio valido que contenga en vez de responder 404 aunque la fila exista.
     *
     * @dataProvider wrappedFolioProvider
     */
    public function test_folio_wrapped_in_text_or_quotes_still_resolves(string $folioRaw): void
    {
        $this->prepareTokensTable();
        $this->makeReportedRow();

        $this->lookupRaw($folioRaw, self::ENV_TOKEN)
            ->assertOk()
            ->assertJsonPath('folio', self::FOLIO)
            ->assertJsonPath('is_test', true);
    }

    public static function wrappedFolioProvider(): array
    {
        return [
            'comillas dobles' => ['%22MND9HNC%22'],
            'comillas simples' => ['%27MND9HNC%27'],
            'prefijo Folio:' => ['Folio:%20MND9HNC'],
            'mensaje completo' => [rawurlencode('Hola, me interesa: 157S Control de Nivel McDonnell & Miller. Folio: MND9HNC')],
            'salto de linea y texto' => [rawurlencode("MND9HNC\nGracias")],
            'minusculas en frase' => [rawurlencode('mi folio es mnd9hnc gracias')],
        ];
    }

    /** Un texto sin ningun folio valido sigue siendo 404 (no se inventa un folio). */
    public function test_text_without_a_valid_folio_is_still_404(): void
    {
        $this->prepareTokensTable();
        $this->makeReportedRow();

        $this->lookupRaw(rawurlencode('Hola, quiero informacion, folio MND9'), self::ENV_TOKEN)->assertStatus(404);
    }

    /** Un 404 deja un registro de diagnostico (valor recibido en hex, base y conteo) sin tokens ni cabeceras. */
    public function test_a_miss_logs_a_diagnostic_without_secrets(): void
    {
        $this->prepareTokensTable();
        $this->makeReportedRow();
        \Illuminate\Support\Facades\Log::spy();

        $this->lookupRaw('MNZZZZZ', self::ENV_TOKEN)->assertStatus(404);

        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context = []) {
                return $message === 'attribution.lookup.folio_no_encontrado'
                    && ($context['normalized'] ?? null) === 'MNZZZZZ'
                    && ($context['raw_hex'] ?? null) === bin2hex('MNZZZZZ')
                    && ($context['rows_total'] ?? null) === 1
                    && !str_contains(json_encode($context), self::ENV_TOKEN);
            })
            ->once();
    }
}
