{{-- Medición uniforme de eventos (WhatsApp, llamadas, correo, carrito, cotización) en track_conversions.
     Incluir UNA vez por página, después de attribution-capture: @include('frontend.partials.mdn-events')

     El folio de contacto (MN + 5 caracteres en el mensaje de WhatsApp / asunto del correo) lo controla
     config/attribution.php: con ATTRIBUTION_FOLIO_ENABLED=false (por defecto) mdn-contact.js queda inerte
     y ningún enlace cambia.

     Modo prueba de atribución (Laboratorio del admin; AttributionTestMode::payload() = cookie de prueba válida):
     el folio queda ENCENDIDO en todos los placements y el número de WhatsApp pasa a ser el de prueba
     (ATTRIBUTION_TEST_WHATSAPP_NUMBER; vacío = https://wa.me/?text=... sin número, nunca la línea real), se
     imprime window.MDN_TEST_MODE y una pill fija "Modo prueba de atribución activo". Fuera de modo prueba
     el HTML es el de siempre. --}}
@php
    $mdnTestPayload = \App\Support\Attribution\AttributionTestMode::payload();
    $mdnTestMode = $mdnTestPayload !== null;
    $attr = config('attribution', []);
    $tpl = $attr['templates'] ?? [];
    $folioCfg = [
        'enabled' => $mdnTestMode ? true : (bool) ($attr['folio_enabled'] ?? false),
        'placements' => $mdnTestMode ? '*' : (string) ($attr['folio_placements'] ?? '*'),
        'invisible' => (bool) ($attr['invisible_folio'] ?? false),
        'whatsapp' => [
            'url' => (string) config('contact.whatsapp.url'),
            'number' => $mdnTestMode
                ? \App\Support\Attribution\AttributionTestMode::whatsappNumber()
                : (string) config('contact.whatsapp.number'),
        ],
        'templates' => [
            'es' => [
                'product' => $tpl['es']['whatsapp']['product'] ?? 'Hola, me interesa: {producto}. Folio: {folio}',
                'generic' => $tpl['es']['whatsapp']['generic'] ?? 'Hola, quiero más información. Folio: {folio}',
            ],
            'pt' => [
                'product' => $tpl['pt']['whatsapp']['product'] ?? 'Olá, tenho interesse em: {produto}. Código: {folio}',
                'generic' => $tpl['pt']['whatsapp']['generic'] ?? 'Olá, quero mais informações. Código: {folio}',
            ],
        ],
        'mail' => [
            'es' => $tpl['es']['mail_subject'] ?? 'Cotización web - Folio {folio}',
            'pt' => $tpl['pt']['mail_subject'] ?? 'Cotação web - Código {folio}',
        ],
    ];
    // Solo en modo prueba: mdn-contact.js acepta entonces whatsapp.number vacío (wa.me/?text=…, sin número).
    if ($mdnTestMode) {
        $folioCfg['testMode'] = true;
    }
@endphp
@if($mdnTestMode)
<script>
    window.MDN_TEST_MODE = @json($mdnTestPayload['n']);
    (function () {
        function show() { window.MDNTestMode && window.MDNTestMode.banner(document); }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', show); else show();
    })();
</script>
@endif
<script>
    window.MDNEventsConfig = {
        url: @json(route('track.conversion')),
        csrf: @json(csrf_token()),
        folio: @json($folioCfg)
    };
</script>
<script defer src="{{ asset('frontend/js/mdn-contact.js') }}?v={{ @filemtime(public_path('frontend/js/mdn-contact.js')) }}"></script>
<script defer src="{{ asset('frontend/js/mdn-events.js') }}?v={{ @filemtime(public_path('frontend/js/mdn-events.js')) }}"></script>
