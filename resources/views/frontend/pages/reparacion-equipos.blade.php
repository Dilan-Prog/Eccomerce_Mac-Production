@extends('frontend.layouts.master')

@section('title')
  Reparación de Equipos
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.category-hub-layout', [
      'heroBadge' => 'Reparación de Equipos · Monterrey, N.L.',
      'heroTitle' => 'Reparación y mantenimiento de instrumentación industrial',
      'heroDescription' => 'Diagnosticamos, reparamos y ponemos a prueba tu equipo industrial para restablecer su operación completa, con garantía por escrito sobre la mano de obra.',
      'services' => [
        [
          'n' => '01',
          'title' => 'Reparación de Videoregistradores',
          'description' => 'Diagnóstico, corrección de fallas, calibración y pruebas funcionales para restablecer por completo el equipo y reintegrarlo a la operación.',
          'image' => 'uploads/servicios/instalacion_reparacion-videoregistradores-1.png',
          'imageAlt' => 'Técnico reparando videoregistrador industrial en taller',
          'bullets' => ['Fallas de lectura y errores en pantalla', 'Tarjetas electrónicas y fuentes de alimentación', 'Almacenamiento y entradas analógicas'],
          'norm' => 'Multimarca',
          'detailsRoute' => 'servicio-reparacion-videoregistradores',
        ],
      ],
      'valueProps' => [
        ['title' => 'Diagnóstico antes de cotizar', 'description' => 'Revisamos la falla para darte un presupuesto preciso antes de intervenir.'],
        ['title' => 'Reparación o reemplazo honesto', 'description' => 'Si no conviene reparar, te lo decimos y te proponemos la mejor alternativa.'],
        ['title' => 'Equipo multimarca', 'description' => 'Trabajamos con equipos de distintos fabricantes, no solo los que vendemos.'],
        ['title' => 'Garantía por escrito', 'description' => 'Respaldo sobre la reparación entregada, documentado.'],
      ],
      'catalogBadge' => 'Catálogo de servicios',
      'catalogTitle' => '¿Qué equipo necesitas reparar?',
      'processBadge' => 'Misma metodología en cada reparación',
      'processTitle' => 'Del diagnóstico a la puesta en operación',
      'processDescription' => 'Cada etapa queda registrada para que tu equipo de mantenimiento tenga trazabilidad completa.',
      'processSteps' => [
        ['n' => '01', 'title' => 'Contacto', 'description' => 'Nos cuentas la falla y nos compartes modelo y fotos del equipo.'],
        ['n' => '02', 'title' => 'Diagnóstico', 'description' => 'Revisión en taller o en sitio para identificar la causa raíz.'],
        ['n' => '03', 'title' => 'Propuesta', 'description' => 'Cotización de la reparación, con alcance y tiempo estimado.'],
        ['n' => '04', 'title' => 'Reparación', 'description' => 'Corrección de la falla: tarjetas, fuentes, sensores o componentes dañados.'],
        ['n' => '05', 'title' => 'Pruebas funcionales', 'description' => 'Verificación del equipo bajo condiciones reales de operación.'],
        ['n' => '06', 'title' => 'Entrega', 'description' => 'Reintegración a planta y documentación del servicio.'],
      ],
      'industriesBadge' => 'Industrias que atendemos',
      'industriesTitle' => 'Equipo que no puede quedarse fuera de servicio',
      'industries' => ['Tratamiento térmico', 'Calderas y vapor', 'Plásticos', 'Alimentos y bebidas', 'Farmacéutica', 'Metalmecánica', 'Tratamiento de agua', 'Químicos', 'Refrigeración', 'Automotriz'],
      'brandsBadge' => 'Marcas y cobertura',
      'brandsTitle' => 'Honeywell, Yokogawa, Eurotherm y más',
      'brandsDescription' => 'Reparamos equipo de distintos fabricantes, no solo los que distribuimos. Base en Monterrey, N.L., atención en sitio en el noreste y recepción de equipo de todo México.',
      'brandTags' => ['Honeywell', 'Yokogawa', 'Eurotherm', 'Omron', 'Siemens', 'Multimarca'],
      'faqBadge' => 'Preguntas frecuentes',
      'faq' => [
        ['q' => '¿Reparan equipos que no compré con ustedes?', 'a' => 'Sí. Revisamos marca y modelo antes de confirmar que podemos repararlo.'],
        ['q' => '¿Cuánto tarda una reparación?', 'a' => 'Depende de la falla y disponibilidad de refacciones; te damos un tiempo estimado con el diagnóstico.'],
        ['q' => '¿Qué pasa si el equipo no tiene reparación?', 'a' => 'Te lo decimos con el diagnóstico y te cotizamos el reemplazo si lo necesitas.'],
        ['q' => '¿Puedo mandar el equipo desde fuera de Monterrey?', 'a' => 'Sí, recibimos equipo de cualquier parte de México para diagnóstico y reparación en taller.'],
        ['q' => '¿La reparación tiene garantía?', 'a' => 'Sí, garantía por escrito sobre la mano de obra de la reparación entregada.'],
        ['q' => '¿Cómo cotizo una reparación?', 'a' => 'Envíanos modelo, fotos del equipo y una descripción de la falla por WhatsApp o correo.'],
      ],
    ])
  </main>
@endsection
