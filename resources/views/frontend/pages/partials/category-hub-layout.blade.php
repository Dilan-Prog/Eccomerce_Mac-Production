{{--
    Layout compartido para las 3 páginas "hub" de categoría de Servicios
    (Instalación y Configuración / Reparación de Equipos / Calibraciones).
    Cada página @include('frontend.pages.partials.category-hub-layout', [...])
    pasando su propio contenido — este partial solo pone la estructura/estilos.
    Basado en mockup de Claude Design, adaptado a la paleta/tipografía ya
    usada en el resto del sitio (#003E7E / #F2A900 / IBM Plex Mono).

    Variables esperadas:
      heroBadge, heroTitle, heroDescription (string)
      services (array) items: ['n'=>'01','title'=>..,'description'=>..,
        'image'=>'uploads/servicios/..','imageAlt'=>..,'bullets'=>[..],
        'norm'=>..,'detailsRoute'=>..]
      valueProps     (array) 4 items: ['title'=>..,'description'=>..]
      catalogBadge, catalogTitle (string)
      processBadge, processTitle, processDescription (string)
      processSteps   (array) items: ['n'=>'01','title'=>..,'description'=>..]
      industriesBadge, industriesTitle (string)
      industries     (array) de strings
      brandsBadge, brandsTitle, brandsDescription (string)
      brandTags      (array) de strings
      faqBadge (string)
      faq            (array) items: ['q'=>..,'a'=>..]
--}}
@php
    $phone = '8124738768';
    $phoneDisplay = '81 2473 8768';
    $whatsapp = 'https://wa.link/f28njw';
@endphp

@push('styles')
<style>
    .mdn-hub-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:clamp(32px,5vw,56px); align-items:center; }
    .mdn-hub-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr)); gap:20px; }
    .mdn-hub-steps { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:2px; }
    .mdn-hub-industries { display:flex; flex-wrap:wrap; gap:8px; }
    .mdn-hub-faq-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,440px),1fr)); gap:0 48px; }
    .mdn-hub-faq-item { cursor:pointer; }
    .mdn-hub-faq-item .mdn-hub-faq-a { display:none; }
    .mdn-hub-faq-item.is-open .mdn-hub-faq-a { display:block; }
    .mdn-hub-faq-item.is-open .mdn-hub-faq-sign { transform:rotate(45deg); }
</style>
@endpush

