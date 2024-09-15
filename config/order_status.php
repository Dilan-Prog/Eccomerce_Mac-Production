<?php

return [

    'order_status_admin' => [
        'pending' => [
            'status' => 'Pendiente', // Pendiente
            'details' => 'Tu orden está actualmente pendiente' // Detalles: Tu orden está actualmente pendiente
        ],
        'processed_and_ready_to_ship' => [
            'status' => 'Procesado y listo para enviar', // Procesado y listo para enviar
            'details' => 'Tu paquete ha sido procesado y pronto estará con nuestro socio de entrega' // Detalles: Tu paquete ha sido procesado y pronto estará con nuestro socio de entrega
        ],
        'dropped_off' => [
            'status' => 'Entregado al transportista', // Entregado al transportista
            'details' => 'Tu paquete ha sido entregado al transportista por el vendedor' // Detalles: Tu paquete ha sido entregado al transportista por el vendedor
        ],
        'shipped' => [
            'status' => 'Enviado', // Enviado
            'details' => 'Tu paquete ha llegado a nuestras instalaciones de logística' // Detalles: Tu paquete ha llegado a nuestras instalaciones de logística
        ],
        'out_for_delivery' => [
            'status' => 'En ruta de entrega', // En ruta de entrega
            'details' => 'Nuestro socio de entrega intentará entregar tu paquete' // Detalles: Nuestro socio de entrega intentará entregar tu paquete
        ],
        'delivered' => [
            'status' => 'Entregado', // Entregado
            'details' => 'Entregado' // Detalles: Entregado
        ],
        'canceled' => [
            'status' => 'Cancelado', // Cancelado
            'details' => 'Cancelado' // Detalles: Cancelado
        ]
    ]

];
