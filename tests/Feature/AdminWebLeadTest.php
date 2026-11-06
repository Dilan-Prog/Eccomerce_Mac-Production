<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Models\WebLead;
use App\Support\WebLeads\WebLeadNotifier;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Panel admin de "Solicitudes web" (Backend\WebLeadController).
 *
 * Sin RefreshDatabase: SQLite en memoria con solo las tablas necesarias (web_leads + usuarios/roles).
 * El notificador se sustituye por un fake para no enviar correos ni webhooks.
 */
class AdminWebLeadTest extends TestCase
{
    use InteractsWithAttributionTestMode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdminUsersTables();
        Schema::dropIfExists('web_leads');
        (require base_path('database/migrations/2026_11_06_100000_create_web_leads_table.php'))->up();
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

    private function url(string $path = ''): string
    {
        return '/admin/web-leads' . $path;
    }

    private function lead(array $attributes = []): WebLead
    {
        return WebLead::create(array_merge([
            'form_type' => 'cotizacion',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '8112345678',
            'company' => 'Industrias Norte',
            'city' => 'Monterrey',
            'state' => 'Nuevo León',
            'message' => 'Necesito cotizar un quemador',
            'status' => 'nuevo',
        ], $attributes));
    }

    /** Fake del notificador: aplica $apply al lead (lo que haria el real) y cuenta las llamadas. */
    private function fakeNotifier(callable $apply): object
    {
        $fake = new class ($apply) extends WebLeadNotifier {
            public array $calls = [];

            public function __construct(private $apply)
            {
            }

            public function notify(WebLead $lead): void
            {
                $this->calls[] = $lead->id;
                ($this->apply)($lead);
            }
        };
        $this->app->instance(WebLeadNotifier::class, $fake);

        return $fake;
    }

    private function tableRows(array $json): array
    {
        return array_map(fn ($r) => $r['row_id'], $json['rows']);
    }

    // ------------------------------------------------------------------ acceso

