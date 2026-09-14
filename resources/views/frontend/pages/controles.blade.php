@extends('frontend.layouts.master')

@section('title')
  Instalación de Controladores de Temperatura
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Instalación de Controladores de Temperatura',
      'heroDescription' => 'Instalamos, configuramos e integramos controladores de temperatura para procesos industriales: hornos, calderas, extrusión, inyección, secado y sistemas de refrigeración. Técnicos especializados, equipo calibrado y puesta en marcha documentada.',
      'heroImage' => 'uploads/servicios/instalacion_controladores-1.png',
      'heroImageAlt' => 'Técnico instalando controlador de temperatura en tablero',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Instalación certificada',
      'badgeCardSubtitle' => 'Bajo normativa NOM e ISO',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 2.6a2 2 0 0 1 3.4 0l8 13.9A2 2 0 0 1 20 19.5H4a2 2 0 0 1-1.7-3L10.3 2.6z"></path><path d="M12 9v4"></path><path d="M12 16.5h.01"></path></svg>',
          'title' => 'Aplicación industrial crítica',
          'body' => 'El control de temperatura define la calidad del producto y la seguridad del proceso. Una instalación deficiente provoca variaciones térmicas, scrap, desgaste acelerado de resistencias y paros no programados. Trabajamos sobre lazo de control completo —sensor, controlador, actuador— para que el proceso mantenga su punto de consigna bajo carga real.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Hornos, calderas y secadores</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Inyección, extrusión y termoformado</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Refrigeración y cadena de frío</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"></path></svg>',
          'title' => 'Tecnología adaptada a tu proceso',
          'body' => 'Seleccionamos el controlador según el tipo de sensor, la inercia térmica y el nivel de integración que requiere tu planta: control PID, on/off, rampa-meseta o comunicación Modbus hacia PLC. Somos distribuidores autorizados Honeywell y trabajamos también con otras marcas presentes en el piso de planta.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Honeywell</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Omron</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Autonics</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Siemens</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Novus</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Delta</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, de la evaluación a la puesta en marcha',
      'processSubtitle' => 'Cada etapa se documenta y se valida con el responsable de mantenimiento antes de avanzar a la siguiente.',
      'processSteps' => [
        ['title' => 'Evaluación técnica', 'body' => 'Visitamos la planta para revisar el proceso, el tablero existente, el tipo de sensor instalado y las condiciones eléctricas y ambientales del punto de control.', 'deliverable' => 'Levantamiento técnico en sitio'],
        ['title' => 'Selección del controlador', 'body' => 'Definimos marca y modelo según rango de temperatura, tipo de entrada, salidas de control requeridas y necesidad de comunicación: Honeywell, Omron, Autonics, Siemens, Novus o Delta.', 'deliverable' => 'Propuesta de equipo y accesorios'],
        ['title' => 'Instalación eléctrica', 'body' => 'Montaje en tablero, alambrado de alimentación y señales, blindaje de termopares o RTD, protecciones y conexión de actuadores conforme a la normativa aplicable.', 'deliverable' => 'Instalación alambrada y protegida'],
        ['title' => 'Configuración y parametrización', 'body' => 'Carga de parámetros del lazo: escalamiento del sensor, sintonización PID, alarmas, rampas y protecciones de sobrecalentamiento según el proceso.', 'deliverable' => 'Respaldo de parámetros'],
        ['title' => 'Integración con PLC, HMI o SCADA', 'body' => 'Comunicación del controlador con el sistema de supervisión de la planta para lectura de variables, registro histórico y ajuste remoto del punto de consigna.', 'deliverable' => 'Variables mapeadas y verificadas'],
        ['title' => 'Pruebas y puesta en marcha', 'body' => 'Verificación del lazo con carga real, pruebas de alarmas, validación de estabilidad térmica y capacitación breve al personal de operación y mantenimiento.', 'deliverable' => 'Reporte de puesta en marcha'],
      ],
      'benefitsTitle' => 'Lo que obtiene tu planta',
      'benefitsSubtitle' => 'Resultados medibles en estabilidad de proceso, cumplimiento normativo y continuidad operativa.',
      'benefits' => [
        ['title' => 'Cumplimiento NOM e ISO', 'body' => 'Instalación y documentación alineadas a los requisitos normativos y de auditoría de tu planta.'],
        ['title' => 'Menos paros no programados', 'body' => 'Un lazo de control bien instalado reduce fallas por sobrecalentamiento y disparos de protección.'],
        ['title' => 'Mayor vida útil del equipo', 'body' => 'Resistencias, motores y aislamientos trabajan dentro de rango, evitando desgaste acelerado.'],
        ['title' => 'Soporte post-instalación', 'body' => 'Acompañamiento técnico después de la puesta en marcha, con atención remota y en sitio.'],
        ['title' => 'Precisión térmica estable', 'body' => 'Sintonización del controlador para mantener el punto de consigna incluso con variaciones de carga.'],
        ['title' => 'Ahorro energético', 'body' => 'El control preciso evita sobreconsumo por ciclos largos de calentamiento innecesario.'],
        ['title' => 'Documentación entregable', 'body' => 'Diagramas, parámetros y reporte de pruebas para el expediente de mantenimiento.'],
        ['title' => 'Refacciones disponibles', 'body' => 'Como distribuidor Honeywell mantenemos disponibilidad de equipo y repuestos en Monterrey.'],
      ],
      'ctaTitle' => 'Cuéntanos de tu proceso y te cotizamos la instalación',
      'ctaDescription' => 'Un especialista revisa tu caso y te contacta con la propuesta técnica. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Respuesta el mismo día hábil',
        'Cotización sin compromiso',
        'Atención en Monterrey y todo el noreste',
      ],
    ])
  </main>
@endsection
