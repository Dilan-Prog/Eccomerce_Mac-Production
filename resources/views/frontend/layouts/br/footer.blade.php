{{-- Pie de la sección Brasil. Sin métodos de pago ni envíos a México. --}}
@php
    $br = config('brasil');
@endphp

@push('styles')
<style>
    .brf { background: var(--azul-oscuro, #002856); color: #C8D9EC; margin-top: 0; }
    .brf-inner { max-width: 1240px; margin: 0 auto; padding: 48px 20px 28px; display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; }
    .brf h3 { color: #fff; font-size: 14px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin: 0 0 14px; }
    .brf p { margin: 0 0 12px; font-size: 14px; line-height: 1.65; }
    .brf a { color: #fff; text-decoration: none; }
    .brf a:hover { text-decoration: underline; }
    .brf ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 9px; font-size: 14px; }
    .brf-logo img { height: 44px; width: auto; margin-bottom: 16px; }
    .brf-badge { display: inline-block; border: 1px solid #4A709C; color: #fff; font-size: 12px; font-weight: 600; padding: 5px 10px; border-radius: 4px; margin-top: 4px; }
    .brf-wa { display: inline-flex; align-items: center; gap: 8px; background: #25D366; color: #fff !important; font-weight: 700; font-size: 14px; padding: 11px 18px; border-radius: 999px; margin-top: 6px; }
    .brf-wa:hover { text-decoration: none !important; filter: brightness(1.05); }
    .brf-wa svg { width: 18px; height: 18px; fill: currentColor; }
    .brf-muted { font-size: 12.5px; color: #8FA9C6; }
    .brf-bottom { border-top: 1px solid #1F4874; }
    .brf-bottom-inner { max-width: 1240px; margin: 0 auto; padding: 16px 20px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; font-size: 12.5px; color: #8FA9C6; }
    .brf-bottom a { color: #C8D9EC; }
    .brf-bottom-links { display: flex; gap: 18px; flex-wrap: wrap; }
    @media (max-width: 991px) { .brf-inner { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 575px) { .brf-inner { grid-template-columns: 1fr; gap: 28px; padding-top: 34px; } .brf-bottom-inner { justify-content: center; text-align: center; } }
</style>
@endpush

<footer class="brf">
    <div class="brf-inner">
        <div>
            <div class="brf-logo">
                <img src="{{ asset('uploads/logo/2k-blanco-azul.png') }}" alt="Mac del Norte" width="200" height="44" loading="lazy">
            </div>
            <p>Distribuidora e comercializadora de produtos industriais com sede em Monterrey, México. Especialistas em instrumentação, automação e controle, com atendimento em português para clientes no Brasil.</p>
            <span class="brf-badge">Distribuidor autorizado Honeywell</span>
        </div>

        <div>
            <h3>Produtos para o Brasil</h3>
            <ul>
                {{-- Enlace interno entre todas las landings: reparte autoridad
                     dentro de la seccion. Se alimenta de config('brasil.landings'). --}}
                @foreach(config('brasil.landings') as $l)
                    <li><a href="{{ route($l['ruta']) }}">{{ $l['marca'] }} {{ $l['nombre'] }}</a></li>
                @endforeach
            </ul>
            <p class="brf-muted" style="margin-top:14px">Não encontrou o seu modelo? Envie o número de peça pelo WhatsApp.</p>
        </div>

        <div>
            <h3>Contato</h3>
            <p>{{ $br['hours'] }}</p>
            <ul>
                <li><a href="mailto:{{ $br['email'] }}">{{ $br['email'] }}</a></li>
                <li><a href="{{ $br['phone_href'] }}">{{ $br['phone'] }}</a></li>
                <li>Monterrey, Nuevo León, México</li>
            </ul>
            <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="brf-wa track-conversion" data-type="whatsapp_br_footer">
                <svg viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.89 9.88zm8.41-18.3A11.8 11.8 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.33 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41z"/></svg>
                Falar no WhatsApp
            </a>
        </div>
    </div>

    <div class="brf-bottom">
        <div class="brf-bottom-inner">
            <span>© {{ date('Y') }} Mac del Norte · Monterrey, México</span>
            <div class="brf-bottom-links">
                <a href="{{ route('index') }}" hreflang="es-MX">Site México (ES)</a>
                <a href="{{ route('Terminos-Condiciones') }}" hreflang="es-MX">Termos e condições (ES)</a>
                <a href="{{ route('Aviso-Privacidad') }}" hreflang="es-MX">Aviso de privacidade (ES)</a>
            </div>
        </div>
    </div>
</footer>
