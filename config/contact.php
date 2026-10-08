<?php

/*
|--------------------------------------------------------------------------
| Datos de contacto del sitio (único lugar donde se editan)
|--------------------------------------------------------------------------
|
| Edita SOLO este archivo para cambiar teléfonos, WhatsApp, correos u horario.
| Las vistas los leen con config('contact.…'). No hay pantalla en el admin para esto.
|
| Regla del negocio:
|   - 81 2473 8768  -> SOLO llamadas (es el que se muestra y se marca en "Llamar").
|   - 81 3582 5559  -> SOLO WhatsApp.
|
| Después de editar en producción: php artisan config:clear (o config:cache).
|
*/

return [

    'whatsapp' => [
        // Enlace que abren TODOS los botones de WhatsApp (el acortador wa.link/f28njw apunta al 81 3582 5559).
        'url' => 'https://wa.link/f28njw',

        // Número en formato internacional sin "+" ni espacios (para construir wa.me y mensajes).
        'number' => '528135825559',

        // Cómo se muestra el número de WhatsApp en pantalla.
        'display' => '81 3582 5559',
    ],

    'phone' => [
        // Teléfono principal: lo que se muestra y se marca (tel:) en todos los botones "Llamar".
        'main' => [
            'number'  => '8124738768',     // solo dígitos, tal como se marca
            'display' => '81 2473 8768',
        ],

        // Asesorías técnicas (pie de página y PDF de cotización).
        'advisory' => [
            'number'  => '8124738744',
            'display' => '81 2473 8744',
        ],
    ],

    'email' => [
        'general'         => 'contacto@macdelnorte.com',
        'sales'           => 'ventas1@macdelnorte.com',
        'product_manager' => 'product.manager@macdelnorte.com',
        // Estos dos usan dominio .mx (aviso de privacidad y términos; las páginas legales no se leen de aquí todavía).
        'privacy'         => 'privacidad@macdelnorte.mx',
        'logistics'       => 'logistica@macdelnorte.mx',
    ],

    'hours' => [
        'es' => 'Lunes a Viernes de 8:30am a 6:00pm',
    ],

    // Mensaje prellenado de WhatsApp (opcional, hoy ningún botón lo usa). Solo funciona con wa.me:
    //   'https://wa.me/' . number . '?text=' . rawurlencode(str_replace(':sku', $sku, message))
    'whatsapp_message' => 'Hola, me interesa cotizar el producto :sku',

];
