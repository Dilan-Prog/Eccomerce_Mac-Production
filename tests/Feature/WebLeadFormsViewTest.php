<?php

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Formularios publicos de solicitud (asociado, calibracion y puesta en marcha, medicion, cotizacion):
 * ya no envian a formsubmit.co sino a route('web-leads.store'), con CSRF, honeypot y campos canonicos.
 *
 * Las paginas completas (layout master) consultan varias tablas del sitio que no existen en la SQLite en memoria
 * de `php artisan test`, asi que cada vista real se renderiza con un layout master minimo (solo @yield('content'))
 * inyectado en setUp; el contenido y el formulario son los reales. Los parciales se prueban tambien por separado.
 */
class WebLeadFormsViewTest extends TestCase
{
    /** vista => form_type esperado */
    private const PAGES = [
        'frontend.pages.associate_page' => 'asociado',
        'frontend.pages.calibracion-puesta' => 'calibracion_puesta',
        'frontend.pages.medicion' => 'medicion',
        'frontend.pages.quotes' => 'cotizacion',
    ];

    private ?string $fakeLayoutDir = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Layout falso: el master real consulta categories, sliders, etc. (tablas MySQL ausentes en SQLite :memory:).
        // Se antepone a las rutas de vistas para que @extends('frontend.layouts.master') resuelva a este.
        $this->fakeLayoutDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webleads-layout-' . uniqid();
        mkdir($this->fakeLayoutDir . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'layouts', 0777, true);
        file_put_contents(
            $this->fakeLayoutDir . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'master.blade.php',
            "@yield('content')"
        );
        $this->app['view']->getFinder()->prependLocation($this->fakeLayoutDir);
    }

    protected function tearDown(): void
    {
        if ($this->fakeLayoutDir && is_dir($this->fakeLayoutDir)) {
            @unlink($this->fakeLayoutDir . '/frontend/layouts/master.blade.php');
            @rmdir($this->fakeLayoutDir . '/frontend/layouts');
            @rmdir($this->fakeLayoutDir . '/frontend');
            @rmdir($this->fakeLayoutDir);
        }
        parent::tearDown();
    }

    /** Renderiza la vista de contenido con un layout minimo (solo el @yield('content')). */
    private function renderContent(string $view, array $data = []): string
    {
        $data += [
            'settings' => new \App\Models\GeneralSetting(['site_name' => 'Mac Del Norte']),
            'errors' => new ViewErrorBag(),
        ];

        return view($view, $data)->render();
    }

    public static function pagesProvider(): array
    {
        $out = [];
        foreach (self::PAGES as $view => $type) {
            $out[$view] = [$view, $type];
        }

        return $out;
    }

    /**
     * @dataProvider pagesProvider
     */
    public function test_form_posts_to_own_backend_with_canonical_fields(string $view, string $type): void
    {
        $html = $this->renderContent($view);

        $this->assertNotSame('', $html, 'La seccion content no se renderizo.');
        $this->assertStringNotContainsString('formsubmit.co', $html);
        $this->assertStringNotContainsString('dilanp270105', $html);
        $this->assertStringNotContainsString('name="_next"', $html);
        $this->assertStringNotContainsString('name="_captcha"', $html);

        $this->assertStringContainsString('action="' . route('web-leads.store') . '"', $html);
        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('id="web-lead-form"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="first_name"', $html);
        $this->assertStringContainsString('name="last_name"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="company"', $html);
        $this->assertStringContainsString('name="city"', $html);
        $this->assertStringContainsString('name="state"', $html);
        $this->assertStringContainsString('name="operation"', $html);
        $this->assertStringContainsString('name="message"', $html);
        $this->assertStringContainsString('name="website"', $html);
        $this->assertStringContainsString('name="form_started_at"', $html);
        $this->assertStringContainsString('<input type="hidden" name="form_type" value="' . $type . '">', $html);
        $this->assertStringContainsString('frontend/js/web-lead-form.js', $html);

        // Un solo campo por nombre canonico (antes habia dos name="Ciudad").
        foreach (['company', 'city', 'first_name', 'email', 'state', 'operation', 'message'] as $name) {
            $this->assertSame(1, substr_count($html, 'name="' . $name . '"'), "name=\"$name\" deberia aparecer una vez");
        }
        // Nombres viejos fuera.
        foreach (['Ciudad', 'Nombre', 'Apellido', 'Telefono', 'Estado', 'Operacion', 'Mensaje', 'Empresa'] as $old) {
            $this->assertStringNotContainsString('name="' . $old . '"', $html);
        }
        foreach (['source_page', 'landing_page', 'referrer', 'session_id', 'gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $attr) {
            $this->assertStringContainsString('name="' . $attr . '"', $html);
        }
    }

    /**
     * @dataProvider pagesProvider
     */
    public function test_honeypot_is_offscreen_not_display_none(string $view, string $type): void
    {
        $html = $this->renderContent($view);

        $this->assertMatchesRegularExpression('/<input type="text" name="website"[^>]*tabindex="-1"[^>]*autocomplete="off"/', $html);
        $this->assertStringContainsString('position:absolute;left:-9999px', $html);
        $this->assertStringNotContainsString('display:none', $html);
    }

    public function test_old_input_and_errors_are_rendered_and_alert_shows(): void
    {
        $errors = (new ViewErrorBag())->put('default', new MessageBag(['email' => ['El email no es valido.']]));

        // old() lee del request actual: se le pone una sesion con el input previo.
        $session = $this->app['session.store'];
        $session->put('_old_input', [
            'form_type' => 'cotizacion', 'first_name' => 'Ana', 'email' => 'ana@example.test',
            'state' => 'Nuevo León', 'operation' => 'Servicio Medición De Gas', 'message' => 'Hola mundo',
        ]);
        $this->app['request']->setLaravelSession($session);

        $html = $this->renderContent('frontend.pages.quotes', ['errors' => $errors]);

        $this->assertStringContainsString('value="Ana"', $html);
        $this->assertStringContainsString('value="ana@example.test"', $html);
        $this->assertStringContainsString('El email no es valido.', $html);
        $this->assertStringContainsString('Revisa los campos marcados e intenta de nuevo.', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('<option selected>Nuevo Le&oacute;n</option>', $html);
        $this->assertStringContainsString('<option selected>Servicio Medici&oacute;n De Gas</option>', $html);
        $this->assertStringContainsString('>Hola mundo</textarea>', $html);
    }

    public function test_success_alert_only_for_matching_form(): void
    {
        $this->assertStringNotContainsString('alert', view('frontend.partials.web-lead-alert', ['formType' => 'medicion', 'errors' => new ViewErrorBag()])->render());

        $session = $this->app['session.store'];
        $session->put('web_lead_status', 'ok');
        $session->put('web_lead_form', 'medicion');
        $this->app['request']->setLaravelSession($session);

        $html = view('frontend.partials.web-lead-alert', ['formType' => 'medicion', 'errors' => new ViewErrorBag()])->render();
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Te contactaremos pronto.', $html);

        $other = view('frontend.partials.web-lead-alert', ['formType' => 'asociado', 'errors' => new ViewErrorBag()])->render();
        $this->assertStringNotContainsString('role="status"', $other);
    }

    public function test_fields_partial_includes_script_once(): void
    {
        // Dos includes en la misma pagina (un solo render): @once debe emitir el script una sola vez.
        $html = \Illuminate\Support\Facades\Blade::render(
            "@include('frontend.partials.web-lead-fields', ['formType' => 'medicion'])@include('frontend.partials.web-lead-fields', ['formType' => 'cotizacion'])"
        );

        $this->assertSame(2, substr_count($html, 'name="website"'));
        $this->assertSame(1, substr_count($html, 'frontend/js/web-lead-form.js'));
    }

    public function test_js_file_uses_attribution_api_defensively(): void
    {
        $js = file_get_contents(public_path('frontend/js/web-lead-form.js'));

        $this->assertStringContainsString('MDNAttribution', $js);
        $this->assertStringContainsString('pageshow', $js);
        $this->assertStringContainsString('try', $js);
    }
}
