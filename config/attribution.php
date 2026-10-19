<?php

/*
|--------------------------------------------------------------------------
| Atribucion por folio de contacto (WhatsApp / correo / cotizacion)
|--------------------------------------------------------------------------
|
| El sitio registra cada contacto (clic en WhatsApp, correo, llamada, cotizacion) con los
| identificadores de clic de anuncios (gclid/gbraid/wbraid/fbclid) y los UTM de la sesion, y
| agrega un FOLIO corto ("MN" + 5 caracteres) al mensaje de WhatsApp / asunto del correo.
| n8n lee el folio del mensaje que llega al CRM y consulta GET /api/attribution/lookup para
| saber de que anuncio vino ese cliente.
|
| Aqui se centra todo lo configurable. Los middleware y controladores leen config(), nunca env():
| con `php artisan config:cache` los env() dejan de funcionar fuera de los archivos de config.
| Despues de cambiar el .env en produccion: php artisan config:clear (o config:cache).
|
*/

return [

    /*
    | Token del endpoint GET /api/attribution/lookup (Authorization: Bearer <token>).
    | Vacio, o de menos de 32 caracteres = endpoint cerrado (responde 503). Es independiente del token de marketing y del de
    | Aspel. Se rota editando el .env; genera uno largo, p. ej. con `php artisan tinker`:
    |   Str::random(64)
    */
    'api_token' => env('ATTRIBUTION_API_TOKEN'),

    /*
    | Interruptor maestro del folio en el navegador. Apagado (false) el JS no cambia ningun enlace
    | ni agrega folio; el rastreo de eventos sigue funcionando igual.
    */
    'folio_enabled' => (bool) env('ATTRIBUTION_FOLIO_ENABLED', false),

    /*
    | Donde se aplica el folio: '*' (todos los botones) o una lista separada por comas de
    | ubicaciones (placement), p. ej. "flotante,footer_oficina". Sirve para un piloto gradual.
    */
    'folio_placements' => env('ATTRIBUTION_FOLIO_PLACEMENTS', '*'),

    /*
    | Folio invisible (U+2060 + 8 bits con U+200B/U+200C + U+2060) al final del mensaje de WhatsApp.
    | Mantener apagado hasta que el aviso de privacidad cubra el uso de identificadores de clic y
    | del folio.
    */
    'invisible_folio' => (bool) env('ATTRIBUTION_INVISIBLE_FOLIO', false),

    /*
    | Verificacion de Origin/Referer en POST /track-conversion (middleware tracking.origin). Solo se
    | guardan eventos cuyo Origin (o Referer) sea el host de APP_URL (con o sin www) o uno de
    | allowed_hosts; lo demas recibe 204 sin guardar nada. Pon origin_check en false para apagarlo sin
    | redeploy si algo falla (env ATTRIBUTION_ORIGIN_CHECK=false y config:clear).
    | allowed_hosts: hosts extra separados por comas (ATTRIBUTION_ALLOWED_HOSTS), comparacion exacta.
    */
    'origin_check' => (bool) env('ATTRIBUTION_ORIGIN_CHECK', true),
    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', strtolower((string) env('ATTRIBUTION_ALLOWED_HOSTS', '')))))),

    /*
    | Formato del folio de contacto. El alfabeto no tiene vocales ni caracteres ambiguos (0/O, 1/I/L)
    | para evitar palabras ofensivas y errores al dictarlo. Debe coincidir con el JS y con la
    | validacion de TrackConversionController (MN + 5 caracteres de este alfabeto).
    */
    'folio' => [
        'prefix' => 'MN',
        'length' => 5,
        'alphabet' => '23456789BCDFGHJKMNPQRSTVWXYZ',
    ],

    /*
    | Consulta por hora (llamadas, que no pueden llevar folio): maximo de eventos devueltos y
    | ventana maxima (minutos) entre `from` y `to`.
    */
    'lookup_max' => 20,
    'time_window_minutes' => 15,

    /*
    | Plantillas del mensaje de WhatsApp y del asunto del correo, por idioma (es = Mexico,
    | pt = paginas /br/*). Marcadores: {producto} y {folio}.
    */
    'templates' => [
        'es' => [
            'whatsapp' => [
                'product' => 'Hola, me interesa: {producto}. Folio: {folio}',
                'generic' => 'Hola, quiero más información. Folio: {folio}',
            ],
            'mail_subject' => 'Cotización web - Folio {folio}',
        ],
        'pt' => [
            'whatsapp' => [
                'product' => 'Olá, tenho interesse em: {produto}. Código: {folio}',
                'generic' => 'Olá, quero mais informações. Código: {folio}',
            ],
            'mail_subject' => 'Cotação web - Código {folio}',
        ],
    ],

    /*
    | Modo prueba (Laboratorio de atribucion del admin). Una cookie cifrada y HttpOnly, emitida solo
    | desde el panel por un admin con el modulo 'site', hace que el sitio publico de ESE navegador
    | se comporte como folio_enabled=true y placements='*', sin cargar GTM/GA4/Ads/Pixel/Sheets, y
    | que los eventos se guarden con is_test=1. whatsapp_number: numero (solo digitos, 10-15) que
    | abren los botones en modo prueba (cuenta de prueba de Kommo); vacio = wa.me/?text= sin numero.
    */
    'test_mode' => [
        'cookie' => 'mdn_attr_test',
        'ttl_minutes' => 120,
        'whatsapp_number' => env('ATTRIBUTION_TEST_WHATSAPP_NUMBER'),
        // Respaldo del .env; el valor guardado desde el panel (tabla attribution_lab_settings) manda sobre este.

        // Solo local/testing: permite que el boton "probar n8n" apunte a una IP privada/loopback (n8n en
        // la red local). En cualquier otro entorno la guardia SSRF siempre exige IPs publicas.
        'allow_private_n8n' => (bool) env('ATTRIBUTION_LAB_ALLOW_PRIVATE_N8N', false),
    ],

];
