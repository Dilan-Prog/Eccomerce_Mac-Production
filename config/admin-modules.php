<?php

/**
 * Single authoritative list of admin-panel module keys, derived from the
 * nav groups in resources/views/admin-ui/layouts/sidebar.blade.php. Used by:
 * - The Roles module's permission checklist (each key = one checkbox).
 * - ModuleAccessMiddleware ("can-access-module:{key}"), which validates a
 *   restricted admin's role against these same keys.
 * Keep this in sync with the sidebar — it's the one place both consult.
 */

return [
    'dashboard' => 'Escritorio',
    'orders' => 'Panel De Control (Órdenes)',
    'transactions' => 'Transacciones',
    'cotizaciones' => 'Cotizaciones',
    'categories' => 'Administrar Categorías',
    'products' => 'Administrar Productos',
    'aspel' => 'Aspel Productos',
    'aspel-integracion' => 'Panel Administrativo',
    'marketing-integracion' => 'Marketing (Tokens API n8n)',
    'ecommerce' => 'Ecommerce (Venta Flash, Cupones, Envío, Pagos)',
    'ads' => 'Publicidad',
    'clientes' => 'Clientes',
    'staff' => 'Administradores y Roles',
    'site' => 'Administrar Sitio Web',
    'settings' => 'Configuración General',
];