    public function test_guest_cannot_access(): void
    {
        $lead = $this->lead();

        $this->get($this->url())->assertRedirect();
        $this->getJson($this->url('/table-data'))->assertUnauthorized();
        $this->getJson($this->url('/' . $lead->id))->assertUnauthorized();
        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => 'contactado'])->assertUnauthorized();
        $this->postJson($this->url('/' . $lead->id . '/resend'))->assertUnauthorized();
        $this->deleteJson($this->url('/' . $lead->id))->assertUnauthorized();

        $this->assertSame(1, WebLead::count());
        $this->assertSame('nuevo', $lead->fresh()->status);
    }

    public function test_non_admin_user_cannot_access(): void
    {
        $lead = $this->lead();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get($this->url('/table-data'))->assertStatus(302);
        $this->actingAs($user)->delete($this->url('/' . $lead->id))->assertStatus(302);
        $this->assertSame(1, WebLead::count());
    }

    public function test_admin_without_site_module_gets_403_everywhere(): void
    {
        $lead = $this->lead();
        $notifier = $this->fakeNotifier(fn () => null);

        $this->actingAs($this->restrictedAdmin(['dashboard']));

        $this->get($this->url())->assertForbidden();
        $this->getJson($this->url('/table-data'))->assertForbidden();
        $this->getJson($this->url('/' . $lead->id))->assertForbidden();
        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => 'contactado'])->assertForbidden();
        $this->postJson($this->url('/' . $lead->id . '/resend'))->assertForbidden();
        $this->deleteJson($this->url('/' . $lead->id))->assertForbidden();

        // Nada se ejecuto.
        $this->assertSame('nuevo', $lead->fresh()->status);
        $this->assertSame(1, WebLead::count());
        $this->assertSame([], $notifier->calls);
    }

    public function test_admin_with_site_module_can_access(): void
    {
        $this->lead();

        $this->actingAs($this->restrictedAdmin(['site']))
            ->getJson($this->url('/table-data'))
            ->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_write_routes_do_not_accept_other_methods(): void
    {
        $lead = $this->lead();
        $this->actingAs($this->admin());

        $this->get($this->url('/' . $lead->id . '/resend'))->assertStatus(405);
        $this->get($this->url('/' . $lead->id . '/status'))->assertStatus(405);
        $this->post($this->url('/' . $lead->id))->assertStatus(405);
        $this->getJson($this->url('/abc'))->assertNotFound();
    }

    // ------------------------------------------------------------------ index / tabla

    public function test_index_renders(): void
    {
        $this->lead();

        $response = $this->actingAs($this->admin())->get($this->url());

        $response->assertOk()
            ->assertViewIs('admin-ui.web-leads.index')
            ->assertSee('Solicitudes web')
            ->assertSee('Cotización pública')
            ->assertSee(route('admin.web-leads.table-data'), false)
            ->assertSee('href="' . route('admin.web-leads.index') . '" class="au-nav-link"', false);
    }

    public function test_table_data_lists_newest_first_with_tabs_and_counts(): void
    {
        $old = $this->lead(['first_name' => 'Antiguo', 'status' => 'contactado']);
        $discarded = $this->lead(['first_name' => 'Descartado', 'status' => 'descartado']);
        $new = $this->lead(['first_name' => 'Reciente', 'status' => 'nuevo']);

        $this->actingAs($this->admin());

        $all = $this->getJson($this->url('/table-data'))->assertOk()->json();
        $this->assertSame([$new->id, $discarded->id, $old->id], $this->tableRows($all));
        $this->assertSame('todas', $all['active_filter']);
        $counts = collect($all['filters'])->pluck('count', 'key')->all();
        $this->assertSame(['todas' => 3, 'nuevas' => 1, 'contactadas' => 1, 'descartadas' => 1], $counts);

        $this->assertSame([$new->id], $this->tableRows($this->getJson($this->url('/table-data?filter=nuevas'))->json()));
        $this->assertSame([$old->id], $this->tableRows($this->getJson($this->url('/table-data?filter=contactadas'))->json()));
        $this->assertSame([$discarded->id], $this->tableRows($this->getJson($this->url('/table-data?filter=descartadas'))->json()));
    }

    public function test_table_data_cells_show_expected_values(): void
    {
        $lead = $this->lead([
            'form_type' => 'asociado',
            'utm_source' => 'google',
            'gclid' => 'abc123',
            'notified_at' => now(),
        ]);
        $this->lead(['first_name' => 'Con error', 'notify_error' => 'SMTP caido']);
        $this->lead(['first_name' => 'Pendiente']);

        $json = $this->actingAs($this->admin())->getJson($this->url('/table-data?sort=created_at&dir=desc'))->assertOk()->json();
        $rows = collect($json['rows'])->keyBy('row_id');
        $cells = $rows[$lead->id]['cells'];

        $this->assertSame('Asociados', $cells['form_type']['label']);
        $this->assertSame('Juan Pérez', $cells['first_name']['value']);
        $this->assertSame('Industrias Norte', $cells['first_name']['subtext']);
        $this->assertSame('Monterrey, Nuevo León', $cells['city']);
        $this->assertSame('google', $cells['utm_source']['value']);
        $this->assertStringContainsString('sí', $cells['utm_source']['subtext']);
        $this->assertSame('Nuevo', $cells['status']['label']);
        $this->assertSame('Correo enviado', $cells['notified_at']['label']);
        $this->assertSame('Error de correo', $rows[$lead->id + 1]['cells']['notified_at']['label']);
        $this->assertSame('Pendiente', $rows[$lead->id + 2]['cells']['notified_at']['label']);
        $this->assertNotEmpty($cells['action']);

        $columnKeys = array_column($json['columns'], 'key');
        $this->assertSame(['created_at', 'form_type', 'first_name', 'email', 'phone', 'city', 'utm_source', 'status', 'notified_at', 'action'], $columnKeys);
    }

    public function test_table_data_date_uses_mexico_city_timezone(): void
    {
        $lead = $this->lead();
        // 2026-03-10 18:30 UTC => 12:30 en CDMX (UTC-6 todo el año).
        $lead->forceFill(['created_at' => '2026-03-10 18:30:00'])->save();

        $json = $this->actingAs($this->admin())->getJson($this->url('/table-data'))->json();

        $this->assertSame('10/03/2026 12:30', $json['rows'][0]['cells']['created_at']);
    }

    public function test_search_matches_name_email_phone_company_and_message(): void
    {
        $a = $this->lead(['first_name' => 'Maria', 'last_name' => 'Lopez', 'email' => 'maria@acme.mx', 'phone' => '5551110000', 'company' => 'Acme', 'message' => 'Horno industrial']);
        $b = $this->lead(['first_name' => 'Pedro', 'last_name' => 'Ramos', 'email' => 'pedro@otro.mx', 'phone' => '5552220000', 'company' => 'Otra SA', 'message' => 'Medicion de gas']);

        $this->actingAs($this->admin());
        $search = fn (string $term) => $this->tableRows($this->getJson($this->url('/table-data?search=' . urlencode($term)))->json());

        $this->assertSame([$a->id], $search('Maria'));
        $this->assertSame([$a->id], $search('Maria Lopez'));
        $this->assertSame([$b->id], $search('otro.mx'));
        $this->assertSame([$b->id], $search('5552220000'));
        $this->assertSame([$a->id], $search('Acme'));
        $this->assertSame([$b->id], $search('Medicion'));
        $this->assertSame([], $search('no-existe-xyz'));
    }

    public function test_form_type_filter_applies_to_rows_and_tab_counts(): void
    {
        $med = $this->lead(['form_type' => 'medicion']);
        $this->lead(['form_type' => 'asociado']);
        $this->lead(['form_type' => 'medicion', 'status' => 'contactado']);

        $this->actingAs($this->admin());

        $json = $this->getJson($this->url('/table-data?form_type=medicion'))->json();
        $this->assertSame(2, $json['total']);
        $this->assertSame(2, collect($json['filters'])->firstWhere('key', 'todas')['count']);
        $this->assertSame(1, collect($json['filters'])->firstWhere('key', 'nuevas')['count']);
        $this->assertContains($med->id, $this->tableRows($json));

        // Un form_type inventado no filtra nada (ni rompe).
        $this->assertSame(3, $this->getJson($this->url('/table-data?form_type=hackeado'))->json()['total']);
    }

    // ------------------------------------------------------------------ show

    public function test_show_returns_all_data_escaped(): void
    {
        $lead = $this->lead([
            'first_name' => '<script>alert(1)</script>',
            'last_name' => 'O"Brien',
            'email' => 'x"><img src=x onerror=alert(2)>@example.com',
            'company' => '<b>Empresa</b>',
            'message' => "Linea 1\n<img src=x onerror=alert(3)>",
            'source_page' => 'javascript:alert(4)',
            'landing_page' => 'https://sitio.test/landing?a=1&b="<x>',
            'utm_source' => '<svg onload=alert(5)>',
            'gclid' => 'GCLID123',
            'notify_error' => '<i>SMTP</i> fallo',
            'webhook_error' => 'HTTP 500 <u>x</u>',
        ]);

        $response = $this->actingAs($this->admin())->get($this->url('/' . $lead->id))->assertOk();
        $html = $response->getContent();

        // Nada del visitante queda sin escapar.
        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<svg onload', $html);
        $this->assertStringNotContainsString('<b>Empresa', $html);
        $this->assertStringNotContainsString('<i>SMTP', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(3)&gt;', $html);
        // javascript: no se vuelve enlace.
        $this->assertStringNotContainsString('href="javascript:', $html);
        // Datos completos presentes.
        $response->assertSee('Cotización pública')
            ->assertSee('GCLID123')
            ->assertSee('Monterrey')
            ->assertSee('Nuevo León')
            ->assertSee('Error')
            ->assertSee(route('admin.web-leads.resend', $lead->id), false)
            ->assertSee(route('admin.web-leads.destroy', $lead->id), false);
    }

    public function test_show_displays_notification_state(): void
    {
        $sent = $this->lead(['notified_at' => '2026-03-10 18:30:00', 'webhook_sent_at' => '2026-03-10 18:31:00']);

        $this->actingAs($this->admin())->get($this->url('/' . $sent->id))
            ->assertOk()
            ->assertSee('10/03/2026 12:30:00')
            ->assertSee('10/03/2026 12:31:00');
    }

    public function test_show_404_for_unknown_lead(): void
    {
        $this->actingAs($this->admin())->getJson($this->url('/999'))->assertNotFound();
    }

    // ------------------------------------------------------------------ updateStatus

    public function test_update_status_accepts_valid_values(): void
    {
        $lead = $this->lead();
        $other = $this->lead(['first_name' => 'Otro']);
        $this->actingAs($this->admin());

        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => 'contactado'])
            ->assertOk()
            ->assertJson(['status' => 'success', 'lead_status' => 'contactado']);
        $this->assertSame('contactado', $lead->fresh()->status);

        // También por query string (como lo mandan los botones de la tabla).
        $this->patchJson($this->url('/' . $lead->id . '/status?status=descartado'))->assertOk();
        $this->assertSame('descartado', $lead->fresh()->status);

        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => 'nuevo'])->assertOk();
        $this->assertSame('nuevo', $lead->fresh()->status);

        // No tocó a los demás.
        $this->assertSame('nuevo', $other->fresh()->status);
    }

    public function test_update_status_rejects_invalid_values(): void
    {
        $lead = $this->lead(['status' => 'contactado']);
        $this->actingAs($this->admin());

        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => 'inventado'])
            ->assertStatus(422)->assertJsonValidationErrors('status');
        $this->patchJson($this->url('/' . $lead->id . '/status'), [])
            ->assertStatus(422)->assertJsonValidationErrors('status');
        $this->patchJson($this->url('/' . $lead->id . '/status'), ['status' => ['nuevo']])
            ->assertStatus(422)->assertJsonValidationErrors('status');

        $this->assertSame('contactado', $lead->fresh()->status);
    }

    // ------------------------------------------------------------------ resend

    public function test_resend_calls_notifier_and_reports_success(): void
    {
        $lead = $this->lead(['notify_error' => 'viejo error']);
        $other = $this->lead(['first_name' => 'Otro']);
        $notifier = $this->fakeNotifier(function (WebLead $l) {
            $l->update(['notified_at' => now(), 'notify_error' => null, 'webhook_sent_at' => now(), 'webhook_error' => null]);
        });

        $this->actingAs($this->admin())
            ->postJson($this->url('/' . $lead->id . '/resend'))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('notice.notify_error', null)
            ->assertJsonPath('notice.webhook_error', null)
            ->assertJsonStructure(['status', 'message', 'notice' => ['notified_at', 'notify_error', 'webhook_sent_at', 'webhook_error']]);

        $this->assertSame([$lead->id], $notifier->calls);
        $fresh = $lead->fresh();
        $this->assertNotNull($fresh->notified_at);
        $this->assertNull($fresh->notify_error);
        $this->assertNull($other->fresh()->notified_at);
    }

    public function test_resend_reflects_errors_left_by_the_notifier(): void
    {
        $lead = $this->lead();
        $this->fakeNotifier(function (WebLead $l) {
            $l->update(['notify_error' => 'Connection refused']);
        });

        $this->actingAs($this->admin())
            ->postJson($this->url('/' . $lead->id . '/resend'))
            ->assertOk()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('notice.notify_error', 'Connection refused')
            ->assertJsonPath('notice.notified_at', null);

        $this->assertSame('Connection refused', $lead->fresh()->notify_error);
    }

    public function test_resend_does_not_500_when_notifier_throws(): void
    {
        $lead = $this->lead();
        $this->fakeNotifier(function () {
            throw new \RuntimeException('boom');
        });

        $this->actingAs($this->admin())
            ->postJson($this->url('/' . $lead->id . '/resend'))
            ->assertOk()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('notice.notify_error', 'boom');
    }

    public function test_resend_404_for_unknown_lead(): void
    {
        $notifier = $this->fakeNotifier(fn () => null);

        $this->actingAs($this->admin())->postJson($this->url('/999/resend'))->assertNotFound();
        $this->assertSame([], $notifier->calls);
    }

    // ------------------------------------------------------------------ destroy

    public function test_destroy_deletes_only_that_row(): void
    {
        $a = $this->lead(['first_name' => 'A']);
        $b = $this->lead(['first_name' => 'B']);
        $c = $this->lead(['first_name' => 'C']);

        $this->actingAs($this->admin())
            ->deleteJson($this->url('/' . $b->id))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull(WebLead::find($b->id));
        $this->assertNotNull(WebLead::find($a->id));
        $this->assertNotNull(WebLead::find($c->id));
        $this->assertSame(2, WebLead::count());

        $this->actingAs($this->admin())->deleteJson($this->url('/' . $b->id))->assertNotFound();
        $this->assertSame(2, WebLead::count());
    }
}
