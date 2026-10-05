@extends('frontend.layouts.master')

@section('title')
  Calibración de Quemadores y Trenes de Gas
@endsection
@section('content')
  <main>
    @include('frontend.pages.partials.service-layout', [
      'badgeText' => 'Combustión industrial · Monterrey, N.L.',
      'heroTitle' => 'Calibración de Quemadores y Trenes de Gas',
      'heroDescription' => 'Ajustamos la relación aire-gas de tu quemador y verificamos el tren de válvulas completo: reguladores, válvulas de seguridad, presostatos y actuadores. Menos consumo de gas, combustión estable y un equipo que arranca cuando debe y corta cuando tiene que cortar.',
      'heroImage' => 'frontend/images/servicios/calibracion-quemadores-tren-gas-regulador.webp',
      'heroImageAlt' => 'Tren de gas natural con válvulas Honeywell y regulador de presión en caldera industrial',
      'extraGallery' => [
        ['image' => 'frontend/images/servicios/calibracion-quemadores-tren-gas-valvulas.webp', 'alt' => 'Válvulas de seguridad y actuadores del tren de gas de un quemador industrial'],
      ],
      'statValue1' => '+30', 'statLabel1' => 'años en el sector industrial',
      'statValue2' => '24 h', 'statLabel2' => 'respuesta en área metropolitana',
      'badgeCardTitle' => 'Combustión verificada',
      'badgeCardSubtitle' => 'Con análisis de gases',
      'infoCards' => [
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path><path d="M12 18a4 4 0 0 0 1.5-7.7"></path></svg>',
          'title' => 'Ajuste de la relación aire-gas',
          'body' => 'Calibramos el quemador midiendo la combustión real con analizador de gases: oxígeno, monóxido de carbono y temperatura de chimenea. Ajustamos levas, varillaje y válvula moduladora en todo el rango de fuego, desde mínimo hasta máximo, y dejamos registro de cómo quedó cada punto.',
          'extra' => '<ul style="margin:6px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Medición de O₂, CO y temperatura de gases</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Ajuste en fuego bajo, medio y alto</li>
            <li style="display:flex;gap:10px;font-size:14.5px;font-weight:600;color:#16202B;line-height:1.45"><span style="color:#003E7E;font-weight:800">—</span>Reporte con lecturas antes y después</li>
          </ul>',
        ],
        [
          'icon' => '<svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#003E7E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 3.8 8.6 9 9 5.2-.4 9-4 9-9V7l-9-5z"></path><path d="m9 12 2 2 4-4"></path></svg>',
          'title' => 'Verificación del tren de gas completo',
          'body' => 'Revisamos la línea pieza por pieza: válvula manual de corte, filtro, regulador de presión, válvulas de seguridad de cierre automático, presostatos de gas alto y bajo, venteos y actuador modulante. Comprobamos hermeticidad, calibramos los puntos de disparo de los presostatos y validamos la secuencia del programador de llama.',
          'extra' => '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;margin-top:6px">
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#003E7E;background:#F7F9FC">Honeywell</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Maxon</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Krom Schröder</div>
            <div style="border:1px solid #DDE3EA;border-radius:4px;padding:11px 12px;font-size:13.5px;font-weight:700;color:#3C4C5D;background:#F7F9FC">Siemens</div>
          </div>',
        ],
      ],
      'processTitle' => 'Seis etapas, del levantamiento al reporte de combustión',
      'processSubtitle' => 'Nada se ajusta a ojo: cada cambio queda medido y documentado para que tu equipo de mantenimiento sepa cómo quedó el quemador.',
      'processSteps' => [
        ['title' => 'Levantamiento del equipo', 'body' => 'Registramos marca y modelo del quemador, el programador de llama, el tren de gas instalado, la presión de suministro y el combustible. Revisamos el historial de fallas y de mantenimientos previos.', 'deliverable' => 'Ficha del equipo y de la línea de gas'],
        ['title' => 'Inspección mecánica y de seguridad', 'body' => 'Verificamos el estado de válvulas, regulador, filtro, manómetros, venteos y conexiones. Buscamos fugas con el método aplicable y revisamos que no haya componentes vencidos o fuera de especificación.', 'deliverable' => 'Checklist de inspección del tren'],
        ['title' => 'Prueba de hermeticidad y seguridades', 'body' => 'Probamos el cierre de las válvulas de seguridad, el corte por presión de gas alta y baja, el presostato de aire y el paro por falla de llama. Confirmamos que el equipo bloquea cuando debe bloquear.', 'deliverable' => 'Registro de pruebas de corte'],
        ['title' => 'Calibración de la combustión', 'body' => 'Con el analizador de gases ajustamos la relación aire-combustible en todo el rango de modulación, buscando el exceso de aire adecuado con el CO dentro de límites seguros y la llama estable.', 'deliverable' => 'Lecturas por punto de fuego'],
        ['title' => 'Ajuste de puntos de disparo', 'body' => 'Calibramos los setpoints de los presostatos y, si aplica, del control de temperatura o presión de la caldera, para que el equipo module en vez de arrancar y parar constantemente.', 'deliverable' => 'Setpoints documentados'],
        ['title' => 'Reporte y recomendaciones', 'body' => 'Entregamos el reporte con las lecturas antes y después, los valores finales de cada ajuste, las observaciones de seguridad y las refacciones que conviene reemplazar o tener en almacén.', 'deliverable' => 'Reporte de combustión y bitácora'],
      ],
      'benefitsTitle' => 'Lo que obtiene tu planta',
      'benefitsSubtitle' => 'Un quemador bien calibrado gasta menos gas, se ensucia menos y deja de parar la producción por bloqueos.',
      'benefits' => [
        ['title' => 'Menos consumo de gas', 'body' => 'Un exceso de aire mal ajustado se va por la chimenea. Calibrar la relación aire-gas se paga en la factura.'],
        ['title' => 'Combustión segura', 'body' => 'Monóxido de carbono bajo control y llama estable en todo el rango de modulación.'],
        ['title' => 'Menos paros no programados', 'body' => 'Presostatos y seguridades bien calibrados dejan de bloquear el equipo sin motivo real.'],
        ['title' => 'Seguridades comprobadas', 'body' => 'Verificamos que las válvulas de corte y el paro por falla de llama actúen cuando tienen que actuar.'],
        ['title' => 'Reporte para auditoría', 'body' => 'Lecturas antes y después, setpoints y observaciones, documentados para tu bitácora de mantenimiento.'],
        ['title' => 'Refacciones originales', 'body' => 'Somos distribuidores autorizados: si hay que cambiar una válvula o un actuador, lo surtimos.'],
        ['title' => 'Vida útil del equipo', 'body' => 'Menos hollín y menos ciclos de arranque se traducen en menos desgaste del quemador y de la caldera.'],
        ['title' => 'Programa de mantenimiento', 'body' => 'Dejamos agendada la siguiente revisión conforme a las horas de operación de tu equipo.'],
      ],
      'ctaTitle' => 'Cuéntanos qué quemador tienes y te cotizamos la calibración',
      'ctaDescription' => 'Un especialista en combustión revisa tu caso y te contacta con la propuesta técnica. Si lo necesitas antes, llámanos o escríbenos por WhatsApp.',
      'ctaBullets' => [
        'Respuesta el mismo día hábil',
        'Cotización sin compromiso',
        'Servicio en sitio en el área metropolitana',
      ],
    ])
  </main>
@endsection
