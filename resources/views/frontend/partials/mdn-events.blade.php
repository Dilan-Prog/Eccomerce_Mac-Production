{{-- Medición uniforme de eventos (WhatsApp, llamadas, correo, carrito, cotización) en track_conversions.
     Incluir UNA vez por página, después de attribution-capture: @include('frontend.partials.mdn-events')

     El folio de contacto (MN + 5 caracteres en el mensaje de WhatsApp / asunto del correo) lo controla
     config/attribution.php: con ATTRIBUTION_FOLIO_ENABLED=false (por defecto) mdn-contact.js queda inerte
     y ningún enlace cambia. --}}
@php
    $attr = config('attribution', []);
    $tpl = $attr['templates'] ?? [];
    $folioCfg = [
        'enabled' => (bool) ($attr['folio_enabled'] ?? false),
        'placements' => (string) ($attr['folio_placements'] ?? '*'),
        'invisible' => (bool) ($attr['invisible_folio'] ?? false),
        'whatsapp' => [
            'url' => (string) config('contact.whatsapp.url'),
            'number' => (string) config('contact.whatsapp.number'),
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
@endphp
<script>
    window.MDNEventsConfig = {
        url: @json(route('track.conversion')),
        csrf: @json(csrf_token()),
        folio: @json($folioCfg)
    };
</script>
<script defer src="{{ asset('frontend/js/mdn-contact.js') }}?v={{ @filemtime(public_path('frontend/js/mdn-contact.js')) }}"></script>
<script defer src="{{ asset('frontend/js/mdn-events.js') }}?v={{ @filemtime(public_path('frontend/js/mdn-events.js')) }}"></script>
