@extends('frontend.layouts.master')

@section('title')
  Instalación y Configuración
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.category-hub-layout', [
      'heroBadge' => 'Instalación y Configuración · Monterrey, N.L.',
      'heroTitle' => 'Instalación y configuración de instrumentación industrial',
      'heroDescription' => 'Montamos, configuramos e integramos controladores, videoregistradores, medidores de flujo y sistemas PLC en tu planta, con técnicos especializados y puesta en marcha documentada.',
      'services' => [
        [
          'n' => '01',
          'title' => 'Controladores de Temperatura',
          'description' => 'Instalamos, configuramos e integramos controladores de temperatura para hornos, calderas, extrusión, inyección, secado y refrigeración.',
          'image' => 'uploads/servicios/instalacion_controladores-1.png',
          'imageAlt' => 'Técnico instalando controlador de temperatura en tablero',
          'bullets' => ['Hornos, calderas y secadores', 'Inyección, extrusión y termoformado', 'Refrigeración y cadena de frío'],
          'norm' => 'NOM · ISO',
          'detailsRoute' => 'servicio-controladores-temperatura',
        ],
        [
          'n' => '02',
          'title' => 'Videoregistradores',
          'description' => 'Instalación profesional de grabadores de variables analógicas, para confiabilidad y precisión en el monitoreo de tus procesos.',
          'image' => 'uploads/servicios/instalacion_videoregistradores-1.png',
          'imageAlt' => 'Técnico instalando videoregistrador de variables analógicas en tablero industrial',
          'bullets' => ['Cableado de canales analógicos', 'Configuración de pantallas y alarmas', 'Exportación de históricos'],
          'norm' => 'NOM · ISO',
          'detailsRoute' => 'servicio-instalacion-videoregistradores',
        ],
        [
          'n' => '03',
          'title' => 'Medidores de Flujo',
          'description' => 'Instalación y puesta en marcha de medidores de caudal industriales, con precisión, eficiencia y trazabilidad.',
          'image' => 'frontend/images/servicios/medidores-flujo-transmisor-presion.jpg',
          'imageAlt' => 'Transmisor de presión Honeywell instalado en línea de proceso industrial',
          'bullets' => ['Selección del punto de medición', 'Configuración del transmisor', 'Verificación con flujo real'],
          'norm' => 'NOM · ISO · ISA',
          'detailsRoute' => 'servicio-instalacion-medidoresdeflujo',
        ],
        [
          'n' => '04',
          'title' => 'PLC — Llave en Mano',
          'description' => 'Servicio integral de automatización con PLC, desde el diseño del proyecto hasta la puesta en marcha.',
          'image' => 'uploads/servicios/instalacion_plc-1.png',
          'imageAlt' => 'Técnico instalando y configurando un PLC en tablero de control industrial',
          'bullets' => ['Diseño del proyecto de automatización', 'Programación y pruebas de lógica', 'Puesta en marcha documentada'],
          'norm' => 'IEC · UL · NOM',
          'detailsRoute' => 'servicio-instalacion-plc',
        ],
      ],
      'valueProps' => [
        ['title' => 'Diagnóstico antes de cotizar', 'description' => 'Revisamos tu instalación para evitar sorpresas en costo y tiempo.'],
        ['title' => 'Técnicos especializados', 'description' => 'Experiencia en instrumentación y control industrial.'],
        ['title' => 'Equipo + instalación', 'description' => 'Suministramos el equipo y lo dejamos funcionando.'],
        ['title' => 'Garantía por escrito', 'description' => 'Respaldo sobre mano de obra y documentación entregada.'],
      ],
      'catalogBadge' => 'Catálogo de servicios',
      'catalogTitle' => '¿Qué equipo necesitas instalar o configurar?',
      'processBadge' => 'Misma metodología en cada instalación',
      'processTitle' => 'De la llamada a la entrega documentada',
      'processDescription' => 'Cada etapa queda registrada para que tu equipo de mantenimiento tenga trazabilidad completa.',
      'processSteps' => [
        ['n' => '01', 'title' => 'Contacto', 'description' => 'Compartes modelo, fotos y necesidad del proceso.'],
        ['n' => '02', 'title' => 'Levantamiento', 'description' => 'Visita técnica para revisar tablero, señales y condiciones.'],
        ['n' => '03', 'title' => 'Propuesta', 'description' => 'Cotización con alcance, tiempos y materiales.'],
        ['n' => '04', 'title' => 'Instalación', 'description' => 'Montaje, cableado y etiquetado según normativa.'],
        ['n' => '05', 'title' => 'Configuración', 'description' => 'Parámetros, alarmas y pruebas con proceso real.'],
        ['n' => '06', 'title' => 'Entrega', 'description' => 'Arranque, capacitación y documentación final.'],
      ],
      'industriesBadge' => 'Industrias que atendemos',
      'industriesTitle' => 'Procesos donde la medición y el control no pueden fallar',
      'industries' => ['Tratamiento térmico', 'Calderas y vapor', 'Plásticos', 'Alimentos y bebidas', 'Farmacéutica', 'Metalmecánica', 'Tratamiento de agua', 'Químicos', 'Refrigeración', 'Automotriz'],
      'brandsBadge' => 'Marcas y cobertura',
      'brandsTitle' => 'Honeywell y equipos multimarca',
      'brandsDescription' => 'Si ya tienes el equipo, lo instalamos. Si no, te lo suministramos con el mismo servicio. Base en Monterrey, N.L., atención en sitio en el noreste y envío de equipo a todo México.',
      'brandTags' => ['Honeywell', 'Omron', 'Autonics', 'Siemens', 'Novus', 'Delta'],
      'faqBadge' => 'Preguntas frecuentes',
      'faq' => [
        ['q' => '¿Instalan equipos que compramos en otro lugar?', 'a' => 'Sí. Revisamos modelo y estado del equipo antes de la visita para confirmar compatibilidad.'],
        ['q' => '¿Pueden suministrar el equipo además de instalarlo?', 'a' => 'Sí. Cotizamos equipo e instalación en una sola propuesta.'],
        ['q' => '¿Necesito parar la producción?', 'a' => 'Planeamos la intervención en paros programados o fines de semana para minimizar el impacto.'],
        ['q' => '¿Dan servicio fuera de Monterrey?', 'a' => 'Atendemos en sitio en el noreste y coordinamos visitas al resto de México; los equipos se envían a todo el país.'],
        ['q' => '¿Qué garantía tienen los servicios?', 'a' => 'Garantía por escrito sobre mano de obra, además de la garantía de fábrica del equipo suministrado.'],
        ['q' => '¿Cómo cotizo?', 'a' => 'Envíanos modelo, fotos del equipo o tablero y una breve descripción del proceso por WhatsApp o correo.'],
      ],
    ])
  </main>
@endsection
