<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AspelClient;
use App\Models\AspelSale;
use App\Models\AspelSaleItem;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use App\Support\MarketingOfferBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Datos de clientes/compras para el flujo de n8n de email marketing (Parte
 * 3 + 3b del plan de automatización). Protegido por `marketing.token`
 * (ver App\Http\Middleware\MarketingApiTokenMiddleware) — sistema de tokens
 * completamente aislado del de Aspel (aspel.token); ninguno de los dos
 * autentica las rutas del otro.
 */
class MarketingDataController extends Controller
{
    public function __construct(private MarketingOfferBuilder $offerBuilder)
    {
    }

    /**
     * GET /api/marketing/customers
     *
     * Solo clientes con al menos una orden válida — mismo criterio que ya
     * usa el dashboard admin para ingresos (AdminController::dashboard):
     * order_status != 'canceled' y payment_status = 1. Evita ofertar en
     * base a compras canceladas/no pagadas. Paginado (50).
     */
    public function customers(Request $request)
    {
        $users = User::query()
            ->whereHas('orders', function ($query) {
                $query->where('order_status', '!=', 'canceled')->where('payment_status', 1);
            })
            ->with(['orders' => function ($query) {
                $query->where('order_status', '!=', 'canceled')
                    ->where('payment_status', 1)
                    ->with(['orderProducts.product.category:id,name'])
                    ->orderByDesc('created_at');
            }])
            ->orderBy('id')
            ->paginate(50);

        $data = $users->getCollection()->map(function (User $user) {
            $totalSpent = 0.0;
            $lastPurchaseAt = null;
            $products = [];

            foreach ($user->orders as $order) {
                $totalSpent += (float) $order->sub_total;

                if (!$lastPurchaseAt || $order->created_at->greaterThan($lastPurchaseAt)) {
                    $lastPurchaseAt = $order->created_at;
                }

                foreach ($order->orderProducts as $orderProduct) {
                    $products[] = [
                        'product_id' => (int) $orderProduct->product_id,
                        'product_name' => $orderProduct->product_name,
                        'category' => optional($orderProduct->product)->category->name ?? null,
                        'qty' => (int) $orderProduct->qty,
                        'purchased_at' => optional($order->created_at)->toDateTimeString(),
                    ];
                }
            }

            return [
                'id' => $user->id,
                'name' => trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')),
                'email' => $user->email,
                'phone' => $user->phone,
                'company' => $user->company,
                'purchase_summary' => [
                    'total_spent' => round($totalSpent, 2),
                    'last_purchase_at' => optional($lastPurchaseAt)->toDateTimeString(),
                    'products' => $products,
                ],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/marketing/email/{userId}[?template_id=N]
     *
     * Arma la oferta personalizada (MarketingOfferBuilder) y regresa el
     * HTML ya renderizado — n8n lo toma tal cual y lo pasa a la API
     * transaccional de Brevo (no al editor de campañas).
     *
     * El contenido viene de una plantilla editable desde el admin (ver
     * App\Models\EmailTemplate + App\Http\Controllers\Backend\EmailTemplateController).
     * Selección de plantilla, en orden:
     * 1. Si se manda `template_id` en la query string, se usa esa (si existe
     *    y está activa) — permite forzar una plantilla específica a mano,
     *    ej. para probar una plantilla nueva antes de dejarla en automático.
     * 2. Si no se manda (o no es válida), se busca una específica para la
     *    categoría dominante del cliente.
     * 3. Si no hay, se cae a la plantilla general (category_id null).
     * 4. Si todavía no existe ninguna plantilla en la base de datos
     *    (instalación nueva / antes de sembrar la default) se cae por
     *    completo a la vista Blade fija de siempre para que esto nunca se
     *    rompa por falta de configuración.
     */
    public function email(Request $request, string $userId)
    {
        $user = User::findOrFail($userId);
        $offer = $this->offerBuilder->build($user);

        $template = null;
        if ($request->filled('template_id')) {
            $template = EmailTemplate::where('id', $request->query('template_id'))->where('status', true)->first();
        }
        if (!$template && $offer['category']) {
            $template = EmailTemplate::where('category_id', $offer['category']->id)->where('status', true)->first();
        }
        if (!$template) {
            $template = EmailTemplate::whereNull('category_id')->where('status', true)->first();
        }

        if ($template) {
            $placeholderData = $this->offerBuilder->placeholderData($user, $offer);

            // Si la plantilla se armó con el editor visual por bloques
            // (blocks_json no vacío), el body efectivo a sustituir con los
            // marcadores de texto es el que genera BlockEmailRenderer a
            // partir de los bloques — NO el campo `body` guardado (que solo
            // es una copia/"caché" ya renderizada, ver EmailTemplateController).
            // Se clona el modelo y se sobreescribe `body` en memoria para no
            // duplicar la lógica de sustitución de marcadores que ya vive en
            // EmailTemplateRenderer.
            $effectiveTemplate = $template;
            if (!empty($template->blocks_json['blocks'] ?? [])) {
                $effectiveTemplate = $template->replicate();
                $effectiveTemplate->body = app(BlockEmailRenderer::class)->render($template->blocks_json, $placeholderData);
            }

            $rendered = app(EmailTemplateRenderer::class)->render($effectiveTemplate, $placeholderData);
            $html = $rendered['html'];
            $subject = $rendered['subject'];
        } else {
            $subject = $offer['category']
                ? 'Ofertas en ' . $offer['category']->name . ' para ti — Mac Del Norte'
                : 'Ofertas especiales para ti — Mac Del Norte';

            $html = view('emails.marketing-offer', [
                'user' => $user,
                'category' => $offer['category'],
                'products' => $offer['products'],
                'coupon' => $offer['coupon'],
            ])->render();
        }

        return response()->json([
            'html' => $html,
            'subject' => $subject,
            'recipient_email' => $user->email,
        ]);
    }

    /**
     * GET /api/marketing/aspel-customers
     *
     * Universo de clientes SEPARADO del de customers() de arriba — a
     * propósito: el ecommerce y Aspel se tratan como canales
     * independientes (la mayoría de los clientes reales de la empresa
     * compran por facturación tradicional, no por el sitio web). Aquí la
     * fuente es puramente Aspel: AspelClient con al menos una factura real
     * (aspel_sales, cruzando por clave = cve_clpv) que no esté cancelada —
     * no requiere que el cliente tenga cuenta en el sitio
     * (aspel_clients.user_id puede ser null). Devuelve TODOS, incluso sin
     * email (CLIE01.EMAILPRED viene vacío/null en varios registros) — la
     * decisión de separarlos por si tienen o no correo se deja a quien
     * consuma este endpoint (n8n), no se filtra aquí, para no ocultar ese
     * universo de clientes sin correo si se necesita para otra cosa (ej.
     * limpieza de datos, seguimiento por otro canal). Paginado (50 por
     * defecto) — se puede pedir un tamaño de página distinto con
     * ?per_page=N (tope 1000, para no armar una respuesta enorme por
     * accidente) si se prefiere traer todo de una sola llamada en vez de
     * iterar página por página desde n8n.
     */
    public function aspelCustomers(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = $perPage > 0 ? min($perPage, 1000) : 50;

        $clients = AspelClient::query()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('aspel_sales')
                    ->whereColumn('aspel_sales.cve_clpv', 'aspel_clients.clave')
                    ->whereNull('aspel_sales.fecha_cancela');
            })
            ->orderBy('id')
            ->paginate($perPage);

        $data = $clients->getCollection()->map(function (AspelClient $client) {
            $sales = AspelSale::where('cve_clpv', $client->clave)
                ->whereNull('fecha_cancela')
                ->orderByDesc('fecha_doc')
                ->get();

            $items = AspelSaleItem::whereIn('cve_doc', $sales->pluck('cve_doc'))
                ->leftJoin('aspel_products', 'aspel_products.cve_art', '=', 'aspel_sale_items.cve_art')
                ->leftJoin('products', 'products.sku', '=', 'aspel_products.cve_art')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->select(
                    'aspel_sale_items.cve_doc',
                    'aspel_sale_items.cve_art',
                    'aspel_sale_items.descr_art',
                    'aspel_sale_items.cant',
                    'categories.name as category_name'
                )
                ->get();

            $products = $items->map(function ($item) use ($sales) {
                $sale = $sales->firstWhere('cve_doc', $item->cve_doc);

                return [
                    'product_sku' => $item->cve_art,
                    'product_name' => $item->descr_art,
                    'category' => $item->category_name,
                    'qty' => (float) $item->cant,
                    'purchased_at' => optional($sale)->fecha_doc,
                ];
            })->values();

            return [
                'clave' => $client->clave,
                'name' => $client->nombre,
                'email' => $client->email,
                'phone' => $client->telefono,
                'company' => $client->nombre_comercial,
                'purchase_summary' => [
                    'total_spent' => round((float) $sales->sum('importe'), 2),
                    'last_purchase_at' => optional($sales->first())->fecha_doc,
                    'products' => $products,
                ],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'current_page' => $clients->currentPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'last_page' => $clients->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/marketing/aspel-email/{clave}[?template_id=N]
     *
     * Equivalente a email() de arriba pero para clientes fuente Aspel — el
     * cliente se identifica por su clave real de Aspel
     * (aspel_clients.clave), no por un id de la tabla users. Misma
     * selección de plantilla (template_id -> categoría dominante -> general
     * -> vista Blade de respaldo) y misma categoría dominante calculada por
     * facturación real (FACTF01/PAR_FACTF01), solo que sin pasar por un
     * User — ver MarketingOfferBuilder::buildForAspelClient().
     */
    public function aspelEmail(Request $request, string $clave)
    {
        $aspelClient = AspelClient::where('clave', $clave)->firstOrFail();
        $offer = $this->offerBuilder->buildForAspelClient($aspelClient);

        $template = null;
        if ($request->filled('template_id')) {
            $template = EmailTemplate::where('id', $request->query('template_id'))->where('status', true)->first();
        }
        if (!$template && $offer['category']) {
            $template = EmailTemplate::where('category_id', $offer['category']->id)->where('status', true)->first();
        }
        if (!$template) {
            $template = EmailTemplate::whereNull('category_id')->where('status', true)->first();
        }

        if ($template) {
            $placeholderData = $this->offerBuilder->placeholderDataForAspelClient($aspelClient, $offer);
            // {{contact.*}} también disponible aquí (ver el cambio de
            // EmailTemplateRenderer que ya acepta un arreglo plano ademas de
            // un User real) — útil para plantillas que se quieran reusar
            // entre clientes de ecommerce y clientes de Aspel.
            $placeholderData['contact'] = [
                'name' => trim($aspelClient->nombre ?? ''),
                'email' => (string) ($aspelClient->email ?? ''),
                'company' => (string) ($aspelClient->nombre_comercial ?? ''),
            ];

            $effectiveTemplate = $template;
            if (!empty($template->blocks_json['blocks'] ?? [])) {
                $effectiveTemplate = $template->replicate();
                $effectiveTemplate->body = app(BlockEmailRenderer::class)->render($template->blocks_json, $placeholderData);
            }

            $rendered = app(EmailTemplateRenderer::class)->render($effectiveTemplate, $placeholderData);
            $html = $rendered['html'];
            $subject = $rendered['subject'];
        } else {
            $subject = $offer['category']
                ? 'Ofertas en ' . $offer['category']->name . ' para ti — Mac Del Norte'
                : 'Ofertas especiales para ti — Mac Del Norte';

            $html = view('emails.marketing-offer', [
                'user' => (object) ['name' => $aspelClient->nombre],
                'category' => $offer['category'],
                'products' => $offer['products'],
                'coupon' => $offer['coupon'],
            ])->render();
        }

        return response()->json([
            'html' => $html,
            'subject' => $subject,
            'recipient_email' => $aspelClient->email,
        ]);
    }

    /**
     * GET /api/marketing/templates/{id}
     *
     * Contenido CRUDO de una plantilla (asunto + HTML, con los marcadores
     * {{...}} todavía SIN sustituir) — para cuando n8n quiere decidir el
     * relleno de variables por su cuenta en vez de pedir el correo ya
     * armado por cliente (ver email()/aspelEmail() de arriba, que sí
     * sustituyen contra un cliente real).
     *
     * Ojo con plantillas armadas con el editor visual por bloques
     * (builder_mode = 'blocks'): su columna `body` es solo una copia de
     * respaldo renderizada con datos FICTICIOS (ver
     * EmailTemplateController::validateData()) — los bloques de
     * productos/cupón ya vienen con tarjetas de ejemplo "quemadas", no con
     * un marcador {{productos}} sustituible. Para esas, sigue siendo mejor
     * usar email()/aspelEmail() con ?template_id= para que el sistema arme
     * el HTML final con datos reales por cliente. Este endpoint es más útil
     * para plantillas en modo "código" (HTML crudo), donde `body` es
     * exactamente lo que se escribió en el editor, marcadores incluidos —
     * por eso se marca uses_dummy_preview_data para que quien consuma esto
     * sepa a qué atenerse.
     */
    public function template(Request $request, string $id)
    {
        $template = EmailTemplate::where('status', true)->findOrFail($id);

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'type' => $template->type,
            'category_id' => $template->category_id,
            'builder_mode' => $template->builder_mode,
            'subject' => $template->subject,
            'body' => $template->body,
            'uses_dummy_preview_data' => $template->builder_mode === 'blocks',
        ]);
    }
}
