@extends('frontend.layouts.master')

@section('title')
  Reparación de Equipos
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.category-detail-layout', [
      'heroBadge' => 'Servicio industrial · Monterrey, N.L.',
      'heroTitle' => 'Reparación de Equipos',
      'heroDescription' => 'Diagnosticamos, reparamos y ponemos a prueba tu equipo industrial para restablecer su operación completa, con garantía por escrito sobre la mano de obra.',
      'stats' => [
        ['value' => '+30', 'label' => 'años en el sector industrial'],
        ['value' => '24 h', 'label' => 'respuesta en área metropolitana'],
        ['value' => 'Escrita', 'label' => 'garantía sobre la reparación'],
      ],
      'services' => [
        [
          'id' => 'videoregistradores',
          'n' => '01',
          'short' => 'Reparación de videoregistradores',
          'title' => 'Reparación de Videoregistradores',
          'description' => 'Diagnóstico, corrección de fallas, calibración y pruebas funcionales para restablecer por completo el equipo y reintegrarlo a la operación.',
          'bullets' => ['Fallas de lectura y errores en pantalla', 'Tarjetas electrónicas y fuentes de alimentación', 'Almacenamiento y entradas analógicas', 'Pruebas funcionales antes de entrega'],
          'chipsLabel' => 'Marcas que atendemos',
          'chips' => ['Honeywell', 'Yokogawa', 'Eurotherm', 'Omron', 'Siemens'],
          'badge' => 'Reparación certificada',
          'norm' => 'Diagnóstico con garantía por escrito',
          'image' => 'uploads/servicios/instalacion_reparacion-videoregistradores-1.png',
          'imageAlt' => 'Técnico reparando videoregistrador industrial en taller',
          'detailsRoute' => 'servicio-reparacion-videoregistradores',
        ],
      ],
      'processBadge' => 'Cómo trabajamos',
      'processTitle' => 'Del diagnóstico a la puesta en operación',
      'processDescription' => 'Cada etapa queda documentada para que tu equipo de mantenimiento tenga trazabilidad completa.',
      'processSteps' => [
        ['n' => '01', 'title' => 'Contacto', 'description' => 'Nos cuentas la falla y compartes modelo y fotos del equipo.'],
        ['n' => '02', 'title' => 'Diagnóstico', 'description' => 'Revisión en taller o en sitio para identificar la causa raíz.'],
        ['n' => '03', 'title' => 'Propuesta', 'description' => 'Cotización de la reparación, con alcance y tiempo estimado.'],
        ['n' => '04', 'title' => 'Reparación', 'description' => 'Corrección de la falla: tarjetas, fuentes, sensores o componentes.'],
        ['n' => '05', 'title' => 'Pruebas funcionales', 'description' => 'Verificación del equipo bajo condiciones reales de operación.'],
        ['n' => '06', 'title' => 'Entrega', 'description' => 'Reintegración a planta y documentación del servicio.'],
      ],
      'whyBadge' => 'Por qué Mac del Norte',
      'whyTitle' => 'Reparamos lo que otros te dirían que reemplaces',
      'whyDescription' => 'Diagnosticamos antes de cotizar y, si no conviene reparar, te lo decimos con honestidad y te proponemos la mejor alternativa.',
      'industries' => ['Tratamiento térmico', 'Calderas y vapor', 'Plásticos', 'Alimentos y bebidas', 'Farmacéutica', 'Metalmecánica', 'Tratamiento de agua', 'Químicos'],
      'values' => [
        ['title' => 'Diagnóstico antes de cotizar', 'description' => 'Revisamos la falla para darte un presupuesto preciso antes de intervenir.'],
        ['title' => 'Equipo multimarca', 'description' => 'Reparamos equipo de distintos fabricantes, no solo los que vendemos.'],
        ['title' => 'Respuesta en 24 h', 'description' => 'Atención rápida en el área metropolitana de Monterrey.'],
        ['title' => 'Garantía por escrito', 'description' => 'Respaldo sobre la mano de obra de la reparación entregada.'],
      ],
      'faqBadge' => 'Preguntas frecuentes',
      'faq' => [
        ['q' => '¿Reparan equipos que no compré con ustedes?', 'a' => 'Sí. Revisamos marca y modelo antes de confirmar que podemos repararlo.'],
        ['q' => '¿Cuánto tarda una reparación?', 'a' => 'Depende de la falla y disponibilidad de refacciones; te damos un tiempo estimado con el diagnóstico.'],
        ['q' => '¿Qué pasa si el equipo no tiene reparación?', 'a' => 'Te lo decimos con el diagnóstico y te cotizamos el reemplazo si lo necesitas.'],
        ['q' => '¿Puedo mandar el equipo desde fuera de Monterrey?', 'a' => 'Sí, recibimos equipo de cualquier parte de México para diagnóstico y reparación en taller.'],
        ['q' => '¿La reparación tiene garantía?', 'a' => 'Sí, garantía por escrito sobre la mano de obra de la reparación entregada.'],
        ['q' => '¿Cómo cotizo una reparación?', 'a' => 'Envíanos modelo, fotos del equipo y una descripción de la falla por WhatsApp o correo.'],
      ],
      'ctaTitle' => 'Envíanos foto de tu equipo o tablero',
    ])
  </main>
@endsection
