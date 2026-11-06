<?php

/*
|--------------------------------------------------------------------------
| Solicitudes de los formularios publicos (web_leads)
|--------------------------------------------------------------------------
| Los controladores leen config(), nunca env() (con `php artisan config:cache` env() deja de funcionar
| fuera de los archivos de config).
*/
return [
    /*
    | Correo(s) que reciben el aviso de cada solicitud, separados por comas.
    | Si esta vacio se usa MAIL_ADMIN_ORDER_ADDRESS. Sin ningun destino no se envia correo (la solicitud
    | igual queda guardada y visible en el panel).
    */
    'notify_to' => array_values(array_filter(array_map('trim', explode(',', (string) env('WEB_LEADS_NOTIFY_TO', env('MAIL_ADMIN_ORDER_ADDRESS', '')))))),

    /*
    | Webhook opcional (n8n). Vacio = apagado. Se envia un POST JSON con los datos de la solicitud;
    | si WEB_LEADS_WEBHOOK_TOKEN esta definido se manda como "Authorization: Bearer ...".
    */
    'webhook_url' => env('WEB_LEADS_WEBHOOK_URL'),
    'webhook_token' => env('WEB_LEADS_WEBHOOK_TOKEN'),
    'webhook_timeout' => 10,

    /*
    | Anti-spam: maximo de envios por minuto por IP y segundos minimos entre que se carga la pagina y se envia
    | (un robot rellena y envia al instante).
    */
    'throttle_per_minute' => 10,
    'min_fill_seconds' => 3,
];
