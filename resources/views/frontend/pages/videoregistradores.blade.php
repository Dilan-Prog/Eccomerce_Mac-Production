@extends('frontend.layouts.master')

@section('title')
  Instalación de Videoregistradores
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Instalación de Videoregistradores',
      'heroDescription' => 'Especialistas en sistemas de grabadores de variables analógicas. Ofrecemos instalación profesional de grabadores de datos para garantizar la confiabilidad y precisión de tus procesos industriales.',
      'heroImage' => 'uploads/servicios/instalacion_videoregistradores-1.png',
      'heroImageAlt' => 'Técnico instalando videoregistrador de variables analógicas en tablero industrial',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Instalación certificada',
      'badgeCardSubtitle' => 'Bajo normativa NOM e ISO',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',
          'title' => 'Grabación de variables analógicas confiable',
          'body' => 'En el sector industrial, un grabador de variables mal instalado puede comprometer la precisión y fiabilidad del monitoreo de datos críticos. Evaluamos cada aspecto de tu entorno —tipo de sensores, almacenamiento de datos, conectividad con sistemas de gestión y redundancia de datos— para asegurar un sistema de grabadores de variables analógicas estable, seguro y eficiente, con respaldo continuo.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Temperatura, presión y variables de proceso</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Registro continuo con respaldo de datos</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Redundancia y trazabilidad de la información</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>',
          'title' => 'Integración y adaptación a tu infraestructura',
          'body' => 'Instalamos grabadores de variables analógicas de última generación, desde modelos básicos hasta sistemas avanzados con capacidades de grabación continua, acceso remoto y configuración personalizada. Trabajamos con marcas líderes en el sector como Honeywell, Omron, Siemens y Yokogawa para garantizar que tu solución se adapte perfectamente a las necesidades de tu proceso industrial.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Honeywell</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Omron</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Siemens</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Yokogawa</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, de la evaluación a la puesta en marcha',
      'processSubtitle' => 'Cada etapa se documenta y se valida con el responsable de mantenimiento antes de avanzar a la siguiente.',
      'processSteps' => [
        ['title' => 'Evaluación técnica del entorno', 'body' => 'Analizamos la infraestructura de sensores, el tipo de variables a medir (temperatura, presión, etc.) y las necesidades específicas del entorno para determinar la mejor configuración del grabador.', 'deliverable' => 'Levantamiento técnico en sitio'],
        ['title' => 'Selección del grabador adecuado', 'body' => 'Recomendamos el modelo de grabador según el número de entradas analógicas, la compatibilidad con los sensores (T/C, RTD, lineales) y la capacidad de almacenamiento necesaria para tu proceso.', 'deliverable' => 'Propuesta de equipo y accesorios'],
        ['title' => 'Instalación eléctrica y física', 'body' => 'Conectamos los sensores y transmisores a los grabadores de forma segura, asegurando las conexiones y la integración con otros sistemas de monitoreo si es necesario.', 'deliverable' => 'Instalación alambrada y protegida'],
        ['title' => 'Configuración y parametrización', 'body' => 'Ajustamos los parámetros de grabación, rangos de medición, y configuramos alarmas o notificaciones según las necesidades específicas del cliente.', 'deliverable' => 'Respaldo de parámetros'],
        ['title' => 'Integración con sistemas existentes', 'body' => 'Integramos el grabador con sistemas de gestión de datos o almacenamiento en la nube para que los datos estén siempre disponibles y sean fáciles de analizar.', 'deliverable' => 'Variables mapeadas y verificadas'],
        ['title' => 'Pruebas y puesta en marcha', 'body' => 'Verificamos la precisión de las mediciones, el funcionamiento de las alarmas y la capacidad de almacenamiento antes de entregar el sistema, asegurando que cumpla con los requisitos operativos.', 'deliverable' => 'Reporte de puesta en marcha'],
      ],
      'benefitsTitle' => 'Lo que obtiene tu planta',
      'benefitsSubtitle' => 'Resultados medibles en confiabilidad de datos, cumplimiento normativo y continuidad operativa.',
      'benefits' => [
        ['title' => 'Cumplimiento NOM e ISO', 'body' => 'Instalación garantizada bajo estándares normativos y de auditoría de tu planta.'],
        ['title' => 'Menos fallos de registro', 'body' => 'Reducción de fallos en la captura y almacenamiento de datos de variables analógicas.'],
        ['title' => 'Mayor precisión de datos', 'body' => 'Aumento de la precisión y fiabilidad de los registros de variables analógicas.'],
        ['title' => 'Asesoría técnica integral', 'body' => 'Acompañamiento desde la evaluación del entorno hasta la calibración y configuración del sistema.'],
        ['title' => 'Adaptación a tu planta', 'body' => 'Instalación adaptada a entornos industriales exigentes y con espacio limitado.'],
        ['title' => 'Soporte post-instalación', 'body' => 'Atención ante dudas o ajustes de configuración después de la puesta en marcha.'],
        ['title' => 'Instalación rápida', 'body' => 'Tiempos de instalación cortos que no interrumpen las operaciones industriales.'],
        ['title' => 'Compatibilidad con SCADA', 'body' => 'Equipos compatibles con sistemas de automatización industrial y SCADA.'],
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
