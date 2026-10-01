@extends('frontend.layouts.master')

@section('title')
  Instalación y Configuración
@endsection
@section('content')
  @push('styles')
  <style>
    .mdn-ic-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: clamp(32px, 5vw, 64px);
      align-items: center;
    }
    @media (min-width: 900px) {
      .mdn-ic-row { grid-template-columns: 1fr 1.35fr; }
    }
    @keyframes mdnIcPulse { 0%,100% { opacity: .35; transform: scale(1); } 50% { opacity: 1; transform: scale(1.35); } }
  </style>
  @endpush
  <main>
    @php
      $phone = '8124738768';
      $phoneDisplay = '81 2473 8768';
      $whatsapp = 'https://wa.link/f28njw';

      $items = [
        [
          'title' => 'Instalación y Configuración de Controladores de Temperatura',
          'description' => 'Instalamos, configuramos e integramos controladores de temperatura para procesos industriales: hornos, calderas, extrusión, inyección, secado y sistemas de refrigeración. Técnicos especializados, equipo calibrado y puesta en marcha documentada.',
          'image' => 'uploads/servicios/instalacion_controladores-1.png',
          'imageAlt' => 'Técnico instalando controlador de temperatura en tablero',
          'detailsRoute' => 'servicio-controladores-temperatura',
          'bg' => 'blue',
          'badgeTitle' => 'Instalación certificada',
          'badgeSubtitle' => 'Bajo normativa NOM e ISO',
        ],
        [
          'title' => 'Instalación de Videoregistradores',
          'description' => 'Especialistas en sistemas de grabadores de variables analógicas. Ofrecemos instalación profesional de grabadores de datos para garantizar la confiabilidad y precisión de tus procesos industriales.',
          'image' => 'uploads/servicios/instalacion_videoregistradores-1.png',
          'imageAlt' => 'Técnico instalando videoregistrador de variables analógicas en tablero industrial',
          'detailsRoute' => 'servicio-instalacion-videoregistradores',
          'bg' => 'white',
          'badgeTitle' => 'Instalación certificada',
          'badgeSubtitle' => 'Bajo normativa NOM e ISO',
        ],
        [
          'title' => 'Instalación, Configuración y Puesta en Marcha de Medidores de Flujo',
          'description' => 'Especialistas en sistemas de medición de caudal. Ofrecemos instalación profesional de medidores de flujo industriales para garantizar precisión, eficiencia y trazabilidad en tus procesos productivos.',
          'image' => 'frontend/images/servicios/medidores-flujo-transmisor-presion.jpg',
          'imageAlt' => 'Transmisor de presión Honeywell instalado en línea de proceso industrial',
          'detailsRoute' => 'servicio-instalacion-medidoresdeflujo',
          'bg' => 'blue',
          'badgeTitle' => 'Instalación certificada',
          'badgeSubtitle' => 'Bajo estándares NOM, ISO e ISA',
        ],
        [
          'title' => 'Instalación, Configuración y Proyecto Llave en Mano de PLC',
          'description' => 'Especialistas en sistemas de automatización con PLC. Ofrecemos servicio integral —desde el diseño del proyecto hasta la puesta en marcha— para garantizar un control fiable y eficiente de tus procesos industriales.',
          'image' => 'uploads/servicios/instalacion_plc-1.png',
          'imageAlt' => 'Técnico instalando y configurando un PLC en tablero de control industrial',
          'detailsRoute' => 'servicio-instalacion-plc',
          'bg' => 'white',
          'badgeTitle' => 'Proyecto llave en mano',
          'badgeSubtitle' => 'Bajo normativa IEC, UL y NOM',
        ],
      ];
    @endphp

    <div style="width:100%;overflow-x:hidden">

      <section style="position:relative;background:#003E7E;color:#fff;overflow:hidden">
        <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF10 1px,transparent 1px),linear-gradient(90deg,#FFFFFF10 1px,transparent 1px);background-size:56px 56px;pointer-events:none"></div>
        <div style="position:relative;max-width:900px;margin:0 auto;padding:clamp(48px,7vw,88px) clamp(16px,4vw,32px) clamp(40px,6vw,64px);text-align:center">
          <div style="display:inline-flex;align-items:center;gap:9px;padding:7px 14px;border:1px solid #FFFFFF33;border-radius:100px;font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#BFD6F0;margin-bottom:24px">
            <span style="width:6px;height:6px;border-radius:50%;background:#F2A900;animation:mdnIcPulse 2.4s ease-in-out infinite"></span>
            Servicio industrial · Monterrey, N.L.
          </div>
          <h1 style="margin:0 0 18px;font-size:clamp(32px,5vw,50px);line-height:1.08;font-weight:800;letter-spacing:-1.2px;text-wrap:balance">Instalación y Configuración</h1>
          <p style="margin:0 auto 36px;font-size:clamp(16px,1.6vw,18px);line-height:1.65;color:#CBDCF0;max-width:56ch;text-wrap:pretty">Instalamos y configuramos controladores de temperatura, videoregistradores, medidores de flujo y sistemas PLC para tu planta industrial, con técnicos especializados y puesta en marcha documentada.</p>
          <div style="display:flex;justify-content:center;gap:clamp(24px,5vw,56px);flex-wrap:wrap">
            <div style="min-width:0">
              <div style="font-family:'IBM Plex Mono',monospace;font-size:clamp(22px,2.4vw,28px);font-weight:600;color:#fff">+30</div>
              <div style="font-size:12.5px;font-weight:600;color:#9FBEDE;letter-spacing:.04em">años en el sector industrial</div>
            </div>
            <div style="width:1px;background:#FFFFFF26"></div>
            <div style="min-width:0">
              <div style="font-family:'IBM Plex Mono',monospace;font-size:clamp(22px,2.4vw,28px);font-weight:600;color:#fff">24 h</div>
              <div style="font-size:12.5px;font-weight:600;color:#9FBEDE;letter-spacing:.04em">respuesta en área metropolitana</div>
            </div>
            <div style="width:1px;background:#FFFFFF26"></div>
            <div style="min-width:0">
              <div style="font-family:'IBM Plex Mono',monospace;font-size:clamp(22px,2.4vw,28px);font-weight:600;color:#fff">4</div>
              <div style="font-size:12.5px;font-weight:600;color:#9FBEDE;letter-spacing:.04em">servicios especializados</div>
            </div>
          </div>
        </div>
      </section>

      @foreach ($items as $i => $item)
        @php
          $isBlue = $item['bg'] === 'blue';
          $imageFirst = !$isBlue;
        @endphp
        <section style="position:relative;overflow:hidden;background:{{ $isBlue ? '#003E7E' : '#ffffff' }};{{ $isBlue ? 'color:#fff;border-top:1px solid #FFFFFF1A' : 'border-top:1px solid #DDE3EA' }}">
          @if ($isBlue)
            <div style="position:absolute;inset:0;background-image:linear-gradient(#FFFFFF0D 1px,transparent 1px),linear-gradient(90deg,#FFFFFF0D 1px,transparent 1px);background-size:56px 56px;pointer-events:none"></div>
          @endif
          <div class="mdn-ic-row" style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,4vw,32px)">

            <div style="min-width:0;{{ $imageFirst ? 'order:2' : 'order:1' }}">
              <div style="font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:{{ $isBlue ? '#8FB6DE' : '#003E7E' }};margin-bottom:14px">Servicio industrial · Monterrey, N.L.</div>
              <h2 style="margin:0 0 16px;font-size:clamp(26px,3.2vw,38px);line-height:1.15;font-weight:800;letter-spacing:-1px;color:{{ $isBlue ? '#fff' : '#16202B' }};text-wrap:balance">{{ $item['title'] }}</h2>
              <p style="margin:0 0 28px;font-size:16px;line-height:1.65;color:{{ $isBlue ? '#CBDCF0' : '#4C5B6B' }};max-width:52ch;text-wrap:pretty">{{ $item['description'] }}</p>

              <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px">
                <a href="tel:{{ $phone }}" class="track-conversion" data-type="telefono_servicios" style="display:flex;align-items:center;gap:10px;padding:15px 22px;border-radius:5px;background:{{ $isBlue ? '#F2A900' : '#003E7E' }};color:{{ $isBlue ? '#16202B' : '#fff' }};font-size:15px;font-weight:800;white-space:nowrap;text-decoration:none">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"></path></svg>
                  Llamar ahora
                </a>
                <a href="{{ $whatsapp }}" target="_blank" class="track-conversion" data-type="whatsapp_servicios" style="display:flex;align-items:center;gap:10px;padding:15px 22px;border-radius:5px;border:1px solid {{ $isBlue ? '#FFFFFF4D' : '#DDE3EA' }};color:{{ $isBlue ? '#fff' : '#003E7E' }};font-size:15px;font-weight:700;white-space:nowrap;text-decoration:none">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.1-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.4-.2-2.8.7.7-2.7-.2-.4A8 8 0 0 1 12 4zm-3.2 4c-.3 0-.7.1-1 .5-.3.4-.8 1-.8 1.9 0 1 .7 2 1 2.4.3.4 1.8 2.9 4.5 3.9 2.2.8 2.7.7 3.2.6.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.1-.7.1l-.8 1c-.1.2-.3.2-.6.1a6.6 6.6 0 0 1-3.3-2.9c-.1-.3 0-.4.1-.6l.6-.7c.2-.2.2-.4.1-.6l-.8-2c-.1-.3-.3-.3-.5-.3z"></path></svg>
                  Escribir por WhatsApp
                </a>
              </div>

              <a href="{{ route($item['detailsRoute']) }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:700;color:{{ $isBlue ? '#BFD6F0' : '#6B7A89' }};text-decoration:underline;text-underline-offset:3px">
                Ver detalles
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
              </a>
            </div>

            <div style="min-width:0;position:relative;{{ $imageFirst ? 'order:1' : 'order:2' }}">
              <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid {{ $isBlue ? '#FFFFFF2E' : '#DDE3EA' }};box-shadow:0 30px 70px -30px #00152C33;aspect-ratio:4/3;min-height:0">
                <img src="{{ asset($item['image']) }}" alt="{{ $item['imageAlt'] }}" style="width:100%;height:100%;object-fit:cover;display:block">
              </div>
              <div style="position:absolute;bottom:-18px;left:-18px;background:#fff;border:1px solid #DDE3EA;border-radius:6px;padding:14px 18px;box-shadow:0 18px 40px -18px #00152C99;display:flex;align-items:center;gap:12px">
                <div style="width:34px;height:34px;border-radius:50%;background:#EAF2FB;display:grid;place-items:center;flex:none">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 3.8 8.6 9 9 5.2-.4 9-4 9-9V7l-9-5z"></path><path d="m9 12 2 2 4-4"></path></svg>
                </div>
                <div style="line-height:1.25">
                  <div style="font-size:13.5px;font-weight:800;color:#16202B">{{ $item['badgeTitle'] }}</div>
                  <div style="font-size:12px;font-weight:600;color:#6B7A89">{{ $item['badgeSubtitle'] }}</div>
                </div>
              </div>
            </div>

          </div>
        </section>
      @endforeach
    </div>
  </main>
@endsection
