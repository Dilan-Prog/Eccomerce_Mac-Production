{{--
    Layout compartido para las páginas de categoría de Servicios
    (Instalación y Configuración / Reparación de Equipos / Calibraciones).
    Basado en mockup de Claude Design, adaptado a la paleta/tipografía ya
    usada en el resto del sitio (#003E7E / #F2A900 / IBM Plex Mono).

    Hero con índice de anclas + N secciones en zigzag enriquecido (azul/
    blanco alternado, checklist + chips de equipo) + metodología + "por
    qué Mac del Norte" + FAQ + CTA final.

    Variables esperadas:
      heroBadge, heroTitle, heroDescription (string)
      stats          (array) 3 items: ['value'=>..,'label'=>..]
      services       (array) items: ['id'=>'ancla','n'=>'01','short'=>..,
        'title'=>..,'description'=>..,'bullets'=>[..],'chipsLabel'=>..,
        'chips'=>[..],'badge'=>..,'norm'=>..,'image'=>..,'imageAlt'=>..,
        'detailsRoute'=>..]
      processBadge, processTitle, processDescription (string)
      processSteps   (array) items: ['n'=>'01','title'=>..,'description'=>..]
      whyBadge, whyTitle, whyDescription (string)
      industries     (array) de strings
      values         (array) 4 items: ['title'=>..,'description'=>..]
      faqBadge (string)
      faq            (array) items: ['q'=>..,'a'=>..]
      ctaTitle (string)
--}}
@php
    $phone = '8124738768';
    $phoneDisplay = '81 2473 8768';
    $whatsapp = 'https://wa.link/f28njw';
@endphp

@push('styles')
<style>
    .mdn-cd-index { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:2px; transform:translateY(40px); }
    .mdn-cd-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr)); gap:48px; align-items:center; }
    .mdn-cd-bullets { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px 20px; }
    .mdn-cd-steps { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:2px; }
    .mdn-cd-why { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,400px),1fr)); gap:48px; }
    .mdn-cd-values { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; }
    .mdn-cd-faq { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,440px),1fr)); gap:0 48px; }
    .mdn-cd-faq-item { cursor:pointer; }
    .mdn-cd-faq-item .mdn-cd-faq-a { display:none; }
    .mdn-cd-faq-item.is-open .mdn-cd-faq-a { display:block; }
    .mdn-cd-faq-item.is-open .mdn-cd-faq-sign { transform:rotate(45deg); }
    .mdn-cd-img-wrap { position:relative; min-height:0; }
    .mdn-cd-img-wrap img { aspect-ratio:1/1; min-height:0; }
</style>
@endpush