<div style="width:100%;overflow-x:hidden">

  {{-- HERO --}}
  <section style="position:relative;background:#003E7E;color:#fff;overflow:hidden">
    <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF0D 1px,transparent 1px),linear-gradient(90deg,#FFFFFF0D 1px,transparent 1px);background-size:56px 56px;pointer-events:none"></div>
    <div class="mdn-hub-row" style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px)">
      <div style="min-width:0;display:flex;flex-direction:column;gap:20px">
        <div style="font-size:12px;font-weight:700;letter-spacing:.14em;color:#9FC1F2;text-transform:uppercase">{{ $heroBadge }}</div>
        <h1 style="margin:0;font-size:clamp(32px,5vw,54px);line-height:1.08;font-weight:800;letter-spacing:-1.2px;text-wrap:balance">{{ $heroTitle }}</h1>
        <p style="margin:0;font-size:clamp(16px,1.6vw,18px);line-height:1.6;color:#CBDCF0;max-width:54ch;text-wrap:pretty">{{ $heroDescription }}</p>
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:6px">
          <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="display:flex;align-items:center;gap:10px;padding:17px 26px;border-radius:5px;background:#F2A900;color:#16202B;font-size:16px;font-weight:800;white-space:nowrap;text-decoration:none">Llamar ahora</a>
          <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="display:flex;align-items:center;gap:10px;padding:17px 26px;border-radius:5px;border:1px solid #FFFFFF4D;color:#fff;font-size:16px;font-weight:700;white-space:nowrap;text-decoration:none">Escribir por WhatsApp</a>
        </div>
      </div>
      <div style="min-width:0;background:#002E5C99;border:1px solid #FFFFFF1F;border-radius:8px;padding:28px">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#F2A900;padding-bottom:12px;text-transform:uppercase">Índice de servicios</div>
        @foreach ($services as $s)
          <a href="{{ route($s['detailsRoute']) }}" style="display:grid;grid-template-columns:36px minmax(0,1fr) auto;gap:12px;align-items:center;padding:14px 0;border-top:1px solid #FFFFFF1F;text-decoration:none">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:13px;color:#9FC1F2">{{ $s['n'] }}</div>
            <div style="color:#fff;font-weight:700;font-size:15px">{{ $s['title'] }}</div>
            <div style="color:#F2A900;font-weight:800">→</div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- VALUE PROPS --}}
  <section style="border-bottom:1px solid #DDE3EA">
    <div style="max-width:1200px;margin:0 auto;padding:0 clamp(16px,4vw,32px);display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
      @foreach ($valueProps as $v)
        <div style="padding:28px 22px;border-left:1px solid #DDE3EA;display:flex;flex-direction:column;gap:6px">
          <div style="font-size:15.5px;font-weight:800;color:#16202B">{{ $v['title'] }}</div>
          <div style="font-size:13.5px;line-height:1.55;color:#4C5B6B">{{ $v['description'] }}</div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- CATÁLOGO --}}
  <section style="background:#F7F9FC">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px);display:flex;flex-direction:column;gap:32px">
      <div style="display:flex;flex-direction:column;gap:12px">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $catalogBadge }}</div>
        <h2 style="margin:0;font-size:clamp(26px,3.4vw,38px);font-weight:800;letter-spacing:-.8px;color:#16202B">{{ $catalogTitle }}</h2>
      </div>
      <div class="mdn-hub-cards">
        @foreach ($services as $s)
          <div style="background:#fff;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;border:1px solid #DDE3EA">
            <div style="position:relative;aspect-ratio:16/10">
              <img src="{{ asset($s['image']) }}" alt="{{ $s['imageAlt'] }}" style="width:100%;height:100%;object-fit:cover;display:block">
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:12px;flex:1">
              <div style="font-size:18px;font-weight:800;line-height:1.25;color:#16202B">{{ $s['title'] }}</div>
              <div style="font-size:14px;color:#4C5B6B;line-height:1.6">{{ $s['description'] }}</div>
              <div style="display:flex;flex-direction:column;gap:6px;padding-top:10px;border-top:1px solid #DDE3EA;flex:1">
                @foreach ($s['bullets'] as $b)
                  <div style="font-size:13px;color:#33445E;display:flex;gap:8px"><span style="color:#003E7E;font-weight:800">✓</span>{{ $b }}</div>
                @endforeach
              </div>
              <div style="display:flex;justify-content:space-between;align-items:center;padding-top:6px">
                <a href="{{ route($s['detailsRoute']) }}" style="font-weight:800;color:#003E7E;font-size:13.5px;text-decoration:underline">Ver servicio →</a>
                <div style="font-size:12px;color:#6B7A89">{{ $s['norm'] }}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- METODOLOGÍA --}}
  <section style="position:relative;background:#003E7E;overflow:hidden">
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(56px,7vw,88px) clamp(16px,4vw,32px);display:flex;flex-direction:column;gap:32px">
      <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:12px;max-width:520px">
          <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#F2A900;text-transform:uppercase">{{ $processBadge }}</div>
          <h2 style="margin:0;font-size:clamp(26px,3.2vw,36px);font-weight:800;letter-spacing:-.8px;color:#fff">{{ $processTitle }}</h2>
        </div>
        <div style="font-size:14.5px;color:#9FC1F2;max-width:360px;line-height:1.55">{{ $processDescription }}</div>
      </div>
      <div class="mdn-hub-steps">
        @foreach ($processSteps as $p)
          <div style="background:#0A5AAF26;padding:24px 20px;display:flex;flex-direction:column;gap:10px;min-height:170px">
            <div style="font-family:'IBM Plex Mono',monospace;color:#F2A900;font-size:14px">{{ $p['n'] }}</div>
            <div style="color:#fff;font-weight:800;font-size:15.5px">{{ $p['title'] }}</div>
            <div style="color:#C7DAEE;font-size:13px;line-height:1.55">{{ $p['description'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- INDUSTRIAS + MARCAS --}}
  <section style="background:#fff">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px);display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,400px),1fr));gap:clamp(32px,5vw,56px)">
      <div style="display:flex;flex-direction:column;gap:16px">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $industriesBadge }}</div>
        <h2 style="margin:0;font-size:clamp(24px,3vw,32px);font-weight:800;letter-spacing:-.6px;line-height:1.2;color:#16202B">{{ $industriesTitle }}</h2>
        <div class="mdn-hub-industries" style="padding-top:6px">
          @foreach ($industries as $ind)
            <div style="border:1px solid #DDE3EA;border-radius:999px;padding:9px 15px;font-size:13.5px;font-weight:600;color:#33445E">{{ $ind }}</div>
          @endforeach
        </div>
      </div>
      <div style="background:#F7F9FC;border-radius:8px;padding:32px;display:flex;flex-direction:column;gap:16px">
        <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $brandsBadge }}</div>
        <div style="font-size:21px;font-weight:800;line-height:1.25;color:#16202B">{{ $brandsTitle }}</div>
        <div style="font-size:14.5px;color:#4C5B6B;line-height:1.6">{{ $brandsDescription }}</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;padding-top:6px">
          @foreach ($brandTags as $tag)
            <div style="background:#fff;border:1px solid #DDE3EA;border-radius:6px;padding:13px 15px;font-size:13px;font-weight:700;color:#003E7E">{{ $tag }}</div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section style="background:#F7F9FC;border-top:1px solid #DDE3EA">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(56px,7vw,96px) clamp(16px,4vw,32px);display:flex;flex-direction:column;gap:20px">
      <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;color:#003E7E;text-transform:uppercase">{{ $faqBadge }}</div>
      <div class="mdn-hub-faq-grid">
        @foreach ($faq as $i => $item)
          <div class="mdn-hub-faq-item" onclick="this.classList.toggle('is-open')" style="border-top:1px solid #DDE3EA">
            <div style="display:flex;justify-content:space-between;gap:16px;padding:20px 0;font-weight:800;font-size:15.5px;color:#16202B">
              <div>{{ $item['q'] }}</div>
              <div class="mdn-hub-faq-sign" style="color:#003E7E;font-size:20px;line-height:1;transition:transform .2s;flex:none">+</div>
            </div>
            <div class="mdn-hub-faq-a" style="font-size:14px;color:#4C5B6B;line-height:1.6;padding-bottom:20px">{{ $item['a'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA FINAL --}}
  <section style="background:#F2A900">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(40px,5vw,56px) clamp(16px,4vw,32px);display:flex;justify-content:space-between;align-items:center;gap:28px;flex-wrap:wrap">
      <div style="display:flex;flex-direction:column;gap:6px">
        <div style="font-size:clamp(22px,2.8vw,28px);font-weight:800;color:#16202B;letter-spacing:-.5px">¿No encuentras tu servicio? Cuéntanos tu proceso</div>
        <div style="font-size:14.5px;color:#16202B">{{ $phoneDisplay }} · 81 3582 5559 · contacto@macdelnorte.com · Lun–Vie 8:30am – 6:00pm</div>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="background:#16202B;color:#fff;font-weight:800;padding:17px 26px;border-radius:5px;text-decoration:none">Llamar ahora</a>
        <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="background:#fff;color:#16202B;font-weight:800;padding:17px 26px;border-radius:5px;text-decoration:none">WhatsApp</a>
      </div>
    </div>
  </section>

</div>
