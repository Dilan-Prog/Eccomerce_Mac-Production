<?php

/*
|--------------------------------------------------------------------------
| Sección Brasil (pt-BR)
|--------------------------------------------------------------------------
|
| Datos de contacto que usan las landings /br/* y su cabecera y pie propios.
| Viven aquí, y no en cada vista, para cambiarlos en un solo lugar.
|
| Toda la conversión de esta sección va por WhatsApp; el correo y el teléfono
| se muestran solo como texto de referencia.
|
*/

return [

    // Mismo enlace corto que usa el resto del sitio. Si se abre una linea
    // dedicada para Brasil, cambiar solo aqui.
    'whatsapp' => env('BR_WHATSAPP_URL', 'https://wa.link/f28njw'),

    'email' => env('BR_CONTACT_EMAIL', 'ventas1@macdelnorte.com'),

    'phone' => '+52 81 2473-8768',
    'phone_href' => 'tel:+528124738768',

    'hours' => 'Seg. a sex., 8h30–18h (horário de Monterrey, MX)',

    /*
    | Landings publicadas. Alimenta el carrusel "Você também pode se
    | interessar", la lista del pie y los enlaces cruzados. Al crear una
    | landing nueva: ruta + metodo del controlador + vista + una entrada aqui.
    |
    | La imagen es la portada del catalogo; el carrusel la usa tal cual.
    */
    'landings' => [
        'honeywell-dc1010' => [
            'ruta' => 'br.honeywell-dc1010',
            'marca' => 'Honeywell',
            'nombre' => 'DC1010 — Controlador de Temperatura 1/16 DIN',
            'resumen' => 'O mais compacto da série DC1000: entrada universal e PID em painel 48 × 48 mm.',
            'imagen' => 'https://www.macdelnorte.com/uploads/media_66c113b85c43f.control de temperatura dc1010ct-101000-e_1.webp',
        ],
        'honeywell-dc1040' => [
            'ruta' => 'br.honeywell-dc1040',
            'marca' => 'Honeywell',
            'nombre' => 'DC1040 — Controlador de Temperatura 1/4 DIN',
            'resumen' => 'Entrada universal e PID com auto-sintonia em painel 96 × 96 mm.',
            'imagen' => 'https://www.macdelnorte.com/uploads/media_670f02b4af765.Dc1040.webp',
        ],
        'honeywell-dc1200' => [
            'ruta' => 'br.honeywell-dc1200',
            'marca' => 'Honeywell',
            'nombre' => 'DC1200 — Controlador de Temperatura 1/8 DIN',
            'resumen' => 'Série DC1202 / DC1203 / DC120L: controle PID em painel 48 × 96 mm.',
            'imagen' => 'https://www.macdelnorte.com/uploads/media_67534c0c1ea0a.dc1202.webp',
        ],
        'honeywell-dc2800' => [
            'ruta' => 'br.honeywell-dc2800',
            'marca' => 'Honeywell',
            'nombre' => 'DC2800 — Controlador de Processo 1/4 DIN',
            'resumen' => 'Linha superior: mais saídas, alarmes e comunicação para malhas exigentes.',
            'imagen' => 'https://www.macdelnorte.com/uploads/media_66c117620c8e2.Dc2800.webp',
        ],
        'mcdonnell-miller' => [
            'ruta' => 'br.mcdonnell-miller',
            'marca' => 'McDonnell & Miller',
            'nombre' => '150S — Controle de Nível e Corte por Baixa Água',
            'resumen' => 'Proteção de caldeiras a vapor: corte por baixo nível de água e controle de bomba.',
            'imagen' => 'https://www.macdelnorte.com/uploads/media_691154d65049d.150S-HD-1.webp',
        ],
    ],

];
