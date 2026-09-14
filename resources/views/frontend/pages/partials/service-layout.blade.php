{{--
    Layout compartido para las páginas de "Servicios" (rediseño 2026, basado en
    mockup de Claude Design). Cada página @include('frontend.pages.partials.service-layout', [...])
    pasando su propio contenido — este partial solo pone la estructura/estilos.

    Variables esperadas:
      badgeText        (string)
      heroTitle        (string)  <h1>
      heroDescription  (string)
      heroImage        (string)  ruta de asset(), ej. 'uploads/servicios/foo-1.png'
      heroImageAlt     (string)
      statValue1/Label1, statValue2/Label2 (string)
      badgeCardTitle, badgeCardSubtitle (string) — tarjeta flotante sobre la imagen del hero
      infoCards        (array) 2 items: ['icon' => <svg raw>, 'title' => .., 'body' => .., 'extra' => <html opcional>]
      processTitle, processSubtitle (string)
      processSteps     (array) items: ['title' => .., 'body' => .., 'deliverable' => ..]
      benefitsTitle, benefitsSubtitle (string)
      benefits         (array) items: ['title' => .., 'body' => ..]
      ctaTitle, ctaDescription (string)
      ctaBullets       (array) de strings
--}}
@php
    $phone = '8124738768';
    $phoneDisplay = '81 2473 8768';
    $whatsapp = 'https://wa.link/f28njw';
@endphp

@push('styles')
<style>
    @keyframes mdnPulse { 0%,100% { opacity: .35; transform: scale(1); } 50% { opacity: 1; transform: scale(1.35); } }
</style>
@endpush

