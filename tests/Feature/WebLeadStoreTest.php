<?php

namespace Tests\Feature;

use App\Mail\WebLeadReceivedMail;
use App\Models\WebLead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * POST /solicitudes-web (WebLeadController@store): guarda en web_leads y despues avisa por correo/webhook.
 * Como el resto de pruebas del repo: SQLite :memory: sin RefreshDatabase; la tabla se crea en setUp.
 */
class WebLeadStoreTest extends TestCase
{
    private const WEBHOOK = 'https://n8n.example.test/webhook/web-leads';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('web_leads');
        (require base_path('database/migrations/2026_11_06_100000_create_web_leads_table.php'))->up();

        config([
            'webleads.notify_to' => ['ventas@example.test'],
            'webleads.webhook_url' => null,
            'webleads.webhook_token' => null,
            'webleads.throttle_per_minute' => 5,
            'webleads.min_fill_seconds' => 3,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'form_type' => 'cotizacion',
            'first_name' => 'Ana',
            'last_name' => 'Lopez',
            'email' => 'ana@example.test',
            'phone' => '8112345678',
            'company' => 'Industrias Norte',
            'city' => 'Monterrey',
            'state' => 'Nuevo Leon',
            'operation' => 'Caldera de vapor',
            'message' => 'Necesito cotizar una calibracion.',
            'source_page' => 'https://macdelnorte.test/cotizacion',
            'landing_page' => '/?gclid=ABC',
            'referrer' => 'https://www.google.com/',
            'session_id' => 'sess-123',
            'gclid' => 'GCLID123',
            'gbraid' => 'GBRAID123',
            'wbraid' => 'WBRAID123',
            'fbclid' => 'FBCLID123',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'calibracion',
            'utm_term' => 'calibrar quemador',
            'utm_content' => 'anuncio-1',
        ], $override);
    }

    private function send(array $data = [], bool $merge = true)
    {
        return $this->from('/cotizacion')->post(route('web-leads.store'), $merge ? $this->payload($data) : $data);
    }

    public function test_route_exists_with_expected_name_and_path(): void
    {
        $this->assertSame('/solicitudes-web', route('web-leads.store', [], false));
    }

    public function test_stores_the_request_with_all_fields(): void
    {
        Mail::fake();

        $this->send()
            ->assertRedirect('/cotizacion')
            ->assertSessionHas('web_lead_status', 'ok')
            ->assertSessionHas('web_lead_form', 'cotizacion')
            ->assertSessionHasNoErrors();

        $this->assertSame(1, WebLead::count());
        $lead = WebLead::firstOrFail();
        foreach ($this->payload() as $field => $value) {
            $this->assertSame($value, $lead->{$field}, "campo $field");
        }
        $this->assertSame('nuevo', $lead->status);
    }

    public function test_optional_fields_can_be_omitted(): void
    {
        Mail::fake();

        $this->send(['form_type' => 'asociado', 'first_name' => 'Luis', 'email' => 'luis@example.test'], false)
            ->assertSessionHas('web_lead_status', 'ok');

        $lead = WebLead::firstOrFail();
        $this->assertSame('asociado', $lead->form_type);
        $this->assertNull($lead->phone);
        $this->assertNull($lead->gclid);
    }

    public function test_missing_required_fields_return_errors_and_store_nothing(): void
    {
        Mail::fake();

        $this->send(['first_name' => '', 'email' => '', 'form_type' => ''])
            ->assertSessionHasErrors(['first_name', 'email', 'form_type'])
            ->assertSessionMissing('web_lead_status');

        $this->assertSame(0, WebLead::count());
        Mail::assertNothingSent();
    }

    public function test_error_messages_are_in_spanish(): void
    {
        $this->send(['first_name' => ''])
            ->assertSessionHasErrors(['first_name' => 'Escribe tu nombre.']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->send(['email' => 'no-es-un-correo'])
            ->assertSessionHasErrors(['email' => 'Escribe un correo electrónico válido.']);

        $this->assertSame(0, WebLead::count());
    }

    public function test_invalid_form_type_is_rejected(): void
    {
        $this->send(['form_type' => 'otro'])->assertSessionHasErrors('form_type');

        $this->assertSame(0, WebLead::count());
    }

    public function test_form_fields_over_their_limit_are_rejected(): void
    {
        $this->send(['message' => str_repeat('a', 5001)])->assertSessionHasErrors('message');

        $this->assertSame(0, WebLead::count());
    }

    public function test_long_attribution_fields_are_truncated_not_rejected(): void
    {
        Mail::fake();

        $this->send([
            'source_page' => 'https://macdelnorte.test/' . str_repeat('x', 900),
            'landing_page' => str_repeat('y', 900),
            'referrer' => str_repeat('r', 900),
            'session_id' => str_repeat('s', 200),
            'gclid' => str_repeat('g', 600),
            'utm_source' => str_repeat('u', 400),
            'utm_campaign' => ['no', 'es', 'texto'],
        ])->assertSessionHasNoErrors()->assertSessionHas('web_lead_status', 'ok');

        $lead = WebLead::firstOrFail();
        $this->assertSame(500, mb_strlen($lead->source_page));
        $this->assertSame(500, mb_strlen($lead->landing_page));
        $this->assertSame(500, mb_strlen($lead->referrer));
        $this->assertSame(64, mb_strlen($lead->session_id));
        $this->assertSame(255, mb_strlen($lead->gclid));
        $this->assertSame(190, mb_strlen($lead->utm_source));
        $this->assertNull($lead->utm_campaign);
    }

    public function test_filled_honeypot_looks_like_success_but_stores_and_sends_nothing(): void
    {
        Mail::fake();
        Http::fake();
        config(['webleads.webhook_url' => self::WEBHOOK]);

        $this->send(['website' => 'http://spam.example'])
            ->assertRedirect('/cotizacion')
            ->assertSessionHas('web_lead_status', 'ok')
            ->assertSessionHasNoErrors();

        $this->assertSame(0, WebLead::count());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_empty_honeypot_does_not_block(): void
    {
        Mail::fake();

        $this->send(['website' => ''])->assertSessionHas('web_lead_status', 'ok');

        $this->assertSame(1, WebLead::count());
    }

    public function test_submission_that_is_too_fast_is_treated_as_a_robot(): void
    {
        Mail::fake();

        $this->send(['form_started_at' => time() - 1])
            ->assertSessionHas('web_lead_status', 'ok');

        $this->assertSame(0, WebLead::count());
        Mail::assertNothingSent();
    }

    public function test_submission_after_the_minimum_time_is_stored(): void
    {
        Mail::fake();

        $this->send(['form_started_at' => time() - 60])->assertSessionHas('web_lead_status', 'ok');

        $this->assertSame(1, WebLead::count());
    }

    public function test_missing_or_invalid_form_started_at_does_not_block(): void
    {
        Mail::fake();

        $this->send(['form_started_at' => 'basura'])->assertSessionHas('web_lead_status', 'ok');
        $this->send(['form_started_at' => ''])->assertSessionHas('web_lead_status', 'ok');
        $this->send()->assertSessionHas('web_lead_status', 'ok');

        $this->assertSame(3, WebLead::count());
    }

    public function test_sends_mail_to_notify_to_and_marks_notified_at(): void
    {
        Mail::fake();
        config(['webleads.notify_to' => ['ventas@example.test', 'admin@example.test']]);

        $this->send();

        Mail::assertSent(WebLeadReceivedMail::class, function (WebLeadReceivedMail $mail) {
            return $mail->hasTo('ventas@example.test')
                && $mail->hasTo('admin@example.test')
                && $mail->hasReplyTo('ana@example.test')
                && $mail->hasSubject('Nueva solicitud web: Cotización pública - Ana Lopez');
        });

        $lead = WebLead::firstOrFail();
        $this->assertNotNull($lead->notified_at);
        $this->assertNull($lead->notify_error);
    }

    public function test_mail_body_shows_data_escaped_with_origin(): void
    {
        $lead = WebLead::create([
            'form_type' => 'medicion',
            'first_name' => '<script>alert(1)</script>',
            'email' => 'x@example.test',
            'message' => 'Hola <b>mundo</b>',
            'gclid' => 'GCLID-XYZ',
            'utm_source' => 'google',
        ]);

        $html = (new WebLeadReceivedMail($lead))->render();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;mundo&lt;/b&gt;', $html);
        $this->assertStringContainsString('GCLID-XYZ', $html);
        $this->assertStringContainsString('Medición', $html);
    }

    public function test_no_mail_destinations_means_no_mail_but_request_is_stored(): void
    {
        Mail::fake();
        config(['webleads.notify_to' => []]);

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        Mail::assertNothingSent();
        $lead = WebLead::firstOrFail();
        $this->assertNull($lead->notified_at);
        $this->assertNull($lead->notify_error);
    }

    public function test_mail_failure_keeps_the_request_and_still_answers_success(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException("Connection could not be established with host smtp.example.test"));

        $this->send()
            ->assertRedirect('/cotizacion')
            ->assertSessionHas('web_lead_status', 'ok')
            ->assertSessionHasNoErrors();

        $lead = WebLead::firstOrFail();
        $this->assertNull($lead->notified_at);
        $this->assertStringContainsString('Connection could not be established', $lead->notify_error);
        $this->assertLessThanOrEqual(500, mb_strlen($lead->notify_error));
    }

    public function test_webhook_posts_json_with_bearer_and_marks_webhook_sent_at(): void
    {
        Mail::fake();
        Http::fake([self::WEBHOOK => Http::response(['ok' => true], 200)]);
        config(['webleads.webhook_url' => self::WEBHOOK, 'webleads.webhook_token' => 'token-secreto-123']);

        $this->send();

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === self::WEBHOOK
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer token-secreto-123')
                && $data['form_type'] === 'cotizacion'
                && $data['form_label'] === 'Cotización pública'
                && $data['email'] === 'ana@example.test'
                && $data['first_name'] === 'Ana'
                && $data['gclid'] === 'GCLID123'
                && $data['utm_campaign'] === 'calibracion'
                && isset($data['id'], $data['created_at']);
        });

        $lead = WebLead::firstOrFail();
        $this->assertNotNull($lead->webhook_sent_at);
        $this->assertNull($lead->webhook_error);
    }

    public function test_webhook_without_token_sends_no_authorization_header(): void
    {
        Mail::fake();
        Http::fake([self::WEBHOOK => Http::response([], 200)]);
        config(['webleads.webhook_url' => self::WEBHOOK]);

        $this->send();

        Http::assertSent(fn ($request) => !$request->hasHeader('Authorization'));
    }

    public function test_failing_webhook_records_error_and_keeps_the_request(): void
    {
        Mail::fake();
        Http::fake([self::WEBHOOK => Http::response('boom', 500)]);
        config(['webleads.webhook_url' => self::WEBHOOK, 'webleads.webhook_token' => 'token-secreto-123']);

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        $lead = WebLead::firstOrFail();
        $this->assertNull($lead->webhook_sent_at);
        $this->assertStringContainsString('500', $lead->webhook_error);
        $this->assertStringNotContainsString('token-secreto-123', (string) $lead->webhook_error);
        // El correo es independiente del webhook.
        $this->assertNotNull($lead->notified_at);
    }

    public function test_webhook_exception_records_error_and_mail_still_goes_out(): void
    {
        Mail::fake();
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: timeout calling ' . self::WEBHOOK);
        });
        config(['webleads.webhook_url' => self::WEBHOOK]);

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        $lead = WebLead::firstOrFail();
        $this->assertNull($lead->webhook_sent_at);
        $this->assertStringContainsString('timeout', $lead->webhook_error);
        $this->assertStringNotContainsString(self::WEBHOOK, $lead->webhook_error);
        $this->assertNotNull($lead->notified_at);
    }

    public function test_mail_failure_does_not_prevent_the_webhook(): void
    {
        Http::fake([self::WEBHOOK => Http::response([], 200)]);
        config(['webleads.webhook_url' => self::WEBHOOK]);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp caido'));

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        $lead = WebLead::firstOrFail();
        $this->assertSame('smtp caido', $lead->notify_error);
        $this->assertNotNull($lead->webhook_sent_at);
    }

    public function test_webhook_with_non_http_scheme_is_rejected_without_calling_out(): void
    {
        Mail::fake();
        Http::fake();
        config(['webleads.webhook_url' => 'file:///etc/passwd']);

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        Http::assertNothingSent();
        $this->assertNotNull(WebLead::firstOrFail()->webhook_error);
    }

    public function test_private_webhook_hosts_are_blocked_outside_local_and_testing(): void
    {
        Mail::fake();
        Http::fake();
        $this->app['env'] = 'production';
        config(['webleads.webhook_url' => 'http://127.0.0.1:5678/webhook/x']);

        // En "production" el CSRF si se verifica (ver prueba de 419 abajo): se manda un token valido.
        $this->withSession(['_token' => 'tok'])
            ->from('/cotizacion')
            ->post(route('web-leads.store'), $this->payload(['_token' => 'tok']))
            ->assertSessionHas('web_lead_status', 'ok');

        Http::assertNothingSent();
        $this->assertStringContainsString('red privada', WebLead::firstOrFail()->webhook_error);
    }

    public function test_no_webhook_configured_makes_no_http_calls(): void
    {
        Mail::fake();
        Http::fake();

        $this->send()->assertSessionHas('web_lead_status', 'ok');

        Http::assertNothingSent();
        $lead = WebLead::firstOrFail();
        $this->assertNull($lead->webhook_sent_at);
        $this->assertNull($lead->webhook_error);
    }

    public function test_sixth_submission_in_a_row_is_rate_limited(): void
    {
        Mail::fake();

        for ($i = 1; $i <= 5; $i++) {
            $this->send(['email' => "cliente$i@example.test"])->assertSessionHas('web_lead_status', 'ok');
        }

        $this->send(['email' => 'cliente6@example.test'])->assertStatus(429);

        $this->assertSame(5, WebLead::count());
    }

    /**
     * Laravel desactiva la verificacion CSRF cuando la app corre pruebas (VerifyCsrfToken::runningUnitTests),
     * y withMiddleware() no la reactiva. Para probarla de verdad se simula un entorno que no es de pruebas.
     */
    public function test_request_without_csrf_token_is_rejected_with_419(): void
    {
        Mail::fake();
        $this->app['env'] = 'production';

        $this->withMiddleware()
            ->from('/cotizacion')
            ->post(route('web-leads.store'), $this->payload())
            ->assertStatus(419);

        $this->assertSame(0, WebLead::count());
    }

    public function test_request_with_valid_csrf_token_is_accepted(): void
    {
        Mail::fake();
        $this->app['env'] = 'production';

        $this->withSession(['_token' => 'tok'])
            ->withMiddleware()
            ->from('/cotizacion')
            ->post(route('web-leads.store'), $this->payload(['_token' => 'tok']))
            ->assertSessionHas('web_lead_status', 'ok');

        $this->assertSame(1, WebLead::count());
    }

    public function test_json_clients_get_a_json_success(): void
    {
        Mail::fake();

        $this->postJson(route('web-leads.store'), $this->payload())
            ->assertOk()
            ->assertJson(['success' => true, 'form' => 'cotizacion']);

        $this->postJson(route('web-leads.store'), ['form_type' => 'cotizacion'])->assertStatus(422);
    }
}
