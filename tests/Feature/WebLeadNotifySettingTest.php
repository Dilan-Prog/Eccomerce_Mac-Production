<?php

namespace Tests\Feature;

use App\Mail\WebLeadReceivedMail;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Models\WebLead;
use App\Models\WebLeadSetting;
use App\Support\WebLeads\WebLeadNotifier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Correos que reciben el aviso de cada solicitud web, editables desde el panel (tabla web_lead_settings) con
 * prioridad sobre WEB_LEADS_NOTIFY_TO del .env.
 */
class WebLeadNotifySettingTest extends TestCase
{
    use InteractsWithAttributionTestMode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdminUsersTables();
        foreach (['web_leads', 'web_lead_settings'] as $table) {
            Schema::dropIfExists($table);
        }
        (require base_path('database/migrations/2026_11_06_100000_create_web_leads_table.php'))->up();
        (require base_path('database/migrations/2026_11_06_110000_create_web_lead_settings_table.php'))->up();
        config(['webleads.notify_to' => []]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function save(?string $value)
    {
        return $this->postJson('/admin/web-leads/settings', ['notify_to' => $value]);
    }

    private function lead(): WebLead
    {
        return WebLead::create([
            'form_type' => 'cotizacion', 'first_name' => 'Ana', 'email' => 'ana@example.com', 'status' => 'nuevo',
        ]);
    }

    // ------------------------------------------------------------------ acceso

    public function test_guest_cannot_save_the_setting(): void
    {
        $this->save('x@example.com')->assertUnauthorized();
        $this->assertNull(WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    public function test_admin_without_the_site_module_cannot_save_the_setting(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor-x', 'is_system' => false]);
        RoleModulePermission::create(['role_id' => $role->id, 'module_key' => 'ads', 'can_access' => true]);
        $user = User::factory()->create(['role' => 'admin', 'role_id' => $role->id]);

        $this->actingAs($user);
        $this->save('x@example.com')->assertForbidden();
        $this->assertNull(WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    // ------------------------------------------------------------------ guardado

    public function test_admin_saves_and_normalizes_the_emails(): void
    {
        $this->actingAs($this->admin());

        $this->save("  Contacto@MacDelNorte.com ; ventas@macdelnorte.com,\ncontacto@macdelnorte.com ")
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('source', 'panel')
            ->assertJsonPath('recipients', ['contacto@macdelnorte.com', 'ventas@macdelnorte.com']);

        $this->assertSame('contacto@macdelnorte.com, ventas@macdelnorte.com', WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    public function test_invalid_emails_are_rejected_and_nothing_is_saved(): void
    {
        $this->actingAs($this->admin());
        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, 'previo@example.com');

        $this->save('bueno@example.com, esto-no-es-correo, otro@')
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertSame('previo@example.com', WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    public function test_more_than_the_maximum_number_of_recipients_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $many = implode(',', array_map(fn ($i) => "u{$i}@example.com", range(1, WebLeadNotifier::MAX_RECIPIENTS + 1)));

        $this->save($many)->assertStatus(422);
        $this->assertNull(WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    public function test_empty_value_clears_the_panel_setting_and_falls_back_to_the_env_config(): void
    {
        $this->actingAs($this->admin());
        config(['webleads.notify_to' => ['env@example.com']]);
        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, 'panel@example.com');
        $this->assertSame(['panel@example.com'], WebLeadNotifier::recipients());

        $this->save('')
            ->assertOk()
            ->assertJsonPath('source', 'env')
            ->assertJsonPath('recipients', ['env@example.com']);

        $this->assertNull(WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO));
    }

    public function test_the_setting_page_shows_the_current_value_and_its_source(): void
    {
        $this->actingAs($this->admin());
        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, 'contacto@macdelnorte.com');

        $this->get('/admin/web-leads')
            ->assertOk()
            ->assertSee('value="contacto@macdelnorte.com"', false)
            ->assertSee('configurado en este panel');
    }

    public function test_the_page_warns_when_no_recipient_is_configured(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/web-leads')->assertOk()->assertSee('no hay ningún correo configurado');
    }

    // ------------------------------------------------------------------ notificador

    public function test_recipients_prefer_the_panel_over_the_env(): void
    {
        config(['webleads.notify_to' => ['env@example.com']]);
        $this->assertSame('env', WebLeadNotifier::recipientsSource());
        $this->assertSame(['env@example.com'], WebLeadNotifier::recipients());

        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, 'panel@example.com');
        $this->assertSame('panel', WebLeadNotifier::recipientsSource());
        $this->assertSame(['panel@example.com'], WebLeadNotifier::recipients());

        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, null);
        config(['webleads.notify_to' => []]);
        $this->assertSame('none', WebLeadNotifier::recipientsSource());
        $this->assertSame([], WebLeadNotifier::recipients());
    }

    public function test_the_real_notifier_sends_the_mail_to_the_panel_recipients(): void
    {
        Mail::fake();
        config(['webleads.notify_to' => ['env@example.com']]);
        WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, 'contacto@macdelnorte.com, ventas@macdelnorte.com');
        $lead = $this->lead();

        (new WebLeadNotifier())->notify($lead);

        Mail::assertSent(WebLeadReceivedMail::class, function ($mail) {
            return $mail->hasTo('contacto@macdelnorte.com') && $mail->hasTo('ventas@macdelnorte.com') && !$mail->hasTo('env@example.com');
        });
        $this->assertNotNull($lead->fresh()->notified_at);
    }

    public function test_a_missing_settings_table_does_not_break_the_notifier(): void
    {
        Schema::dropIfExists('web_lead_settings');
        config(['webleads.notify_to' => ['env@example.com']]);

        $this->assertSame(['env@example.com'], WebLeadNotifier::recipients());
        $this->assertSame('env', WebLeadNotifier::recipientsSource());
    }

    public function test_parse_emails_accepts_common_separators_and_reports_invalid_ones(): void
    {
        [$valid, $invalid] = WebLeadNotifier::parseEmails("a@x.com, B@X.com;c@x.com\nd@x.com  a@x.com malo");

        $this->assertSame(['a@x.com', 'b@x.com', 'c@x.com', 'd@x.com'], $valid);
        $this->assertSame(['malo'], $invalid);
    }
}