<div style="width:100%;overflow-x:hidden">

  {{-- HERO --}}
  <section style="position:relative;background:#003E7E;color:#fff">
    <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF0B 1px,transparent 1px),linear-gradient(90deg,#FFFFFF0B 1px,transparent 1px);background-size:56px 56px;pointer-events:none;overflow:hidden"></div>
    <div style="position:relative;max-width:1160px;margin:0 auto;padding:clamp(56px,7vw,88px) clamp(16px,4vw,32px) 0;display:flex;flex-direction:column;align-items:center;text-align:center;gap:20px">
      <div style="border:1px solid #FFFFFF4D;border-radius:999px;padding:8px 18px;font-size:11px;font-weight:700;letter-spacing:.14em;color:#DCE8FA;text-transform:uppercase">{{ $heroBadge }}</div>
      <h1 style="margin:0;font-size:clamp(34px,5vw,54px);line-height:1.08;font-weight:800;letter-spacing:-1px;color:#fff;text-wrap:balance">{{ $heroTitle }}</h1>
      <p style="margin:0;max-width:700px;font-size:17px;line-height:1.65;color:#DCE8FA;text-wrap:pretty">{{ $heroDescription }}</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;padding-top:6px">
        <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="background:#F2A900;color:#16202B;font-weight:800;padding:16px 26px;border-radius:5px;font-size:15px;text-decoration:none">Solicitar cotización</a>
        <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="border:1px solid #FFFFFF73;color:#fff;font-weight:700;padding:16px 26px;border-radius:5px;font-size:15px;text-decoration:none">Llamar {{ $phoneDisplay }}</a>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));width:100%;max-width:820px;margin-top:22px">
        @foreach ($stats as $s)
          <div style="padding:16px 12px;display:flex;flex-direction:column;gap:4px;border-left:1px solid #FFFFFF2E">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:28px;font-weight:700;color:#fff">{{ $s['value'] }}</div>
            <div style="font-size:12.5px;color:#9FC1F2">{{ $s['label'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
    <div class="mdn-cd-index" style="max-width:1160px;margin:48px auto 0;padding:0 clamp(16px,4vw,32px)">
      @foreach ($services as $s)
        <a href="#{{ $s['id'] }}" style="background:#fff;padding:20px 22px;display:flex;flex-direction:column;gap:6px;box-shadow:0 14px 34px rgba(6,44,102,.14);color:#16202B;text-decoration:none">
          <div style="font-family:'IBM Plex Mono',monospace;font-size:12px;font-weight:700;color:#003E7E">{{ $s['n'] }}</div>
          <div style="font-size:15.5px;font-weight:700;line-height:1.3">{{ $s['short'] }}</div>
          <div style="font-size:13px;color:#F2A900;font-weight:700">Ir al servicio ↓</div>
        </a>
      @endforeach
    </div>
  </section>

  <div style="height:min(10vw,72px)"></div>

  {{-- SERVICIOS EN ZIGZAG --}}
  @foreach ($services as $i => $s)
    @php $isBlue = $i % 2 === 0; @endphp
    <section id="{{ $s['id'] }}" style="padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px);background:{{ $isBlue ? '#003E7E' : '#fff' }}">
      <div class="mdn-cd-row" style="max-width:1160px;margin:0 auto">
        <div style="display:flex;flex-direction:column;gap:20px;order:{{ $isBlue ? 0 : 1 }}">
          <div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:{{ $isBlue ? '#9FC1F2' : '#003E7E' }};text-transform:uppercase">{{ $s['n'] }} · Servicio industrial · Monterrey, N.L.</div>
          <h2 style="margin:0;font-size:clamp(27px,3.4vw,38px);line-height:1.15;font-weight:800;letter-spacing:-.8px;color:{{ $isBlue ? '#fff' : '#16202B' }};text-wrap:balance">{{ $s['title'] }}</h2>
          <p style="margin:0;font-size:16px;line-height:1.7;color:{{ $isBlue ? '#DCE8FA' : '#4C5B6B' }}">{{ $s['description'] }}</p>
          <div class="mdn-cd-bullets">
            @foreach ($s['bullets'] as $b)
              <div style="display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.5;font-weight:600;color:{{ $isBlue ? '#fff' : '#16202B' }}">
                <span style="flex:none;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;background:{{ $isBlue ? '#F2A900' : '#EAF2FB' }};color:{{ $isBlue ? '#16202B' : '#003E7E' }}">✓</span>
                <span>{{ $b }}</span>
              </div>
            @endforeach
          </div>
          <div style="display:flex;flex-direction:column;gap:8px">
            <div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:{{ $isBlue ? '#9FC1F2' : '#6B7A89' }};text-transform:uppercase">{{ $s['chipsLabel'] }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px">
              @foreach ($s['chips'] as $c)
                <div style="font-size:12px;font-weight:600;padding:7px 12px;border-radius:999px;background:{{ $isBlue ? '#FFFFFF1A' : '#F7F9FC' }};color:{{ $isBlue ? '#fff' : '#003E7E' }};border:1px solid {{ $isBlue ? '#FFFFFF33' : '#DDE3EA' }}">{{ $c }}</div>
              @endforeach
            </div>
          </div>
          <div style="display:flex;gap:12px;flex-wrap:wrap;padding-top:4px">
            <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="font-weight:800;padding:15px 24px;border-radius:5px;font-size:15px;background:{{ $isBlue ? '#F2A900' : '#003E7E' }};color:{{ $isBlue ? '#16202B' : '#fff' }};text-decoration:none">Llamar ahora</a>
            <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="font-weight:700;padding:15px 24px;border-radius:5px;font-size:15px;border:1px solid {{ $isBlue ? '#FFFFFF73' : '#C9D4E5' }};color:{{ $isBlue ? '#fff' : '#003E7E' }};text-decoration:none">Escribir por WhatsApp</a>
          </div>
          <a href="{{ route($s['detailsRoute']) }}" style="font-size:14px;font-weight:700;text-decoration:underline;color:{{ $isBlue ? '#fff' : '#003E7E' }}">Ver detalles del servicio →</a>
        </div>
        <div class="mdn-cd-img-wrap" style="order:{{ $isBlue ? 1 : 0 }}">
          <img src="{{ asset($s['image']) }}" alt="{{ $s['imageAlt'] }}" style="width:100%;object-fit:cover;border-radius:10px;display:block;background:#EAF2FB">
          <div style="position:absolute;left:-16px;bottom:-22px;background:#fff;border-radius:8px;padding:14px 18px;box-shadow:0 12px 30px rgba(6,44,102,.22);display:flex;gap:12px;align-items:center">
            <div style="width:34px;height:34px;border-radius:50%;background:#EAF2FB;color:#003E7E;display:flex;align-items:center;justify-content:center;font-weight:800;flex:none">✓</div>
            <div style="display:flex;flex-direction:column">
              <div style="font-weight:800;font-size:13.5px;color:#16202B">{{ $s['badge'] }}</div>
              <div style="font-size:12px;color:#6B7A89">{{ $s['norm'] }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endforeach

  {{-- METODOLOGÍA --}}
  <section style="background:#062C66;padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px)">
    <div style="max-width:1160px;margin:0 auto;display:flex;flex-direction:column;gap:32px">
      <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:10px">
          <div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:#F2A900;text-transform:uppercase">{{ $processBadge }}</div>
          <h2 style="margin:0;font-size:clamp(26px,3.2vw,36px);font-weight:800;color:#fff;letter-spacing:-.6px">{{ $processTitle }}</h2>
        </div>
        <p style="margin:0;font-size:14.5px;color:#9FC1F2;max-width:380px;line-height:1.6">{{ $processDescription }}</p>
      </div>
      <div class="mdn-cd-steps">
        @foreach ($processSteps as $p)
          <div style="background:#0A3A80;padding:24px 20px;display:flex;flex-direction:column;gap:10px;min-height:170px">
            <div style="color:#F2A900;font-size:13.5px;font-family:'IBM Plex Mono',monospace">{{ $p['n'] }}</div>
            <div style="color:#fff;font-weight:800;font-size:15.5px">{{ $p['title'] }}</div>
            <div style="color:#BFD5F5;font-size:13px;line-height:1.55">{{ $p['description'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- POR QUÉ MAC DEL NORTE --}}
  <section style="padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px)">
    <div class="mdn-cd-why" style="max-width:1160px;margin:0 auto">
      <div style="display:flex;flex-direction:column;gap:16px">
        <div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $whyBadge }}</div>
        <h2 style="margin:0;font-size:clamp(26px,3.2vw,36px);font-weight:800;letter-spacing:-.6px;line-height:1.2;color:#16202B">{{ $whyTitle }}</h2>
        <p style="margin:0;font-size:16px;color:#4C5B6B;line-height:1.7">{{ $whyDescription }}</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;padding-top:6px">
          @foreach ($industries as $ind)
            <div style="border:1px solid #DDE3EA;border-radius:999px;padding:9px 15px;font-size:13px;font-weight:600;color:#33445E">{{ $ind }}</div>
          @endforeach
        </div>
      </div>
      <div class="mdn-cd-values">
        @foreach ($values as $v)
          <div style="background:#F7F9FC;border-radius:8px;padding:24px;display:flex;flex-direction:column;gap:8px">
            <div style="font-size:15.5px;font-weight:800;color:#16202B">{{ $v['title'] }}</div>
            <div style="font-size:13.5px;color:#4C5B6B;line-height:1.6">{{ $v['description'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section style="background:#F7F9FC;border-top:1px solid #DDE3EA;padding:0 clamp(16px,4vw,32px) clamp(56px,7vw,96px)">
    <div style="max-width:1160px;margin:0 auto;display:flex;flex-direction:column;gap:20px;padding-top:clamp(56px,7vw,96px)">
      <div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $faqBadge }}</div>
      <div class="mdn-cd-faq">
        @foreach ($faq as $item)
          <div class="mdn-cd-faq-item" onclick="this.classList.toggle('is-open')" style="border-top:1px solid #DDE3EA">
            <div style="display:flex;justify-content:space-between;gap:16px;padding:20px 0;font-weight:700;font-size:15.5px;color:#16202B">
              <div>{{ $item['q'] }}</div>
              <div class="mdn-cd-faq-sign" style="color:#003E7E;font-size:20px;line-height:1;transition:transform .2s;flex:none">+</div>
            </div>
            <div class="mdn-cd-faq-a" style="font-size:14px;color:#4C5B6B;line-height:1.65;padding-bottom:20px">{{ $item['a'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA FINAL --}}
  <section style="background:#F2A900;padding:clamp(40px,5vw,56px) clamp(16px,4vw,32px)">
    <div style="max-width:1160px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:28px;flex-wrap:wrap">
      <div style="display:flex;flex-direction:column;gap:6px">
        <div style="font-size:clamp(22px,2.8vw,30px);font-weight:800;color:#16202B">{{ $ctaTitle }}</div>
        <div style="font-size:14.5px;color:#16202B">{{ $phoneDisplay }} · 81 3582 5559 · contacto@macdelnorte.com · Lun–Vie 8:30am a 6:00pm</div>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="background:#16202B;color:#fff;font-weight:800;padding:16px 26px;border-radius:5px;text-decoration:none">Escribir por WhatsApp</a>
        <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="background:#fff;color:#16202B;font-weight:800;padding:16px 26px;border-radius:5px;text-decoration:none">Llamar ahora</a>
      </div>
    </div>
  </section>

</div>
