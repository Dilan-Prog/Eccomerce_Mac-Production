<?php

/*
|--------------------------------------------------------------------------
| Empleos: vacantes y textos de la página /empleos
|--------------------------------------------------------------------------
|
| Edita SOLO este archivo para agregar, cambiar o apagar vacantes. No hay pantalla
| en el admin para esto. La postulación es 100% por WhatsApp: cada botón abre el
| chat con un mensaje ya escrito (plantillas de 'mensajes').
|
| El número de WhatsApp y el horario NO se escriben aquí: salen de config/contact.php
| (whatsapp.number y hours.es).
|
| Vacante nueva: copia un bloque de 'vacantes', cambia la clave y la 'ref' (deben ser
| únicas, sin espacios) y pon 'activa' => true. Para ocultar una vacante sin borrarla,
| 'activa' => false. Las 'areas' que uses en 'area' deben existir en la lista 'areas'.
|
| Después de editar en producción: php artisan config:clear (o config:cache).
|
*/

return [

    // Mensajes de WhatsApp prellenados. :puesto y :ref se sustituyen por los de la vacante.
    'mensajes' => [
        'postular' => 'Hola, quiero postularme a la vacante :puesto (ref. :ref). Te envío mi CV por este chat.',
        'duda'     => 'Hola, tengo una duda sobre la vacante :puesto (ref. :ref).',
        'general'  => 'Hola, quiero enviar mi CV para la cartera general de vacantes de Mac del Norte.',
    ],

    'areas' => [
        ['nombre' => 'Servicio técnico', 'descripcion' => 'Instalación, configuración y mantenimiento de instrumentación en planta y taller.'],
        ['nombre' => 'Proyectos',        'descripcion' => 'Ingeniería, programación de PLC y proyectos llave en mano.'],
        ['nombre' => 'Ventas',           'descripcion' => 'Asesoría técnica-comercial y desarrollo de cuentas industriales.'],
        ['nombre' => 'Operaciones',      'descripcion' => 'Almacén, logística y administración que sostienen cada entrega.'],
    ],

    // 'modalidad': Campo | Presencial | Híbrido | Prácticas.  'publicada': fecha AAAA-MM-DD.
    // No hay campo de sueldo: la página muestra "Sueldo a tratar".
    'vacantes' => [

        'tecnico-instrumentacion' => [
            'ref'         => 'ST-01',
            'titulo'      => 'Técnico en Instrumentación',
            'area'        => 'Servicio técnico',
            'modalidad'   => 'Campo',
            'horario'     => 'Lun–Vie 8:30–18:00',
            'publicada'   => '2026-10-06',
            'experiencia' => '2+ años',
            'escolaridad' => 'Técnico / Ingeniería',
            'descripcion' => 'Instalarás, configurarás y darás mantenimiento a controladores de temperatura, videoregistradores y medidores de flujo en plantas de nuestros clientes del área metropolitana.',
            'responsabilidades' => [
                'Instalación y cableado de instrumentos en tablero',
                'Configuración y sintonía PID de controladores',
                'Diagnóstico de fallas en sitio',
                'Elaboración de reportes de servicio',
            ],
            'requisitos' => [
                'Técnico o Ing. en electrónica, mecatrónica o afín',
                '2+ años en instrumentación industrial',
                'Lectura de diagramas eléctricos',
                'Licencia de manejo vigente',
            ],
            'deseable' => ['Experiencia con equipos Honeywell UDC', 'Curso DC-3 trabajo en alturas'],
            'ofrecemos' => ['Prestaciones superiores a la ley', 'Capacitación Honeywell', 'Uniforme y EPP', 'Vehículo para servicio'],
            'activa' => true,
        ],

        'tecnico-jr-taller' => [
            'ref'         => 'ST-02',
            'titulo'      => 'Técnico Jr. de Taller',
            'area'        => 'Servicio técnico',
            'modalidad'   => 'Presencial',
            'horario'     => 'Lun–Vie 8:30–18:00',
            'publicada'   => '2026-10-02',
            'experiencia' => '0–1 años',
            'escolaridad' => 'Técnico',
            'descripcion' => 'Prepararás, probarás y configurarás equipos en taller antes de su entrega o instalación, con acompañamiento de técnicos senior.',
            'responsabilidades' => [
                'Pruebas de equipos en banco',
                'Configuración básica de controladores',
                'Armado de pequeños tableros',
                'Control de herramienta de taller',
            ],
            'requisitos' => [
                'Técnico en electrónica o electricidad',
                'Uso de multímetro',
                'Disposición para aprender',
                'Trabajo en equipo',
            ],
            'deseable' => ['Prácticas en área industrial'],
            'ofrecemos' => ['Prestaciones superiores a la ley', 'Capacitación Honeywell', 'Uniforme y EPP', 'Plan de carrera a Técnico'],
            'activa' => true,
        ],

        'ingeniero-automatizacion' => [
            'ref'         => 'PR-01',
            'titulo'      => 'Ingeniero de Automatización (PLC)',
            'area'        => 'Proyectos',
            'modalidad'   => 'Híbrido',
            'horario'     => 'Lun–Vie 8:30–18:00',
            'publicada'   => '2026-10-07',
            'experiencia' => '3+ años',
            'escolaridad' => 'Ingeniería',
            'descripcion' => 'Diseñarás y programarás proyectos llave en mano con PLC y HMI, desde la ingeniería hasta la puesta en marcha con el cliente.',
            'responsabilidades' => [
                'Ingeniería y diseño de tableros de control',
                'Programación de PLC HC900 y HMI',
                'Integración de instrumentación de campo',
                'Arranque y capacitación al cliente',
            ],
            'requisitos' => [
                'Ing. en mecatrónica, electrónica o control',
                '3+ años programando PLC',
                'Protocolos Modbus / Ethernet IP',
                'Inglés técnico',
            ],
            'deseable' => ['Experiencia en SCADA', 'Normas IEC / UL'],
            'ofrecemos' => ['Prestaciones superiores a la ley', 'Capacitación Honeywell', 'Uniforme y EPP', 'Laptop y licencias'],
            'activa' => true,
        ],

        'ejecutivo-ventas-industriales' => [
            'ref'         => 'VT-01',
            'titulo'      => 'Ejecutivo de Ventas Industriales',
            'area'        => 'Ventas',
            'modalidad'   => 'Campo',
            'horario'     => 'Lun–Vie 8:30–18:00',
            'publicada'   => '2026-10-04',
            'experiencia' => '2+ años',
            'escolaridad' => 'Licenciatura / Ingeniería',
            'descripcion' => 'Desarrollarás cuentas industriales ofreciendo equipo Honeywell y McDonnell & Miller junto con nuestros servicios de instalación.',
            'responsabilidades' => [
                'Prospección y visita a plantas industriales',
                'Elaboración de cotizaciones técnicas',
                'Seguimiento de cartera de clientes',
                'Coordinación con el área técnica',
            ],
            'requisitos' => [
                'Ing. industrial, mecánico o afín',
                '2+ años en ventas B2B industriales',
                'Manejo de CRM',
                'Auto propio',
            ],
            'deseable' => ['Conocimiento de instrumentación', 'Cartera en Nuevo León'],
            'ofrecemos' => ['Prestaciones superiores a la ley', 'Capacitación Honeywell', 'Uniforme y EPP', 'Comisiones sin tope', 'Apoyo de gasolina'],
            'activa' => true,
        ],

        'auxiliar-almacen' => [
            'ref'         => 'OP-01',
            'titulo'      => 'Auxiliar de Almacén',
            'area'        => 'Operaciones',
            'modalidad'   => 'Presencial',
            'horario'     => 'Lun–Vie 8:30–18:00',
            'publicada'   => '2026-10-02',
            'experiencia' => '1+ año',
            'escolaridad' => 'Preparatoria',
            'descripcion' => 'Controlarás entradas, salidas e inventario de equipo y refacciones, y prepararás envíos a clientes en todo México.',
            'responsabilidades' => [
                'Recepción y acomodo de mercancía',
                'Surtido y empaque de pedidos',
                'Conteos cíclicos de inventario',
                'Coordinación con paqueterías',
            ],
            'requisitos' => [
                'Preparatoria terminada',
                '1+ año en almacén',
                'Manejo básico de Excel',
                'Orden y atención al detalle',
            ],
            'deseable' => ['Manejo de montacargas'],
            'ofrecemos' => ['Prestaciones superiores a la ley', 'Capacitación Honeywell', 'Uniforme y EPP'],
            'activa' => true,
        ],

        'practicante-ingenieria' => [
            'ref'         => 'PP-01',
            'titulo'      => 'Practicante de Ingeniería',
            'area'        => 'Servicio técnico',
            'modalidad'   => 'Prácticas',
            'horario'     => '4–6 h diarias',
            'publicada'   => '2026-10-05',
            'experiencia' => 'Sin experiencia',
            'escolaridad' => 'Estudiante 7.º sem.+',
            'descripcion' => 'Acompañarás a nuestros técnicos en instalaciones y aprenderás configuración de equipos industriales en proyectos reales.',
            'responsabilidades' => [
                'Apoyo en instalaciones en planta',
                'Preparación de equipos en taller',
                'Documentación de proyectos',
                'Capacitación en equipos Honeywell',
            ],
            'requisitos' => [
                'Estudiante de 7.º semestre o más',
                'Electrónica, mecatrónica o afín',
                'Disponibilidad de 4–6 horas',
                'Ganas de aprender',
            ],
            'deseable' => ['Proyecto de residencia profesional'],
            'ofrecemos' => ['Constancia de prácticas', 'Capacitación Honeywell', 'Posibilidad de contratación'],
            'activa' => true,
        ],

    ],

    'carrera' => [
        ['n' => '01', 'tiempo' => '6–12 meses',    'titulo' => 'Practicante',          'descripcion' => 'Aprendes en taller y acompañas instalaciones reales.',            'habilidades' => ['Seguridad', 'Cableado']],
        ['n' => '02', 'tiempo' => '1–2 años',      'titulo' => 'Técnico Jr.',          'descripcion' => 'Pruebas en banco, configuración básica y apoyo en campo.',        'habilidades' => ['UDC', 'Diagramas']],
        ['n' => '03', 'tiempo' => '2–4 años',      'titulo' => 'Técnico de Servicio',  'descripcion' => 'Atiendes servicios en planta de forma independiente.',            'habilidades' => ['PID', 'Flujo', 'Registro']],
        ['n' => '04', 'tiempo' => '4+ años',       'titulo' => 'Técnico Senior',       'descripcion' => 'Resuelves casos complejos y formas a nuevos técnicos.',           'habilidades' => ['PLC', 'Modbus']],
        ['n' => '05', 'tiempo' => 'Por desempeño', 'titulo' => 'Líder de Proyecto',    'descripcion' => 'Coordinas proyectos llave en mano de principio a fin.',           'habilidades' => ['Gestión', 'Cliente']],
    ],

    'beneficios' => [
        ['titulo' => 'Prestaciones', 'items' => [
            ['t' => 'Superiores a la ley', 'd' => 'Vacaciones, prima vacacional y aguinaldo por encima del mínimo.'],
            ['t' => 'IMSS e Infonavit',    'd' => 'Alta desde el primer día.'],
            ['t' => 'Fondo de ahorro',     'd' => 'Aportación de la empresa para tu ahorro anual.'],
            ['t' => 'Vales de despensa',   'd' => 'Apoyo mensual adicional al sueldo.'],
        ]],
        ['titulo' => 'Desarrollo', 'items' => [
            ['t' => 'Capacitación Honeywell', 'd' => 'Formación en los equipos que instalamos.'],
            ['t' => 'Plan de carrera',        'd' => 'Ruta técnica clara con evaluaciones periódicas.'],
        ]],
        ['titulo' => 'Día a día', 'items' => [
            ['t' => 'Herramienta y EPP', 'd' => 'Uniforme, equipo de protección y herramienta.'],
            ['t' => 'Horario estable',   'd' => 'Lunes a viernes de 8:30am a 6:00pm.'],
        ]],
    ],

    'proceso' => [
        ['n' => '01', 'titulo' => 'Escribes por WhatsApp', 'descripcion' => 'Eliges la vacante, se abre el chat con tu mensaje listo y envías tu CV ahí mismo.', 'tiempo' => 'Día 1'],
        ['n' => '02', 'titulo' => 'Revisión',              'descripcion' => 'Recursos Humanos evalúa tu perfil.',                                                   'tiempo' => '≤ 5 días'],
        ['n' => '03', 'titulo' => 'Entrevista',            'descripcion' => 'Conversación con RH y el líder del área.',                                             'tiempo' => 'Semana 1–2'],
        ['n' => '04', 'titulo' => 'Prueba técnica',        'descripcion' => 'Evaluación práctica según la posición.',                                               'tiempo' => 'Semana 2'],
        ['n' => '05', 'titulo' => 'Oferta e ingreso',      'descripcion' => 'Propuesta, documentación e inducción.',                                                'tiempo' => 'Semana 3'],
    ],

    'documentos' => ['INE', 'CURP', 'RFC', 'NSS', 'Comprobante de domicilio', 'Comprobante de estudios', 'Acta de nacimiento'],

    'faq' => [
        ['q' => '¿Cómo envío mi CV?',                                      'a' => 'Solo por WhatsApp. Elige la vacante, toca "Postularme por WhatsApp" y se abre el chat con tu mensaje listo; adjunta tu CV (PDF, Word o foto legible) en esa misma conversación.'],
        ['q' => '¿Puedo postularme a más de una vacante?',                 'a' => 'Sí. Escríbenos una vez por cada vacante o indícalo en tu mensaje y evaluaremos tu perfil para cada posición.'],
        ['q' => '¿Cuánto tarda el proceso?',                               'a' => 'En promedio 2 a 3 semanas desde la recepción del CV hasta la oferta.'],
        ['q' => '¿Necesito experiencia con equipos Honeywell?',            'a' => 'No es indispensable. Te capacitamos en los equipos que manejamos.'],
        ['q' => '¿Reciben CV sin vacante específica?',                     'a' => 'Sí. Usa el botón de cartera general y te contactamos cuando haya una posición afín.'],
        ['q' => '¿Ofrecen prácticas profesionales o residencias?',         'a' => 'Sí, para estudiantes de 7.º semestre en adelante de carreras técnicas e ingenierías.'],
        ['q' => '¿El trabajo requiere viajar?',                            'a' => 'Algunas posiciones de campo atienden plantas fuera del área metropolitana; se indica en cada vacante.'],
    ],

    'aviso' => 'Mac del Norte es un empleador con igualdad de oportunidades. No solicitamos pagos ni certificados de no embarazo en ninguna etapa del proceso.',

];
