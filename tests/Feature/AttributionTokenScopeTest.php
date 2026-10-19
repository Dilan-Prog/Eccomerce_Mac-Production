<?php

namespace Tests\Feature;

use App\Models\MarketingApiToken;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Tokens de marketing_api_tokens con "uso" (scope): marketing | attribution.
 *
 * Reglas criticas: un token `marketing` NO autentica /api/attribution/* y uno `attribution` NO
 * autentica /api/marketing/*. El .env (ATTRIBUTION_API_TOKEN) sigue valiendo como respaldo, y el
 * codigo tolera que la tabla o la columna `scope` aun no existan (deploy antes de migrate).
 *
 * Sin RefreshDatabase: SQLite en memoria; las migraciones de la tabla se cargan a mano.
 * Todos los secretos de aqui son valores FALSOS de prueba.
 */
class AttributionTokenScopeTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    private const ENV_TOKEN = 'token-env-falso-de-prueba-con-mas-de-32-caracteres';
    private const FAKE_SECRET = 'FAKE-SECRET-abcdefghijklmnopqrstuvwxyz0123456789';
    private const FOLIO = 'MN7K4Q2';

    /** @var string[] */
    private array $tmpFiles = [];

    /** @var string[] */
    private array $tmpDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        config(['attribution.api_token' => self::ENV_TOKEN]);

        // Ruta de prueba protegida SOLO por marketing.token: no depende de las tablas del controlador real.
        Route::middleware('marketing.token')->get('/api/marketing/__ping', fn () => response()->json(['ok' => true]));
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

    /** Carga las migraciones de marketing_api_tokens (con o sin la del scope). */
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

    /** Inserta un token directo en BD con un secreto falso; devuelve el bearer "{key_id}.{secret}". */
    private function makeToken(?string $scope = 'attribution', int $status = 1, ?string $keyId = null, string $secret = self::FAKE_SECRET): string
    {
        $keyId ??= 'mk_' . substr(md5(uniqid('', true)), 0, 16);
        $row = [
            'name' => 'Token de prueba',
            'key_id' => $keyId,
            'secret_hash' => Hash::make($secret),
            'secret_last_four' => substr($secret, -4),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($scope !== null) {
            $row['scope'] = $scope;
        }
        DB::table('marketing_api_tokens')->insert($row);

        return $keyId . '.' . $secret;
    }

    private function lastUsed(string $bearer): ?string
    {
        return DB::table('marketing_api_tokens')->where('key_id', explode('.', $bearer, 2)[0])->value('last_used_at');
    }

    private function lookup(?string $bearer)
    {
        $this->makeEvent([
            'type' => 'whatsapp_click',
            'contact_folio' => self::FOLIO,
            'event_id' => 'TEST-' . uniqid(),
            'session_id' => 'sess-1',
            'gclid' => 'TEST_GCLID_0123456789ABCDEF',
        ], '2026-10-18 12:00:00');

        return $this->getJson('/api/attribution/lookup?folio=' . self::FOLIO, $bearer ? ['Authorization' => 'Bearer ' . $bearer] : []);
    }

    private function ping(?string $bearer)
    {
        return $this->getJson('/api/marketing/__ping', $bearer ? ['Authorization' => 'Bearer ' . $bearer] : []);
    }

    private function admin(): User
    {
        $this->prepareAdminUsersTables();

        return User::factory()->create(['role' => 'admin']);
    }

    private function restrictedAdmin(array $modules): User
    {
        $this->prepareAdminUsersTables();
        $role = Role::create(['name' => 'Editor ' . uniqid(), 'slug' => 'editor-' . uniqid(), 'is_system' => false]);
        foreach ($modules as $module) {
            RoleModulePermission::create(['role_id' => $role->id, 'module_key' => $module, 'can_access' => true]);
        }

        return User::factory()->create(['role' => 'admin', 'role_id' => $role->id]);
    }

    // ------------------------------------------------------------------ lookup con token de BD

    public function test_valid_attribution_token_passes_lookup_and_updates_last_used(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution');
        $this->assertNull($this->lastUsed($bearer));

        $response = $this->lookup($bearer);

        $response->assertOk()->assertJsonPath('folio', self::FOLIO);
        $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
        $this->assertNotNull($this->lastUsed($bearer));
    }

    public function test_marketing_token_cannot_use_the_attribution_endpoint(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('marketing');

        $response = $this->lookup($bearer);

        $response->assertStatus(401);
        $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
        $this->assertNull($this->lastUsed($bearer));
    }

    public function test_attribution_token_cannot_use_marketing_routes(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution');

        $this->ping($bearer)->assertStatus(401);
        // Y tampoco en una ruta real de /api/marketing/*: la corta el middleware antes del controlador.
        $this->getJson('/api/marketing/coupons', ['Authorization' => 'Bearer ' . $bearer])->assertStatus(401);
        $this->assertNull($this->lastUsed($bearer));
    }

    public function test_marketing_token_still_works_on_marketing_routes(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('marketing');

        $this->ping($bearer)->assertOk()->assertJson(['ok' => true]);
        $this->assertNotNull($this->lastUsed($bearer));
    }

    public function test_existing_tokens_without_explicit_scope_stay_valid_for_marketing(): void
    {
        // Fila creada ANTES de la migracion del scope; luego se migra: el default la deja en 'marketing'.
        $this->prepareTokensTable(withScope: false);
        $bearer = $this->makeToken(null);
        (require database_path('migrations/2026_10_22_100000_add_scope_to_marketing_api_tokens_table.php'))->up();

        $this->assertSame('marketing', DB::table('marketing_api_tokens')->value('scope'));
        $this->ping($bearer)->assertOk();
        $this->lookup($bearer)->assertStatus(401);
    }

    public function test_revoked_attribution_token_is_rejected(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution', status: 0);

        // El .env sigue configurado, por eso es 401 y no 503.
        $this->lookup($bearer)->assertStatus(401)->assertJsonPath('message', 'Token inválido o revocado.');
    }

    public function test_wrong_secret_or_unknown_key_is_rejected(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution');
        [$keyId] = explode('.', $bearer, 2);

        $this->lookup($keyId . '.otro-secreto-falso')->assertStatus(401);
        $this->lookup($keyId)->assertStatus(401);
        $this->lookup($keyId . '.')->assertStatus(401);
        $this->lookup('mk_inexistente.' . self::FAKE_SECRET)->assertStatus(401);
        $this->assertNull($this->lastUsed($bearer));
    }

    public function test_env_token_still_works_as_fallback_even_with_db_tokens(): void
    {
        $this->prepareTokensTable();
        $this->makeToken('attribution');

        $this->lookup(self::ENV_TOKEN)->assertOk();
    }

    public function test_no_env_and_no_tokens_is_503(): void
    {
        $this->prepareTokensTable();
        config(['attribution.api_token' => '']);

        $response = $this->lookup('cualquiera');

        $response->assertStatus(503)->assertJsonPath('message', 'Endpoint de atribución no configurado.');
        $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
    }

    public function test_only_non_attribution_or_revoked_tokens_still_counts_as_not_configured(): void
    {
        $this->prepareTokensTable();
        config(['attribution.api_token' => 'corto']);
        $this->makeToken('marketing');
        $this->makeToken('attribution', status: 0);

        $this->lookup('cualquiera')->assertStatus(503);
    }

    public function test_short_env_with_db_token_authenticates_with_the_db_token(): void
    {
        $this->prepareTokensTable();
        config(['attribution.api_token' => 'corto']);
        $bearer = $this->makeToken('attribution');

        $this->lookup($bearer)->assertOk();
        // El .env corto se sigue ignorando como credencial y, habiendo tokens de BD, ya no es 503 sino 401.
        $this->lookup('corto')->assertStatus(401);
    }

    // ------------------------------------------------------------------ BD sin migrar

    public function test_table_without_scope_column_falls_back_to_env_without_500(): void
    {
        $this->prepareTokensTable(withScope: false);
        $bearer = $this->makeToken(null);

        $this->lookup(self::ENV_TOKEN)->assertOk();
        $this->lookup($bearer)->assertStatus(401);
        $this->lookup('mk_x.y')->assertStatus(401);

        // Sin columna y sin .env: nadie entra, tambien sin 500.
        config(['attribution.api_token' => '']);
        $this->lookup($bearer)->assertStatus(503);
    }

    public function test_marketing_keeps_working_on_a_table_without_scope_column(): void
    {
        $this->prepareTokensTable(withScope: false);
        $bearer = $this->makeToken(null);

        $this->ping($bearer)->assertOk();
        $this->ping('mk_x.y')->assertStatus(401);
    }

    public function test_missing_table_falls_back_to_env_without_500(): void
    {
        $this->assertFalse(Schema::hasTable('marketing_api_tokens'));

        $this->lookup(self::ENV_TOKEN)->assertOk();
        $this->lookup('mk_x.y')->assertStatus(401);

        config(['attribution.api_token' => '']);
        $this->lookup('mk_x.y')->assertStatus(503);
    }

    // ------------------------------------------------------------------ controlador del admin

    public function test_store_with_attribution_scope_saves_it_and_reveals_the_secret_once(): void
    {
        $this->prepareTokensTable();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/marketing-tokens', ['name' => 'n8n Google Ads', 'scope' => 'attribution']);

        $response->assertOk()->assertJsonPath('status', 'success');
        $reveal = $response->json('reveal_secret');
        $this->assertMatchesRegularExpression('/^mk_[A-Za-z0-9]{16}\.[A-Za-z0-9]{40}$/', $reveal);

        $row = DB::table('marketing_api_tokens')->first();
        $this->assertSame('attribution', $row->scope);
        $this->assertSame(1, (int) $row->status);
        $this->assertStringNotContainsString(explode('.', $reveal, 2)[1], json_encode($row));

        // El token recien creado funciona en atribucion y no en marketing.
        $this->lookup($reveal)->assertOk();
        $this->ping($reveal)->assertStatus(401);

        // El listado no vuelve a mostrar el secreto ni el hash; si el uso.
        $list = $this->actingAs($admin)->getJson('/admin/marketing-tokens/table-data');
        $list->assertOk();
        $content = $list->getContent();
        $this->assertStringNotContainsString($reveal, $content);
        $this->assertStringNotContainsString(explode('.', $reveal, 2)[1], $content);
        $this->assertStringNotContainsString($row->secret_hash, $content);
        $this->assertStringNotContainsString('secret_hash', $content);
        $this->assertSame('Atribución Google Ads', $list->json('rows.0.cells.scope.label'));
        $this->assertStringNotContainsString('reveal_secret', $content);
    }

    public function test_store_without_scope_defaults_to_marketing(): void
    {
        $this->prepareTokensTable();

        $response = $this->actingAs($this->admin())->postJson('/admin/marketing-tokens', ['name' => 'Flujo de correo']);

        $response->assertOk();
        $this->assertSame('marketing', DB::table('marketing_api_tokens')->value('scope'));
        $this->ping($response->json('reveal_secret'))->assertOk();
    }

    public function test_store_rejects_an_invalid_scope(): void
    {
        $this->prepareTokensTable();

        $this->actingAs($this->admin())
            ->postJson('/admin/marketing-tokens', ['name' => 'X', 'scope' => 'aspel'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('scope');

        $this->assertSame(0, DB::table('marketing_api_tokens')->count());
    }

    public function test_store_marketing_token_still_works_before_the_scope_migration(): void
    {
        $this->prepareTokensTable(withScope: false);
        $admin = $this->admin();

        $ok = $this->actingAs($admin)->postJson('/admin/marketing-tokens', ['name' => 'Previo a migrar', 'scope' => 'marketing']);
        $ok->assertOk();
        $this->ping($ok->json('reveal_secret'))->assertOk();

        // Un token de atribucion sin columna no se puede distinguir del de marketing: se rechaza.
        $this->actingAs($admin)->postJson('/admin/marketing-tokens', ['name' => 'Atribucion', 'scope' => 'attribution'])->assertStatus(422);
        $this->assertSame(1, DB::table('marketing_api_tokens')->count());
    }

    public function test_update_can_change_scope_and_revoke(): void
    {
        $this->prepareTokensTable();
        $admin = $this->admin();
        $bearer = $this->makeToken('marketing');
        $id = DB::table('marketing_api_tokens')->value('id');

        $this->actingAs($admin)->putJson("/admin/marketing-tokens/{$id}", ['name' => 'Renombrado', 'status' => 1, 'scope' => 'attribution'])
            ->assertOk()->assertJsonMissing(['reveal_secret']);

        $this->assertSame('attribution', DB::table('marketing_api_tokens')->where('id', $id)->value('scope'));
        $this->lookup($bearer)->assertOk();
        $this->ping($bearer)->assertStatus(401);

        // Sin scope en el cuerpo el uso no cambia; con scope invalido, 422.
        $this->actingAs($admin)->putJson("/admin/marketing-tokens/{$id}", ['name' => 'Otra vez', 'status' => 0])->assertOk();
        $this->assertSame('attribution', DB::table('marketing_api_tokens')->where('id', $id)->value('scope'));
        $this->lookup($bearer)->assertStatus(401);
        $this->actingAs($admin)->putJson("/admin/marketing-tokens/{$id}", ['name' => 'X', 'status' => 1, 'scope' => 'otro'])->assertStatus(422);
    }

    public function test_regenerate_keeps_scope_and_invalidates_the_old_secret(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution');
        $id = DB::table('marketing_api_tokens')->value('id');

        $response = $this->actingAs($this->admin())->postJson("/admin/marketing-tokens/{$id}/regenerate");

        $response->assertOk();
        $new = $response->json('reveal_secret');
        $this->assertSame('attribution', DB::table('marketing_api_tokens')->value('scope'));
        $this->lookup($bearer)->assertStatus(401);
        $this->lookup($new)->assertOk();
    }

    public function test_module_permissions_are_unchanged(): void
    {
        $this->prepareTokensTable();
        $outsider = $this->restrictedAdmin(['site']);
        $insider = $this->restrictedAdmin(['marketing-integracion']);

        $this->actingAs($outsider)->getJson('/admin/marketing-tokens/table-data')->assertForbidden();
        $this->actingAs($outsider)->postJson('/admin/marketing-tokens', ['name' => 'X', 'scope' => 'attribution'])->assertForbidden();
        $this->assertSame(0, DB::table('marketing_api_tokens')->count());

        $this->actingAs($insider)->getJson('/admin/marketing-tokens/table-data')->assertOk();
        $this->actingAs($insider)->postJson('/admin/marketing-tokens', ['name' => 'X', 'scope' => 'attribution'])->assertOk();

        auth()->logout();
        $this->postJson('/admin/marketing-tokens', ['name' => 'X'])->assertUnauthorized();
    }

    public function test_listing_works_before_the_scope_migration(): void
    {
        $this->prepareTokensTable(withScope: false);
        $this->makeToken(null);

        $response = $this->actingAs($this->admin())->getJson('/admin/marketing-tokens/table-data');

        $response->assertOk();
        $this->assertSame('Email Marketing', $response->json('rows.0.cells.scope.label'));
    }

    // ------------------------------------------------------------------ vistas

    public function test_index_passes_only_a_count_never_token_values(): void
    {
        $this->prepareTokensTable();
        $bearer = $this->makeToken('attribution');
        $this->makeToken('attribution', status: 0);
        $this->makeToken('marketing');
        $this->stubIndexView();

        $response = $this->actingAs($this->admin())->get('/admin/marketing-tokens');

        $response->assertOk();
        $data = json_decode(html_entity_decode(strip_tags($response->getContent())), true);
        $this->assertSame(1, $data['db_tokens_active']);
        $this->assertSame(self::ENV_TOKEN === '' ? false : true, $data['token_configured']);
        $this->assertStringNotContainsString(self::ENV_TOKEN, $response->getContent());
        $this->assertStringNotContainsString(explode('.', $bearer, 2)[1], $response->getContent());
        $this->assertStringNotContainsString(explode('.', $bearer, 2)[0], $response->getContent());
    }

    public function test_index_card_badge_reflects_env_and_db_tokens(): void
    {
        $this->prepareTokensTable();
        $admin = $this->admin();
        $badge = function (string $env, bool $dbToken) use ($admin): string {
            config(['attribution.api_token' => $env]);
            DB::table('marketing_api_tokens')->delete();
            if ($dbToken) {
                $this->makeToken('attribution');
            }
            $html = $this->actingAs($admin)->get('/admin/marketing-tokens')->assertOk()->getContent();
            preg_match('/au-badge au-badge-\w+">([^<]+)</', $html, $m);
            $this->assertStringContainsString('eligiendo el uso', $html);
            $this->assertStringNotContainsString(self::ENV_TOKEN, $html);

            return $m[1] ?? '';
        };

        $this->assertSame('Token no configurado', $badge('', false));
        $this->assertSame('Token configurado', $badge('', true));
        $this->assertSame('Token demasiado corto', $badge('corto', false));
        $this->assertSame('Token configurado', $badge('corto', true));
        $this->assertSame('Token configurado', $badge(self::ENV_TOKEN, false));
    }

    public function test_form_fragments_render_the_scope_select(): void
    {
        $this->prepareTokensTable();
        $admin = $this->admin();

        $create = $this->actingAs($admin)->get('/admin/marketing-tokens/create-fragment');
        $create->assertOk()->assertSee('name="scope"', false)->assertSee('Uso del token')
            ->assertSee('Email Marketing (/api/marketing/*)')->assertSee('Atribución Google Ads (/api/attribution/lookup)');
        $this->assertMatchesRegularExpression('/<option value="marketing"\s+selected/', $create->getContent());

        $this->makeToken('attribution');
        $id = DB::table('marketing_api_tokens')->value('id');
        $edit = $this->actingAs($admin)->get("/admin/marketing-tokens/{$id}/edit-fragment");
        $edit->assertOk();
        $this->assertMatchesRegularExpression('/<option value="attribution"\s+selected/', $edit->getContent());
        $this->assertDoesNotMatchRegularExpression('/<option value="marketing"\s+selected/', $edit->getContent());
    }

    public function test_index_view_compiles_and_shows_the_new_card_texts(): void
    {
        $file = resource_path('views/admin-ui/marketing-tokens/index.blade.php');
        $source = file_get_contents($file);
        $compiled = Blade::compileString($source);

        $this->assertStringContainsString('Token configurado', $source);
        $this->assertStringContainsString('db_tokens_active', $source);
        $this->assertStringContainsString('Atribución Google Ads', $source);
        $this->assertStringContainsString('Respaldo: <code>ATTRIBUTION_API_TOKEN</code>', $source);
        $this->assertNotSame('', $compiled);

        // El PHP compilado debe ser sintacticamente valido.
        $tmp = tempnam(sys_get_temp_dir(), 'bladecheck');
        file_put_contents($tmp, $compiled);
        $this->tmpFiles[] = $tmp;
        exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $out, $code);
        $this->assertSame(0, $code, implode("\n", $out));
    }

    /** Sustituye la vista del index (el layout real del admin necesita MySQL) por un volcado de $attribution. */
    private function stubIndexView(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mt_views_' . uniqid();
        $dir = $root . DIRECTORY_SEPARATOR . 'admin-ui' . DIRECTORY_SEPARATOR . 'marketing-tokens';
        mkdir($dir, 0777, true);
        $file = $dir . DIRECTORY_SEPARATOR . 'index.blade.php';
        file_put_contents($file, '{{ json_encode($attribution) }}');
        $this->tmpFiles[] = $file;
        $this->tmpDirs = [$dir, dirname($dir), $root];
        app('view')->getFinder()->prependLocation($root);
    }

    // ------------------------------------------------------------------ laboratorio

    public function test_lab_status_counts_a_db_attribution_token_as_configured(): void
    {
        $this->prepareTokensTable();
        $this->prepareAdminUsersTables();
        config(['attribution.api_token' => '']);
        $bearer = $this->makeToken('attribution');

        $response = $this->actingAs($this->admin())->getJson('/admin/attribution-lab/status');

        $response->assertOk();
        $this->assertSame(['configured' => true, 'length_ok' => true], $response->json('data.token'));
        $messages = implode("\n", array_column($response->json('data.warnings'), 'message'));
        $this->assertStringNotContainsString('ATTRIBUTION_API_TOKEN', $messages);
        // Nunca sale el token ni su key_id.
        $this->assertStringNotContainsString(explode('.', $bearer, 2)[0], $response->getContent());
        $this->assertStringNotContainsString(explode('.', $bearer, 2)[1], $response->getContent());
    }

    public function test_lab_status_without_any_token_warns_and_points_to_the_panel(): void
    {
        $this->prepareTokensTable();
        $this->makeToken('marketing');
        $this->makeToken('attribution', status: 0);
        config(['attribution.api_token' => '']);

        $response = $this->actingAs($this->admin())->getJson('/admin/attribution-lab/status');

        $this->assertSame(['configured' => false, 'length_ok' => false], $response->json('data.token'));
        $messages = implode("\n", array_column($response->json('data.warnings'), 'message'));
        $this->assertStringContainsString('Marketing', $messages);
        $this->assertStringContainsString('503', $messages);
    }

    public function test_lab_status_short_env_with_db_token_warns_it_is_ignored(): void
    {
        $this->prepareTokensTable();
        $this->makeToken('attribution');
        config(['attribution.api_token' => 'corto']);

        $response = $this->actingAs($this->admin())->getJson('/admin/attribution-lab/status');

        $this->assertSame(['configured' => true, 'length_ok' => true], $response->json('data.token'));
        $messages = implode("\n", array_column($response->json('data.warnings'), 'message'));
        $this->assertStringContainsString('demasiado corto', $messages);
        $this->assertStringNotContainsString('responde 503', $messages);
    }

    public function test_model_helpers(): void
    {
        $this->prepareTokensTable();
        $this->assertSame(0, MarketingApiToken::activeAttributionCount());
        $this->makeToken('attribution');
        $this->makeToken('attribution', status: 0);
        $this->makeToken('marketing');
        $this->assertSame(1, MarketingApiToken::activeAttributionCount());

        $token = new MarketingApiToken();
        $this->assertSame('Email Marketing', $token->useLabel());
        $token->scope = 'attribution';
        $this->assertSame('Atribución Google Ads', $token->useLabel());
        // El hash nunca se serializa.
        $this->assertArrayNotHasKey('secret_hash', (new MarketingApiToken())->forceFill(['secret_hash' => 'x'])->toArray());
    }
}
