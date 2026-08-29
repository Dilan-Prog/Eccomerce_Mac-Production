<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

/**
 * Landing con pestañas del módulo de Email Marketing: Plantillas / Listas /
 * Campañas / Secuencias. Es la única entrada del sidebar para todo el
 * módulo, y sustituye al link suelto "Plantillas de correo".
 *
 * No tiene lógica propia: cada pestaña monta su AU.AdminTable apuntando al
 * table-data de su propio controlador (EmailTemplateController,
 * EmailContactListController, EmailCampaignController,
 * EmailSequenceController). Ninguna URL vieja se rompe — las rutas
 * admin.email-templates.* siguen existiendo tal cual y funcionando.
 */
class EmailMarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view');
    }

    public function index()
    {
        return view('admin-ui.email-marketing.index');
    }
}
