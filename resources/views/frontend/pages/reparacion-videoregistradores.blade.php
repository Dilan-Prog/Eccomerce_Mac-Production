@extends('frontend.layouts.master')

@section('title')
  Reparación de Videoregistradores
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Reparación de Videoregistradores',
      'heroDescription' => 'Especialistas en la reparación y mantenimiento de videoregistradores industriales. Diagnóstico, corrección de fallas, calibración y pruebas funcionales para garantizar el restablecimiento completo del equipo y su reintegración a la operación industrial.',
      'heroImage' => 'uploads/servicios/instalacion_reparacion-videoregistradores-1.png',
      'heroImageAlt' => 'Técnico reparando videoregistrador industrial en taller',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Reparación certificada',
      'badgeCardSubtitle' => 'Diagnóstico con garantía por escrito',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
          'title' => 'Reparación profesional de videoregistradores',
          'body' => 'Reparamos y restauramos videoregistradores industriales que presentan fallos de lectura, errores en pantalla, daños en tarjetas electrónicas, fuentes de alimentación, almacenamiento o entradas analógicas. Trabajamos con equipos de marcas reconocidas como Honeywell, Yokogawa, Eurotherm, entre otras.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Fallas de lectura y errores en pantalla</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Tarjetas electrónicas y fuentes de alimentación</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Almacenamiento y entradas analógicas</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
          'title' => 'Servicio técnico con garantía',
          'body' => 'Ejecutamos un diagnóstico técnico profundo y pruebas de laboratorio para validar la reparación antes de su entrega. Garantizamos el correcto registro de datos, integridad de entradas/salidas y comunicación con el sistema SCADA o DCS. Nuestro trabajo incluye calibración y respaldo de configuración.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Honeywell</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Yokogawa</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Eurotherm</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, del diagnóstico a la entrega con garantía',
      'processSubtitle' => 'Cada etapa se documenta y se valida con el responsable de mantenimiento antes de avanzar a la siguiente.',
      'processSteps' => [
        ['title' => 'Evaluación técnica inicial', 'body' => 'Se realiza un diagnóstico completo del videoregistrador: revisión de circuitos, entradas analógicas, salidas, fuente de alimentación y módulos de almacenamiento. Identificamos errores de lectura, fallas de comunicación, o daños eléctricos.', 'deliverable' => 'Diagnóstico técnico en sitio o taller'],
        ['title' => 'Informe técnico y presupuesto', 'body' => 'Entregamos un informe detallado con las fallas detectadas, piezas a reemplazar o reparar, tiempos estimados y costos. Solo procedemos con autorización del cliente.', 'deliverable' => 'Cotización detallada y autorización del cliente'],
        ['title' => 'Reparación de componentes', 'body' => 'Reemplazamos o reparamos fuentes, tarjetas electrónicas, convertidores A/D, módulos de entradas/salidas, pantallas y otros componentes críticos, utilizando refacciones originales o equivalentes certificados.', 'deliverable' => 'Componentes reparados o sustituidos'],
        ['title' => 'Calibración y configuración', 'body' => 'Configuramos nuevamente los rangos de medición, tipos de señales (RTD, T/C, mA), y calibramos el equipo según sus especificaciones originales o requerimientos del cliente.', 'deliverable' => 'Parámetros y señales calibrados'],
        ['title' => 'Pruebas funcionales y validación', 'body' => 'Realizamos pruebas de entradas analógicas, alarmas, almacenamiento de datos y comunicación (Ethernet, USB, RS-485, etc.), verificando el correcto funcionamiento del sistema antes de su entrega.', 'deliverable' => 'Comunicación y almacenamiento validados'],
        ['title' => 'Entrega y garantía', 'body' => 'Entregamos el videoregistrador reparado, con informe de servicio, parámetros restaurados y garantía por el trabajo realizado. Opcionalmente ofrecemos mantenimiento preventivo o capacitación técnica.', 'deliverable' => 'Equipo entregado con garantía por escrito'],
      ],
      'benefitsTitle' => 'Beneficios de reparar tu videoregistrador con nosotros',
      'benefitsSubtitle' => 'Resultados medibles en ahorro, continuidad operativa y respaldo técnico.',
      'benefits' => [
        ['title' => 'Reparación integral', 'body' => 'Fuentes, tarjetas electrónicas, entradas analógicas y pantallas reparadas por completo.'],
        ['title' => 'Ahorro frente a equipo nuevo', 'body' => 'Evita el gasto de reemplazar el videoregistrador completo.'],
        ['title' => 'Calibración de señales', 'body' => 'Restauramos configuraciones originales y calibramos señales en mA, V, RTD y T/C.'],
        ['title' => 'Garantía por escrito', 'body' => 'Soporte técnico post-servicio incluido en cada reparación.'],
        ['title' => 'Compatibilidad SCADA', 'body' => 'Integración validada con sistemas SCADA, Historian y redes industriales existentes.'],
        ['title' => 'Menos tiempo de paro', 'body' => 'Reducimos el tiempo de inactividad y mantenemos la continuidad operativa.'],
        ['title' => 'Experiencia multimarca', 'body' => 'Trabajamos con Honeywell eZtrend, Multitrend SX, Eurotherm, Yokogawa, entre otros.'],
        ['title' => 'Reporte técnico detallado', 'body' => 'Entregamos recomendaciones preventivas junto con el informe de servicio.'],
      ],
      'ctaTitle' => 'Cuéntanos la falla y te cotizamos la reparación',
      'ctaDescription' => 'Un especialista revisa tu caso y te contacta con el diagnóstico y la propuesta técnica. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Diagnóstico técnico sin compromiso',
        'Cotización antes de reparar',
        'Atención en Monterrey y todo el noreste',
      ],
    ])
  </main>
@endsection
