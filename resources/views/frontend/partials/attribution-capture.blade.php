{{-- Captura de atribución (gclid/gbraid/wbraid/fbclid/UTM/referrer/sesión) + registro "page_visit" en Google Sheets.
     Incluir dentro de <head>: @include('frontend.partials.attribution-capture') --}}
<script src="{{ asset('frontend/js/attribution.js') }}?v={{ @filemtime(public_path('frontend/js/attribution.js')) }}"></script>
<script>
(function() {
    try {
        window.MDNAttribution && window.MDNAttribution.capture();
    } catch (e) {
        console.warn('No se pudo capturar la atribución:', e);
    }

    // Registro de la visita en el backend (type "page_visit"): una vez por sesión, y otra vez si llega
    // un click id nuevo (gclid/gbraid/wbraid/fbclid) dentro de la misma sesión. El event_id es
    // determinista, asi que un reintento o recarga no duplica la fila.
    try {
        const attr = window.MDNAttribution && window.MDNAttribution.get();
        if (attr && attr.session_id) {
            const clickId = attr.gclid || attr.gbraid || attr.wbraid || attr.fbclid || '';
            let h = 5381;
            for (let i = 0; i < clickId.length; i++) { h = ((h << 5) + h + clickId.charCodeAt(i)) >>> 0; }
            const visitKey = 'v-' + attr.session_id + (clickId ? '-' + h.toString(36) : '');
            let alreadySent = false;
            try { alreadySent = localStorage.getItem('mdn_visit_sent') === visitKey; } catch (e) {}

            if (!alreadySent) {
                try { localStorage.setItem('mdn_visit_sent', visitKey); } catch (e) {}
                const visitPayload = Object.assign({}, attr, {
                    type: 'page_visit',
                    page_url: window.location.href,
                    event_id: visitKey
                });
                fetch('{{ route('track.conversion') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(visitPayload),
                    keepalive: true
                }).catch(function(err) {
                    console.warn('No se pudo registrar la visita en el backend:', err);
                });
            }
        }
    } catch (e) {
        console.warn('No se pudo registrar la visita en el backend:', e);
    }

    // Registro de visita con parámetros de campaña en Google Sheets (contrato del webhook sin cambios).
    try {
        const params = new URLSearchParams(window.location.search);
        const googleSheetsWebhook = 'https://script.google.com/macros/s/AKfycbwU_alwJ8RczaMMaRWUCcBD2Pc9exMGsG5vWGX-J7-h5BQajHC43VR3Ufk3QiGeQtZF/exec';

        if (params.has('gclid') || params.has('utm_source')) {
            fetch(googleSheetsWebhook, {
                method: 'POST',
                mode: 'no-cors',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    gclid: params.get('gclid') || '',
                    utm_source: params.get('utm_source') || '',
                    utm_medium: params.get('utm_medium') || '',
                    utm_campaign: params.get('utm_campaign') || '',
                    landing_page: window.location.pathname,
                    type: 'page_visit',
                    fecha: new Date().toLocaleString('sv-SE', { timeZone: 'America/Mexico_City' })
                })
            }).catch(function(err) {
                console.warn('No se pudo registrar la visita:', err);
            });
        }
    } catch (e) {
        console.warn('No se pudo registrar la visita:', e);
    }
})();
</script>
