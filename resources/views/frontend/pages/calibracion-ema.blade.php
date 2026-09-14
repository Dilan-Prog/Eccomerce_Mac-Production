@extends('frontend.layouts.master')

@section('title')
  Calibraciones EMA de Videoregistradores y Medidores de Flujo
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio acreditado EMA · Monterrey, N.L.',
      'heroTitle' => 'Calibraciones EMA de Videoregistradores y Medidores de Flujo',
      'heroDescription' => 'Contamos con acreditación EMA para calibrar y certificar videoregistradores de variables analógicas y medidores de flujo industriales. Aseguramos la trazabilidad, confiabilidad y cumplimiento normativo de tus instrumentos de medición.',
      'heroImage' => 'uploads/servicios/calibracion-ema-1.png',
      'heroImageAlt' => 'Técnico calibrando videoregistrador industrial con acreditación EMA',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Calibración acreditada',
      'badgeCardSubtitle' => 'Con acreditación EMA',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
          'title' => 'Calibración con trazabilidad',
          'body' => 'Realizamos calibraciones acreditadas ante la EMA (Entidad Mexicana de Acreditación) para videoregistradores de variables analógicas como temperatura, presión, flujo, nivel o señales eléctricas. Emitimos certificados válidos ante auditorías ISO 9001, IATF, NOM y clientes con requerimientos internacionales.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Temperatura, presión y nivel</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Señales eléctricas 4-20 mA, mV, V</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Certificados para ISO 9001, IATF y NOM</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>',
          'title' => 'Medidores de flujo confiables y certificados',
          'body' => 'Calibramos medidores de flujo tipo electromagnético, ultrasónico, turbina o Coriolis bajo normas nacionales e internacionales, con trazabilidad al CENAM. Garantizamos exactitud en tus mediciones para procesos industriales, comerciales o de calidad, asegurando cumplimiento ante PROFECO, ISO e industria farmacéutica o alimentaria.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Electromagnético</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Ultrasónico</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Turbina</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Coriolis</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, de la recepción al certificado EMA',
      'processSubtitle' => 'Cada etapa queda documentada para respaldar la trazabilidad del instrumento ante tus auditorías.',
      'processSteps' => [
        ['title' => 'Recepción y verificación de equipo', 'body' => 'Registramos y verificamos trazabilidad de cada instrumento (videoregistrador o medidor de flujo), su estado, rango de operación y última calibración EMA.', 'deliverable' => 'Registro de ingreso del equipo'],
        ['title' => 'Ajuste y acondicionamiento', 'body' => 'Preparamos el equipo: limpieza, revisión de conexiones, acondicionamiento de sensores y verificación de fuente de alimentación, para asegurar condiciones óptimas de calibración.', 'deliverable' => 'Equipo listo para calibración'],
        ['title' => 'Calibración con patrones certificados', 'body' => 'Realizamos la calibración con patrones rastreables a CENAM/EMA: señales eléctricas (4–20 mA, mV, V), caudales de prueba y variables analógicas (T/C, RTD), siguiendo procedimientos EMA.', 'deliverable' => 'Mediciones bajo patrones trazables'],
        ['title' => 'Registro y análisis de datos', 'body' => 'Capturamos las lecturas previas y posteriores a la calibración, analizamos desviaciones e incertidumbre, y generamos reporte técnico conforme a EMA.', 'deliverable' => 'Reporte técnico de desviaciones'],
        ['title' => 'Emisión de certificado EMA', 'body' => 'Entregamos certificado oficial EMA con trazabilidad completa, valores de corrección, curva de calibración y niveles de confianza para auditorías.', 'deliverable' => 'Certificado EMA con curva de calibración'],
        ['title' => 'Devolución y soporte post-calibración', 'body' => 'Devolvemos el equipo con etiqueta de calibración, informe técnico y ofrecemos soporte para interpretación de resultados y programación de la próxima calibración.', 'deliverable' => 'Equipo etiquetado y reporte entregado'],
      ],
      'benefitsTitle' => 'Lo que obtiene tu planta',
      'benefitsSubtitle' => 'Resultados medibles en trazabilidad, cumplimiento normativo y continuidad de tus procesos de medición.',
      'benefits' => [
        ['title' => 'Certificados con validez oficial', 'body' => 'Válidos ante auditorías ISO 9001, IATF, NOM y clientes con requerimientos internacionales.'],
        ['title' => 'Videoregistradores calibrados', 'body' => 'Cubrimos variables como temperatura, presión, corriente, voltaje y señales 4-20 mA.'],
        ['title' => 'Medidores de flujo trazables', 'body' => 'Trazabilidad al CENAM y apego a normas nacionales e internacionales.'],
        ['title' => 'Equipos patrón certificados', 'body' => 'Procedimientos de calibración avalados por la EMA.'],
        ['title' => 'Reporte de incertidumbre', 'body' => 'Documentamos desviaciones, errores e incertidumbre expandida de cada medición.'],
        ['title' => 'Calibración en sitio o laboratorio', 'body' => 'Elige el esquema que mejor se adapte a la operación de tu planta.'],
        ['title' => 'Aseguramiento de calidad', 'body' => 'Evita sanciones o rechazos en tus procesos productivos por instrumentos fuera de tolerancia.'],
        ['title' => 'Programas de calibración periódica', 'body' => 'Etiquetado de equipos y seguimiento conforme a tus normativas internas.'],
      ],
      'ctaTitle' => 'Cuéntanos qué equipo necesitas calibrar y te cotizamos',
      'ctaDescription' => 'Un especialista revisa tu caso y te contacta con la propuesta técnica. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Respuesta el mismo día hábil',
        'Cotización sin compromiso',
        'Certificados con trazabilidad EMA y CENAM',
      ],
    ])
  </main>
@endsection
