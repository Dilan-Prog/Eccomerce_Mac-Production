<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use App\Support\MarketingOfferBuilder;
use Illuminate\Http\Request;

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
}
