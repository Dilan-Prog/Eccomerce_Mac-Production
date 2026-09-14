@extends('frontend.layouts.master')

@section('title')
  Instalación de Medidores de Flujo
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Instalación, Configuración y Puesta en Marcha de Medidores de Flujo',
      'heroDescription' => 'Especialistas en sistemas de medición de caudal. Ofrecemos instalación profesional de medidores de flujo industriales para garantizar precisión, eficiencia y trazabilidad en tus procesos productivos.',
      'heroImage' => 'uploads/servicios/instalacion_medidorFlujo-1.png',
      'heroImageAlt' => 'Técnico instalando medidor de flujo industrial en línea de proceso',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Instalación certificada',
      'badgeCardSubtitle' => 'Bajo estándares NOM, ISO e ISA',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 2.6a2 2 0 0 1 3.4 0l8 13.9A2 2 0 0 1 20 19.5H4a2 2 0 0 1-1.7-3L10.3 2.6z"></path><path d="M12 9v4"></path><path d="M12 16.5h.01"></path></svg>',
          'title' => 'Control de flujo preciso y confiable',
          'body' => 'En entornos industriales, una instalación incorrecta de medidores de flujo puede afectar directamente la eficiencia, calidad y seguridad del proceso. Evaluamos cuidadosamente cada variable operativa (tipo de fluido, presión, temperatura, caudal esperado, compatibilidad con PLCs y SCADA) para asegurar un sistema de medición confiable, preciso y alineado a los estándares normativos.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Selección de tecnología según fluido y proceso</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Compatibilidad con PLC, SCADA y HMI</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Cumplimiento normativo NOM, ISO e ISA</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"></path></svg>',
          'title' => 'Adaptación a tu infraestructura de proceso',
          'body' => 'Instalamos medidores de flujo industriales de última generación, desde tecnologías electromagnéticas y ultrasónicas hasta vortex y Coriolis, con opciones de salida analógica, digital o por protocolo industrial. Nos aseguramos de que el sistema quede calibrado, probado y listo para su operación, integrándolo sin conflictos a tu sistema SCADA, HMI o control local.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Electromagnéticos</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Ultrasónicos</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Vortex</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Coriolis</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">4-20 mA / HART</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Modbus</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, de la evaluación a la puesta en marcha',
      'processSubtitle' => 'Cada etapa se documenta y se valida con el responsable de proceso antes de avanzar a la siguiente.',
      'processSteps' => [
        ['title' => 'Evaluación técnica del sistema', 'body' => 'Analizamos el tipo de fluido, presión, temperatura, caudal promedio y condiciones de instalación para seleccionar la tecnología de medición más adecuada (ultrasónica, electromagnética, Coriolis, etc.).', 'deliverable' => 'Levantamiento técnico en sitio'],
        ['title' => 'Selección del medidor de flujo', 'body' => 'Recomendamos el equipo óptimo según la aplicación, compatibilidad con señales industriales (4-20 mA, Modbus, HART) y precisión requerida para el proceso.', 'deliverable' => 'Propuesta de equipo y accesorios'],
        ['title' => 'Instalación eléctrica y mecánica', 'body' => 'Instalamos el medidor en línea o por bypass según lo requerido, garantizando correcta orientación, sellado, aislamiento y conexión eléctrica para la alimentación y comunicación.', 'deliverable' => 'Instalación alambrada y protegida'],
        ['title' => 'Configuración y calibración inicial', 'body' => 'Parametrizamos el medidor según la aplicación (tipo de fluido, unidad de medida, rangos, factor K, alarmas) y realizamos calibraciones de fábrica o en campo si es necesario.', 'deliverable' => 'Respaldo de parámetros de calibración'],
        ['title' => 'Integración con sistemas de control', 'body' => 'Integramos el medidor con PLCs, SCADA o sistemas de adquisición de datos existentes, garantizando que las lecturas de flujo estén disponibles en tiempo real.', 'deliverable' => 'Variables mapeadas y verificadas'],
        ['title' => 'Pruebas funcionales y puesta en marcha', 'body' => 'Verificamos la precisión de las mediciones, respuesta a cambios de flujo, funcionamiento de salidas, alarmas, y dejamos el sistema completamente operativo y documentado.', 'deliverable' => 'Reporte de puesta en marcha'],
      ],
      'benefitsTitle' => 'Beneficios de Nuestros Servicios',
      'benefitsSubtitle' => 'Resultados medibles en precisión de medición, cumplimiento normativo y continuidad operativa.',
      'benefits' => [
        ['title' => 'Instalación bajo norma', 'body' => 'Instalación garantizada bajo estándares NOM, ISO e ISA.'],
        ['title' => 'Menor pérdida en el proceso', 'body' => 'Reducción de errores de medición y pérdidas en procesos críticos.'],
        ['title' => 'Mayor eficiencia operativa', 'body' => 'Aumento de la eficiencia operativa y trazabilidad de caudales.'],
        ['title' => 'Asesoría técnica integral', 'body' => 'Asesoría técnica desde la selección hasta la puesta en marcha del medidor.'],
        ['title' => 'Adaptación a tu planta', 'body' => 'Adaptación a líneas de proceso existentes y condiciones industriales exigentes.'],
        ['title' => 'Soporte post-instalación', 'body' => 'Soporte técnico post-instalación y ajustes finos en sitio.'],
        ['title' => 'Instalación rápida', 'body' => 'Instalaciones rápidas sin afectar la producción ni el flujo del sistema.'],
        ['title' => 'Compatibilidad total', 'body' => 'Compatibilidad con sistemas de automatización, PLCs y SCADA.'],
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
