{{-- Medición uniforme de eventos (WhatsApp, llamadas, carrito, cotización) en track_conversions.
     Incluir UNA vez por página, después de attribution-capture: @include('frontend.partials.mdn-events') --}}
<script>
    window.MDNEventsConfig = {
        url: @json(route('track.conversion')),
        csrf: @json(csrf_token())
    };
</script>
<script defer src="{{ asset('frontend/js/mdn-events.js') }}?v={{ @filemtime(public_path('frontend/js/mdn-events.js')) }}"></script>
