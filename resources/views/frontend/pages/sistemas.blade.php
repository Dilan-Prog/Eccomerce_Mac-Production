@extends('frontend.layouts.master')

@section('title')
  Instalación de Controles de Temperatura
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Instalación de Controladores de Temperatura',
      'heroDescription' => 'Realizamos instalaciones especializadas de controladores de temperatura para procesos industriales, comerciales y de automatización. Nuestro equipo técnico garantiza precisión, cumplimiento normativo y soluciones adaptadas a los requerimientos térmicos de cada aplicación, con respaldo técnico desde el primer momento.',
      'heroImage' => 'frontend/images/imagen ejemplo.png',
      'heroImageAlt' => 'Técnico instalando y configurando un controlador de temperatura industrial',
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Instalación certificada',
      'badgeCardSubtitle' => 'Bajo normativa NOM e ISO',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 2.6a2 2 0 0 1 3.4 0l8 13.9A2 2 0 0 1 20 19.5H4a2 2 0 0 1-1.7-3L10.3 2.6z"></path><path d="M12 9v4"></path><path d="M12 16.5h.01"></path></svg>',
          'title' => 'Aplicación industrial crítica',
          'body' => 'En la industria, el control térmico no es opcional: es vital. Un controlador mal instalado puede causar variaciones de temperatura, errores en el proceso o incluso fallas en la producción. Evaluamos cada variable del entorno —tipo de carga, sensores, salidas, comunicación con PLC o HMI— para garantizar un funcionamiento estable, seguro y continuo.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Sensores: termopares y RTD</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Salidas de control y relés de estado sólido</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Comunicación con PLC, HMI o SCADA</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"></path></svg>',
          'title' => 'Tecnología adaptada a tu proceso',
          'body' => 'Instalamos desde controladores PID simples hasta sistemas con lógica compleja, comunicación Modbus, relés de estado sólido y más. Integramos equipos de marcas reconocidas y adaptamos la tecnología a tu línea de producción, no al revés.',
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
      'processTitle' => 'Nuestro proceso de instalación, paso a paso',
      'processSubtitle' => 'Cada etapa se revisa contigo antes de avanzar a la siguiente, para garantizar resultados sin sorpresas.',
      'processSteps' => [
        ['title' => 'Evaluación técnica del entorno', 'body' => 'Analizamos maquinaria, procesos térmicos, sensores y espacio físico para definir el tipo de controlador ideal.', 'deliverable' => 'Levantamiento técnico en sitio'],
        ['title' => 'Selección del controlador adecuado', 'body' => 'Recomendamos el modelo y configuración más eficientes según el perfil térmico y la compatibilidad industrial.', 'deliverable' => 'Propuesta de equipo y accesorios'],
        ['title' => 'Instalación eléctrica y física', 'body' => 'Conectamos sensores (termopares, RTD), actuadores y alimentaciones con conexiones seguras y certificadas.', 'deliverable' => 'Instalación alambrada y protegida'],
        ['title' => 'Configuración y parametrización', 'body' => 'Ajustamos parámetros PID, rangos de temperatura, modos de control y alarmas según tu aplicación.', 'deliverable' => 'Respaldo de parámetros'],
        ['title' => 'Integración con sistemas existentes', 'body' => 'Conectamos con PLCs, HMIs o SCADA vía comunicación serial, Ethernet o relé, si aplica.', 'deliverable' => 'Variables mapeadas y verificadas'],
        ['title' => 'Pruebas y puesta en marcha', 'body' => 'Verificamos precisión, estabilidad térmica y funcionalidad total antes de entregar el sistema, con capacitación breve al personal.', 'deliverable' => 'Reporte de puesta en marcha'],
      ],
      'benefitsTitle' => 'Beneficios de nuestros servicios',
      'benefitsSubtitle' => 'Resultados que se notan en la estabilidad térmica, el cumplimiento normativo y la continuidad de tu producción.',
      'benefits' => [
        ['title' => 'Cumplimiento NOM e ISO', 'body' => 'Instalación garantizada bajo estándares normativos vigentes para tu industria.'],
        ['title' => 'Menos paros no programados', 'body' => 'Reducimos fallos térmicos y disparos por sobrecalentamiento que detienen la producción.'],
        ['title' => 'Mayor vida útil del equipo', 'body' => 'Tu sistema térmico y sus componentes trabajan dentro de rango, sin desgaste prematuro.'],
        ['title' => 'Asesoría técnica completa', 'body' => 'Acompañamiento desde la evaluación inicial hasta la calibración final del sistema.'],
        ['title' => 'Adaptación a tu espacio', 'body' => 'Soluciones para entornos exigentes o con espacio físico limitado en planta.'],
        ['title' => 'Soporte post-instalación', 'body' => 'Atención ante dudas, ajustes o reconfiguración después de la puesta en marcha.'],
        ['title' => 'Instalación rápida', 'body' => 'Tiempos de trabajo cortos que no afectan la continuidad de tu producción.'],
        ['title' => 'Compatibilidad con SCADA', 'body' => 'Equipos listos para integrarse con sistemas SCADA y automatización industrial.'],
      ],
      'ctaTitle' => 'Cuéntanos tu proyecto y te enviamos una propuesta',
      'ctaDescription' => 'Un especialista revisa tu proceso y te contacta con la solución técnica adecuada. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Respuesta el mismo día hábil',
        'Cotización sin compromiso',
        'Cobertura en Monterrey y área metropolitana',
      ],
    ])
  </main>
@endsection
