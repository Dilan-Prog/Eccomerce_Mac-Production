@extends('frontend.layouts.master')

@section('title')
  PLC — Configuración, Instalación y Llave en Mano
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Instalación, Configuración y Proyecto Llave en Mano de PLC',
      'heroDescription' => 'Especialistas en sistemas de automatización con PLC. Ofrecemos servicio integral —desde el diseño del proyecto hasta la puesta en marcha— para garantizar un control fiable y eficiente de tus procesos industriales.',
      'heroImage' => 'uploads/servicios/instalacion_plc-1.png',
      'heroImageAlt' => 'Técnico instalando y configurando un PLC en tablero de control industrial',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Proyecto llave en mano',
      'badgeCardSubtitle' => 'Bajo normativa IEC, UL y NOM',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 2.6a2 2 0 0 1 3.4 0l8 13.9A2 2 0 0 1 20 19.5H4a2 2 0 0 1-1.7-3L10.3 2.6z"></path><path d="M12 9v4"></path><path d="M12 16.5h.01"></path></svg>',
          'title' => 'Soluciones de PLC llave en mano',
          'body' => 'En entornos industriales, un PLC mal dimensionado o configurado puede causar paros, defectos o ineficiencias. Diseñamos y entregamos proyectos llave en mano de PLC, evaluando protocolos, E/S digitales y analógicas, comunicaciones y redundancia para asegurar un sistema robusto y escalable.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Comunicaciones Modbus, Profinet y EtherNet/IP</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>E/S digitales y analógicas dimensionadas al proceso</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Redundancia y escalabilidad del sistema</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"></path></svg>',
          'title' => 'Adaptación a tu infraestructura de control',
          'body' => 'Instalamos y configuramos PLC de las principales marcas, desarrollando lógicas de control, pantallas HMI y comunicación con SCADA. Nos aseguramos de que tu sistema funcione sin conflictos y cumpla con las normas IEC, UL y NOM.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Honeywell</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Siemens</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Allen-Bradley</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Schneider</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Mitsubishi</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, del diseño a la puesta en marcha',
      'processSubtitle' => 'Cada etapa se documenta y se valida contigo antes de avanzar a la siguiente.',
      'processSteps' => [
        ['title' => 'Ingeniería y diseño del sistema', 'body' => 'Definimos especificaciones del PLC, E/S, HMI y comunicaciones según requisitos de proceso, normativas y entorno operativo.', 'deliverable' => 'Ingeniería de detalle documentada'],
        ['title' => 'Suministro de equipos y materiales', 'body' => 'Proveemos PLC, módulos de E/S, fuentes, cables, gabinetes y HMI de acuerdo al diseño aprobado.', 'deliverable' => 'Lista de equipo y materiales'],
        ['title' => 'Instalación eléctrica y montaje', 'body' => 'Montamos gabinetes, tendido de cables y conexionado de PLC, E/S y HMI, cumpliendo normas de seguridad eléctrica.', 'deliverable' => 'Tablero instalado y cableado'],
        ['title' => 'Programación y configuración', 'body' => 'Desarrollamos la lógica ladder o estructurada, configuramos HMI y establecemos comunicaciones con SCADA o ERP.', 'deliverable' => 'Programa y respaldo de configuración'],
        ['title' => 'Integración y pruebas en planta', 'body' => 'Ejecutamos pruebas de I/O, simulaciones de proceso y validación de secuencias antes de la puesta en marcha.', 'deliverable' => 'Reporte de pruebas de I/O'],
        ['title' => 'Puesta en marcha y capacitación', 'body' => 'Activamos el sistema en condiciones reales, entrenamos a tu equipo de operación y entregamos documentación técnica completa.', 'deliverable' => 'Reporte de puesta en marcha y manuales'],
      ],
      'benefitsTitle' => 'Beneficios de tu proyecto llave en mano',
      'benefitsSubtitle' => 'Resultados medibles en continuidad operativa, cumplimiento normativo y control de tu proceso.',
      'benefits' => [
        ['title' => 'Proyecto llave en mano', 'body' => 'Diseño, suministro, instalación y puesta en marcha en un solo servicio integral.'],
        ['title' => 'Cumplimiento normativo', 'body' => 'Instalación alineada a los estándares internacionales IEC, UL, NOM e ISA.'],
        ['title' => 'Menos tiempos de paro', 'body' => 'Optimizamos los ciclos de producción y reducimos paros no programados.'],
        ['title' => 'Lógicas y HMI a medida', 'body' => 'Desarrollo de lógicas de control a medida y pantallas HMI intuitivas para tu operación.'],
        ['title' => 'Integración con tus sistemas', 'body' => 'Comunicación con SCADA, ERP y redes industriales existentes en tu planta.'],
        ['title' => 'Soporte y capacitación', 'body' => 'Acompañamiento técnico y capacitación para tu equipo de mantenimiento.'],
        ['title' => 'Documentación completa', 'body' => 'Manuales eléctricos, diagramas ladder y esquemas de cableado para tu expediente.'],
        ['title' => 'Escalabilidad', 'body' => 'Sistemas preparados para futuras ampliaciones o migraciones sin rediseñar desde cero.'],
      ],
      'ctaTitle' => 'Cuéntanos de tu proyecto y te cotizamos la solución',
      'ctaDescription' => 'Un especialista revisa tu caso y te contacta con la propuesta técnica. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Respuesta el mismo día hábil',
        'Cotización sin compromiso',
        'Atención en Monterrey y todo el noreste',
      ],
    ])
  </main>
@endsection
