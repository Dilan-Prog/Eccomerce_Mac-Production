@extends('frontend.layouts.master')

@section('title')
  Calibraciones
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.category-detail-layout', [
      'heroBadge' => 'Acreditación EMA · Monterrey, N.L.',
      'heroTitle' => 'Calibraciones',
      'heroDescription' => 'Calibramos y certificamos videoregistradores y medidores de flujo industriales con acreditación EMA, asegurando trazabilidad, confiabilidad y cumplimiento normativo de tus instrumentos de medición.',
      'stats' => [
        ['value' => '+30', 'label' => 'años en el sector industrial'],
        ['value' => '24 h', 'label' => 'respuesta en área metropolitana'],
        ['value' => 'EMA', 'label' => 'acreditación reconocida'],
      ],
      'services' => [
        [
          'id' => 'calibracion-ema',
          'n' => '01',
          'short' => 'Calibraciones EMA',
          'title' => 'Calibraciones EMA de Videoregistradores y Medidores de Flujo',
          'description' => 'Calibración acreditada ante la EMA para videoregistradores de variables analógicas y medidores de flujo, con certificados válidos ante auditorías ISO 9001, IATF y NOM.',
          'bullets' => ['Temperatura, presión y nivel', 'Señales eléctricas 4-20 mA, mV, V', 'Certificados para ISO 9001, IATF y NOM', 'Trazabilidad a patrones nacionales'],
          'chipsLabel' => 'Marcas que atendemos',
          'chips' => ['Honeywell', 'Yokogawa', 'Eurotherm', 'Omron', 'Siemens'],
          'badge' => 'Calibración acreditada',
          'norm' => 'Con acreditación EMA',
          'image' => 'uploads/servicios/calibracion-ema-1.png',
          'imageAlt' => 'Técnico calibrando videoregistrador industrial con acreditación EMA',
          'detailsRoute' => 'servicio-calibracion-ema',
        ],
      ],
      'processBadge' => 'Cómo trabajamos',
      'processTitle' => 'Del levantamiento al certificado acreditado',
      'processDescription' => 'Cada calibración queda documentada para que tu equipo de calidad tenga trazabilidad completa.',
      'processSteps' => [
        ['n' => '01', 'title' => 'Contacto', 'description' => 'Compartes marca, modelo y rango del instrumento a calibrar.'],
        ['n' => '02', 'title' => 'Programación', 'description' => 'Agendamos la calibración en sitio o la recolección del equipo.'],
        ['n' => '03', 'title' => 'Propuesta', 'description' => 'Cotización con puntos de calibración y tiempo de entrega.'],
        ['n' => '04', 'title' => 'Calibración', 'description' => 'Medición contra patrones trazables, bajo procedimiento acreditado.'],
        ['n' => '05', 'title' => 'Análisis de resultados', 'description' => 'Cálculo de incertidumbre y verificación de cumplimiento.'],
        ['n' => '06', 'title' => 'Entrega', 'description' => 'Certificado acreditado EMA y reintegración del equipo.'],
      ],
      'whyBadge' => 'Por qué Mac del Norte',
      'whyTitle' => 'Acreditación EMA para instrumentación multimarca',
      'whyDescription' => 'Nuestros certificados son reconocidos por la Entidad Mexicana de Acreditación, válidos ante auditorías de calidad de tu planta.',
      'industries' => ['Tratamiento térmico', 'Calderas y vapor', 'Plásticos', 'Alimentos y bebidas', 'Farmacéutica', 'Metalmecánica', 'Tratamiento de agua', 'Químicos'],
      'values' => [
        ['title' => 'Acreditación EMA', 'description' => 'Certificados reconocidos por la Entidad Mexicana de Acreditación.'],
        ['title' => 'Trazabilidad garantizada', 'description' => 'Resultados trazables a patrones nacionales de referencia.'],
        ['title' => 'Válido para auditorías', 'description' => 'Certificados aceptados en auditorías ISO 9001, IATF y NOM.'],
        ['title' => 'Entrega documentada', 'description' => 'Certificado de calibración con incertidumbre y resultados.'],
      ],
      'faqBadge' => 'Preguntas frecuentes',
      'faq' => [
        ['q' => '¿Qué significa que la calibración sea acreditada EMA?', 'a' => 'Que el procedimiento y los patrones usados están reconocidos por la Entidad Mexicana de Acreditación, dando validez al certificado ante auditorías.'],
        ['q' => '¿Qué instrumentos calibran?', 'a' => 'Videoregistradores de variables analógicas y medidores de flujo; consúltanos si tu instrumento no aparece en el catálogo.'],
        ['q' => '¿La calibración es en sitio o en laboratorio?', 'a' => 'Depende del instrumento; algunos se calibran en planta y otros se recolectan para calibrarse en laboratorio.'],
        ['q' => '¿Cuánto tarda la entrega del certificado?', 'a' => 'El tiempo depende del instrumento y los puntos de calibración; te lo confirmamos en la propuesta.'],
        ['q' => '¿Dan servicio fuera de Monterrey?', 'a' => 'Sí, coordinamos visitas al resto de México y recibimos equipo de todo el país.'],
        ['q' => '¿Cómo cotizo una calibración?', 'a' => 'Envíanos marca, modelo y rango del instrumento por WhatsApp o correo.'],
      ],
      'ctaTitle' => 'Envíanos marca y modelo de tu instrumento',
    ])
  </main>
@endsection