<div style="width:100%;overflow-x:hidden">

  <section style="position:relative;background:#003E7E;color:#fff;overflow:hidden">
    <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF10 1px,transparent 1px),linear-gradient(90deg,#FFFFFF10 1px,transparent 1px);background-size:56px 56px;pointer-events:none"></div>
    <div style="position:absolute;top:-140px;right:-120px;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,#0A5AAF66,transparent 68%);pointer-events:none"></div>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(40px,6vw,80px) clamp(16px,4vw,32px) clamp(48px,6vw,88px);display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:clamp(32px,5vw,64px);align-items:center">
      <div style="min-width:0">
        <div style="display:inline-flex;align-items:center;gap:9px;padding:7px 14px;border:1px solid #FFFFFF33;border-radius:100px;font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#BFD6F0;margin-bottom:26px">
          <span style="width:6px;height:6px;border-radius:50%;background:#F2A900;animation:mdnPulse 2.4s ease-in-out infinite"></span>
          {{ $badgeText }}
        </div>
        <h1 style="margin:0 0 20px;font-size:clamp(32px,5.2vw,58px);line-height:1.04;font-weight:800;letter-spacing:-1.4px;text-wrap:balance">{{ $heroTitle }}</h1>
        <p style="margin:0 0 34px;font-size:clamp(16px,1.6vw,19px);line-height:1.6;color:#CBDCF0;max-width:52ch;text-wrap:pretty">{{ $heroDescription }}</p>
        <div style="display:flex;gap:14px;flex-wrap:wrap">
          <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="display:flex;align-items:center;gap:10px;padding:17px 26px;border-radius:5px;background:#F2A900;color:#16202B;font-size:16px;font-weight:800;white-space:nowrap">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"></path></svg>
            Llamar ahora
          </a>
          <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="display:flex;align-items:center;gap:10px;padding:17px 26px;border-radius:5px;border:1px solid #FFFFFF4D;color:#fff;font-size:16px;font-weight:700;white-space:nowrap">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.1-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.4-.2-2.8.7.7-2.7-.2-.4A8 8 0 0 1 12 4zm-3.2 4c-.3 0-.7.1-1 .5-.3.4-.8 1-.8 1.9 0 1 .7 2 1 2.4.3.4 1.8 2.9 4.5 3.9 2.2.8 2.7.7 3.2.6.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.1-.7.1l-.8 1c-.1.2-.3.2-.6.1a6.6 6.6 0 0 1-3.3-2.9c-.1-.3 0-.4.1-.6l.6-.7c.2-.2.2-.4.1-.6l-.8-2c-.1-.3-.3-.3-.5-.3z"></path></svg>
            Escribir por WhatsApp
          </a>
        </div>
        <div style="margin-top:36px;display:flex;gap:clamp(20px,4vw,44px);flex-wrap:wrap">
          <div style="min-width:0">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:clamp(22px,2.4vw,28px);font-weight:600;color:#fff">{{ $statValue1 }}</div>
            <div style="font-size:12.5px;font-weight:600;color:#9FBEDE;letter-spacing:.04em">{{ $statLabel1 }}</div>
          </div>
          <div style="width:1px;background:#FFFFFF26"></div>
          <div style="min-width:0">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:clamp(22px,2.4vw,28px);font-weight:600;color:#fff">{{ $statValue2 }}</div>
            <div style="font-size:12.5px;font-weight:600;color:#9FBEDE;letter-spacing:.04em">{{ $statLabel2 }}</div>
          </div>
        </div>
      </div>
      <div style="min-width:0;position:relative">
        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid #FFFFFF2E;box-shadow:0 30px 70px -30px #00152C;aspect-ratio:4/3">
          <img src="{{ asset($heroImage) }}" alt="{{ $heroImageAlt }}" style="width:100%;height:100%;object-fit:cover;display:block">
        </div>
        <div style="position:absolute;bottom:-18px;left:-18px;background:#fff;border:1px solid #DDE3EA;border-radius:6px;padding:14px 18px;box-shadow:0 18px 40px -18px #00152C99;display:flex;align-items:center;gap:12px">
          <div style="width:34px;height:34px;border-radius:50%;background:#EAF2FB;display:grid;place-items:center">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 3.8 8.6 9 9 5.2-.4 9-4 9-9V7l-9-5z"></path><path d="m9 12 2 2 4-4"></path></svg>
          </div>
          <div style="line-height:1.25">
            <div style="font-size:13.5px;font-weight:800;color:#16202B">{{ $badgeCardTitle }}</div>
            <div style="font-size:12px;font-weight:600;color:#6B7A89">{{ $badgeCardSubtitle }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section style="background:#fff">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,4vw,32px);display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:clamp(28px,4vw,48px);align-items:stretch">
      @foreach ($infoCards as $card)
        <div style="min-width:0;display:flex;flex-direction:column;gap:18px;padding-top:6px">
          <div style="width:46px;height:46px;border-radius:5px;background:#EAF2FB;border:1px solid #DDE3EA;display:grid;place-items:center">
            {!! $card['icon'] !!}
          </div>
          <h2 style="margin:0;font-size:clamp(22px,2.4vw,27px);line-height:1.2;font-weight:800;letter-spacing:-.6px;color:#16202B">{{ $card['title'] }}</h2>
          <p style="margin:0;font-size:15.5px;line-height:1.7;color:#4C5B6B;text-wrap:pretty">{{ $card['body'] }}</p>
          @if (!empty($card['extra']))
            {!! $card['extra'] !!}
          @endif
        </div>
      @endforeach
    </div>
  </section>

  <section style="background:#F7F9FC;border-top:1px solid #DDE3EA;border-bottom:1px solid #DDE3EA">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,4vw,32px)">
      <div style="display:flex;justify-content:space-between;gap:28px;flex-wrap:wrap;align-items:end;margin-bottom:clamp(28px,4vw,48px)">
        <div style="min-width:0;max-width:640px">
          <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#003E7E;margin-bottom:14px">Nuestro proceso</div>
          <h2 style="margin:0 0 14px;font-size:clamp(26px,3.4vw,40px);line-height:1.12;font-weight:800;letter-spacing:-1px;color:#16202B;text-wrap:balance">{{ $processTitle }}</h2>
          <p style="margin:0;font-size:16px;line-height:1.65;color:#4C5B6B;max-width:56ch">{{ $processSubtitle }}</p>
        </div>
      </div>

      <div style="min-width:0;display:flex;flex-direction:column;gap:10px">
        @foreach ($processSteps as $i => $step)
          <div class="mdn-svc-step" style="background:#fff;border:1px solid #DDE3EA;border-radius:6px;overflow:hidden;transition:box-shadow .2s">
            <button type="button" class="mdn-svc-step-toggle" onclick="mdnServiceStepToggle(this)" style="width:100%;display:flex;align-items:center;gap:16px;padding:18px 20px;background:none;border:0;cursor:pointer;text-align:left">
              <span class="mdn-svc-step-num" style="flex:none;width:36px;height:36px;border-radius:50%;display:grid;place-items:center;font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:600;border:1px solid {{ $i === 0 ? '#003E7E' : '#DDE3EA' }};background:{{ $i === 0 ? '#003E7E' : '#F7F9FC' }};color:{{ $i === 0 ? '#FFFFFF' : '#6B7A89' }}">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
              <span style="flex:1;min-width:0;font-size:clamp(15px,1.6vw,17px);font-weight:800;letter-spacing:-.3px;color:#16202B;line-height:1.3">{{ $step['title'] }}</span>
              <span class="mdn-svc-step-caret" style="flex:none;color:#003E7E;transform:{{ $i === 0 ? 'rotate(180deg)' : 'rotate(0deg)' }};transition:transform .25s">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
              </span>
            </button>
            <div class="mdn-svc-step-body" style="display:{{ $i === 0 ? 'block' : 'none' }};padding:0 20px 20px 72px">
              <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#4C5B6B;text-wrap:pretty">{{ $step['body'] }}</p>
              <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#003E7E;letter-spacing:.02em;line-height:1.4">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                {{ $step['deliverable'] }}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section style="background:#fff">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,4vw,32px)">
      <div style="min-width:0;max-width:640px;margin-bottom:clamp(26px,4vw,42px)">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#003E7E;margin-bottom:14px">Beneficios</div>
        <h2 style="margin:0 0 16px;font-size:clamp(26px,3.4vw,40px);line-height:1.12;font-weight:800;letter-spacing:-1px;color:#16202B;text-wrap:balance">{{ $benefitsTitle }}</h2>
        <p style="margin:0;font-size:16px;line-height:1.65;color:#4C5B6B;max-width:46ch;text-wrap:pretty">{{ $benefitsSubtitle }}</p>
      </div>
      <div style="min-width:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:2px;background:#DDE3EA;border:1px solid #DDE3EA;border-radius:6px;overflow:hidden">
        @foreach ($benefits as $b)
          <div style="background:#fff;padding:22px;display:flex;flex-direction:column;gap:11px">
            <div style="width:26px;height:26px;border-radius:50%;background:#003E7E;display:grid;place-items:center;flex:none">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
            </div>
            <div style="font-size:15.5px;font-weight:800;letter-spacing:-.3px;color:#16202B;line-height:1.3">{{ $b['title'] }}</div>
            <div style="font-size:14px;line-height:1.6;color:#5A6875;text-wrap:pretty">{{ $b['body'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section style="position:relative;background:#003E7E;color:#fff;overflow:hidden">
    <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF0D 1px,transparent 1px),linear-gradient(90deg,#FFFFFF0D 1px,transparent 1px);background-size:56px 56px;pointer-events:none"></div>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,4vw,32px) clamp(56px,7vw,104px);display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(32px,4vw,64px);align-items:start">
      <div style="min-width:0">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#8FB6DE;margin-bottom:14px">Solicita tu visita técnica</div>
        <h2 style="margin:0 0 18px;font-size:clamp(26px,3.4vw,40px);line-height:1.12;font-weight:800;letter-spacing:-1px;text-wrap:balance">{{ $ctaTitle }}</h2>
        <p style="margin:0 0 32px;font-size:16.5px;line-height:1.65;color:#C7DAEE;max-width:48ch;text-wrap:pretty">{{ $ctaDescription }}</p>
        <ul style="margin:0 0 32px;padding:0;list-style:none;display:flex;flex-direction:column;gap:14px">
          @foreach ($ctaBullets as $bullet)
            <li style="display:flex;gap:12px;align-items:flex-start;font-size:15px;line-height:1.5;color:#E4EEF8"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F2A900" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:2px"><path d="M20 6 9 17l-5-5"></path></svg>{{ $bullet }}</li>
          @endforeach
        </ul>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="display:flex;align-items:center;gap:9px;padding:14px 20px;border:1px solid #FFFFFF40;border-radius:5px;color:#fff;font-size:15px;font-weight:700">{{ $phoneDisplay }}</a>
          <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="display:flex;align-items:center;gap:9px;padding:14px 20px;border:1px solid #FFFFFF40;border-radius:5px;color:#fff;font-size:15px;font-weight:700">WhatsApp directo</a>
        </div>
      </div>

      <div style="min-width:0;background:#fff;border-radius:8px;border:1px solid #DDE3EA;box-shadow:0 40px 80px -40px #00152C;padding:clamp(24px,3.5vw,40px);display:flex;flex-direction:column;gap:22px">
        <div style="display:flex;align-items:center;gap:12px;padding-bottom:20px;border-bottom:1px solid #DDE3EA">
          <div style="width:40px;height:40px;border-radius:50%;background:#EAF2FB;display:grid;place-items:center;flex:none">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="#003E7E"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.1-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.4-.2-2.8.7.7-2.7-.2-.4A8 8 0 0 1 12 4zm-3.2 4c-.3 0-.7.1-1 .5-.3.4-.8 1-.8 1.9 0 1 .7 2 1 2.4.3.4 1.8 2.9 4.5 3.9 2.2.8 2.7.7 3.2.6.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.1-.7.1l-.8 1c-.1.2-.3.2-.6.1a6.6 6.6 0 0 1-3.3-2.9c-.1-.3 0-.4.1-.6l.6-.7c.2-.2.2-.4.1-.6l-.8-2c-.1-.3-.3-.3-.5-.3z" /></svg>
          </div>
          <div style="min-width:0">
            <div style="font-size:17px;font-weight:800;letter-spacing:-.3px;color:#16202B;line-height:1.25">Atención directa por WhatsApp</div>
            <div style="font-size:13px;font-weight:600;color:#6B7A89">Lunes a viernes, 8:00 a 18:00 h</div>
          </div>
        </div>

        <p style="margin:0;font-size:15.5px;line-height:1.7;color:#4C5B6B;text-wrap:pretty">Mándanos un mensaje contándonos tu caso. Un especialista te responde con la propuesta técnica y la cotización del servicio.</p>

        <div style="display:flex;flex-direction:column;gap:10px">
          <div style="display:flex;gap:10px;align-items:flex-start;font-size:14.5px;line-height:1.5;color:#16202B;font-weight:600"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:2px"><path d="M20 6 9 17l-5-5" /></svg>Puedes enviarnos fotos del equipo o del tablero actual</div>
          <div style="display:flex;gap:10px;align-items:flex-start;font-size:14.5px;line-height:1.5;color:#16202B;font-weight:600"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:2px"><path d="M20 6 9 17l-5-5" /></svg>Agendamos la visita técnica sin compromiso</div>
          <div style="display:flex;gap:10px;align-items:flex-start;font-size:14.5px;line-height:1.5;color:#16202B;font-weight:600"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:2px"><path d="M20 6 9 17l-5-5" /></svg>Respuesta el mismo día hábil</div>
        </div>

        <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="display:flex;align-items:center;justify-content:center;gap:11px;padding:19px 26px;border-radius:5px;background:#003E7E;color:#fff;font-size:17px;font-weight:800;letter-spacing:-.2px">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.1-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.4-.2-2.8.7.7-2.7-.2-.4A8 8 0 0 1 12 4zm-3.2 4c-.3 0-.7.1-1 .5-.3.4-.8 1-.8 1.9 0 1 .7 2 1 2.4.3.4 1.8 2.9 4.5 3.9 2.2.8 2.7.7 3.2.6.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.1-.7.1l-.8 1c-.1.2-.3.2-.6.1a6.6 6.6 0 0 1-3.3-2.9c-.1-.3 0-.4.1-.6l.6-.7c.2-.2.2-.4.1-.6l-.8-2c-.1-.3-.3-.3-.5-.3z" /></svg>
          Iniciar conversación
        </a>
        <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:17px 24px;border-radius:5px;border:1px solid #DDE3EA;color:#003E7E;font-size:16px;font-weight:700">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z" /></svg>
          Llamar al {{ $phoneDisplay }}
        </a>
        <div style="font-size:12.5px;color:#6B7A89;line-height:1.5;text-align:center">Atendemos Monterrey, su área metropolitana y todo el noreste del país.</div>
      </div>
    </div>
  </section>

</div>

@push('scripts')
<script>
    if (typeof mdnServiceStepToggle !== 'function') {
        function mdnServiceStepToggle(button) {
            const step = button.closest('.mdn-svc-step');
            const body = step.querySelector('.mdn-svc-step-body');
            const num = step.querySelector('.mdn-svc-step-num');
            const caret = step.querySelector('.mdn-svc-step-caret');
            const isOpen = body.style.display === 'block';

            document.querySelectorAll('.mdn-svc-step').forEach((el) => {
                el.querySelector('.mdn-svc-step-body').style.display = 'none';
                el.querySelector('.mdn-svc-step-num').style.background = '#F7F9FC';
                el.querySelector('.mdn-svc-step-num').style.borderColor = '#DDE3EA';
                el.querySelector('.mdn-svc-step-num').style.color = '#6B7A89';
                el.querySelector('.mdn-svc-step-caret').style.transform = 'rotate(0deg)';
            });

            if (!isOpen) {
                body.style.display = 'block';
                num.style.background = '#003E7E';
                num.style.borderColor = '#003E7E';
                num.style.color = '#FFFFFF';
                caret.style.transform = 'rotate(180deg)';
            }
        }
    }
</script>
@endpush
