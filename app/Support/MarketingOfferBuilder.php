<?php

namespace App\Support;

use App\Models\AspelClient;
use App\Models\AspelSaleItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Arma la oferta personalizada que consume MarketingDataController::email()
 * (Parte 3b del plan de automatización n8n/email marketing). Vive en el
 * ecommerce, no en n8n — n8n solo pide el HTML ya armado y lo manda por
 * Brevo.
 */
class MarketingOfferBuilder
{
    /** Máximo de productos recomendados en la plantilla. */
    public const MAX_RECOMMENDED_PRODUCTS = 4;

    /**
     * @return array{category: ?Category, products: Collection<int, Product>, coupon: ?Coupon}
     */
    public function build(User $user): array
    {
        $categoryId = $this->resolveDominantCategory($user);

        if ($categoryId) {
            return [
                'category' => Category::find($categoryId),
                'products' => $this->recommendedProducts($categoryId, $user),
                'coupon' => $this->couponForCategory($categoryId),
            ];
        }

        return [
            'category' => null,
            'products' => $this->fallbackProducts(),
            'coupon' => null,
        ];
    }

    /**
     * Agrupa las compras del cliente (solo órdenes válidas — mismo criterio
     * que el dashboard admin para ingresos: order_status != 'canceled' y
     * payment_status = 1) por category_id del producto. Criterio principal:
     * unidades compradas; desempate: gasto total.
     *
     * Fallback (Parte 1b del plan, ya implementado): si el cliente no tiene
     * historial en orders/order_products del ecommerce (o ninguna de sus
     * compras mapeó a una categoría), se intenta con su historial real de
     * ventas en Aspel — aspel_sales/aspel_sale_items, sincronizadas vía
     * POST /api/aspel/ventas — para clientes que compraron solo en Aspel y
     * nunca en el ecommerce. Solo aplica si el User está vinculado a un
     * AspelClient (aspel_clients.user_id -> users.id, el mismo enlace que
     * usa AdminCotizacionController::resolveAspelClient()). Si no hay
     * vínculo o tampoco hay ventas en Aspel, cae al fallback genérico de
     * build().
     */
    public function resolveDominantCategory(User $user): ?int
    {
        $rows = OrderProduct::query()
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.order_status', '!=', 'canceled')
            ->where('orders.payment_status', 1)
            ->select('order_products.product_id', 'order_products.qty', 'order_products.unit_price')
            ->get();

        $totals = [];

        if ($rows->isNotEmpty()) {
            $categoryByProduct = Product::whereIn('id', $rows->pluck('product_id')->unique())
                ->pluck('category_id', 'id');

            foreach ($rows as $row) {
                $categoryId = $categoryByProduct[$row->product_id] ?? null;
                if (!$categoryId) {
                    continue;
                }
                $totals[$categoryId]['qty'] = ($totals[$categoryId]['qty'] ?? 0) + (int) $row->qty;
                $totals[$categoryId]['spent'] = ($totals[$categoryId]['spent'] ?? 0) + ((float) $row->unit_price * (int) $row->qty);
            }
        }

        if (empty($totals)) {
            $aspelClient = AspelClient::where('user_id', $user->id)->first();

            if ($aspelClient) {
                $aspelRows = AspelSaleItem::query()
                    ->join('aspel_sales', 'aspel_sales.cve_doc', '=', 'aspel_sale_items.cve_doc')
                    ->join('aspel_products', 'aspel_products.cve_art', '=', 'aspel_sale_items.cve_art')
                    ->join('products', 'products.sku', '=', 'aspel_products.cve_art')
                    ->where('aspel_sales.cve_clpv', $aspelClient->clave)
                    ->whereNull('aspel_sales.fecha_cancela')
                    ->select('products.category_id', 'aspel_sale_items.cant', 'aspel_sale_items.tot_partida')
                    ->get();

                foreach ($aspelRows as $row) {
                    $categoryId = $row->category_id;
                    if (!$categoryId) {
                        continue;
                    }
                    $totals[$categoryId]['qty'] = ($totals[$categoryId]['qty'] ?? 0) + (float) $row->cant;
                    $totals[$categoryId]['spent'] = ($totals[$categoryId]['spent'] ?? 0) + (float) $row->tot_partida;
                }
            }
        }

        if (empty($totals)) {
            return null;
        }

        uasort($totals, fn ($a, $b) => ($b['qty'] <=> $a['qty']) ?: ($b['spent'] <=> $a['spent']));

        return (int) array_key_first($totals);
    }

    /**
     * Hasta MAX_RECOMMENDED_PRODUCTS productos activos y aprobados de la
     * categoría dominante, excluyendo lo que el cliente ya compró.
     */
    protected function recommendedProducts(int $categoryId, User $user): Collection
    {
        $purchasedProductIds = OrderProduct::query()
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.user_id', $user->id)
            ->pluck('order_products.product_id');

        return Product::where('category_id', $categoryId)
            ->where('status', 1)
            ->where('is_approved', 1)
            ->whereNotIn('id', $purchasedProductIds)
            ->orderByDesc('id')
            ->limit(self::MAX_RECOMMENDED_PRODUCTS)
            ->get();
    }

    /** Cliente sin historial (ni en el ecommerce, ver TODO de arriba) → más vendidos del catálogo completo, sin cupón. */
    protected function fallbackProducts(): Collection
    {
        $topProductIds = OrderProduct::query()
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.order_status', '!=', 'canceled')
            ->where('orders.payment_status', 1)
            ->selectRaw('order_products.product_id, SUM(order_products.qty) as total_qty')
            ->groupBy('order_products.product_id')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->pluck('order_products.product_id')
            ->all();

        if (empty($topProductIds)) {
            return Product::where('status', 1)
                ->where('is_approved', 1)
                ->orderByDesc('id')
                ->limit(self::MAX_RECOMMENDED_PRODUCTS)
                ->get();
        }

        return Product::whereIn('id', $topProductIds)
            ->where('status', 1)
            ->where('is_approved', 1)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $topProductIds))
            ->take(self::MAX_RECOMMENDED_PRODUCTS)
            ->values();
    }

    /**
     * Cupón activo restringido a esta categoría (Parte 5 del plan —
     * coupons.category_id). Protegido: si esa migración todavía no ha
     * corrido cuando esto se despliegue (trabajo en paralelo, archivo
     * aparte), simplemente no ofrece cupón en vez de tronar.
     */
    protected function couponForCategory(int $categoryId): ?Coupon
    {
        try {
            if (!Schema::hasColumn('coupons', 'category_id')) {
                return null;
            }

            return Coupon::where('category_id', $categoryId)->where('status', 1)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Mapa de placeholders de texto plano ({{clave}}) consumido por
     * App\Support\EmailTemplateRenderer para las plantillas de correo
     * editables desde el admin (ver EmailTemplateController). Centraliza
     * aquí la resolución de cada valor para que tanto la plantilla de base
     * de datos como (en el futuro) cualquier otro consumidor usen
     * exactamente los mismos datos que hoy arma build().
     *
     * @param  array{category: ?Category, products: Collection<int, Product>, coupon: ?Coupon}  $offer
     * @return array{nombre_cliente: string, categoria: string, productos: string, cupon_codigo: string, cupon_descuento: string, cupon_bloque: string}
     */
    public function placeholderData(User $user, array $offer): array
    {
        return [
            'nombre_cliente' => trim($user->name ?? ''),
            'categoria' => $offer['category']->name ?? '',
            'productos' => $this->renderProductsBlock($offer['products']),
            'cupon_codigo' => $offer['coupon']->cod ?? '',
            'cupon_descuento' => $this->couponDiscountText($offer['coupon']),
            'cupon_bloque' => $this->renderCouponBlock($offer['coupon']),
        ];
    }

    /**
     * Bloque HTML con las tarjetas de los productos recomendados — mismo
     * markup (tablas + estilos en línea, para clientes de correo) que antes
     * vivía fijo en resources/views/emails/marketing-offer.blade.php.
     * Reutilizado tanto por el fallback a esa vista Blade como por
     * {{productos}} en las plantillas de base de datos.
     *
     * @param  Collection<int, Product>  $products
     */
    public function renderProductsBlock(Collection $products): string
    {
        if ($products->isEmpty()) {
            return '<tr><td style="padding:0 20px 16px;"><p style="margin:0; font-size:14px; color:#777777;">Pronto tendremos nuevas recomendaciones para ti.</p></td></tr>';
        }

        $html = '';
        foreach ($products as $product) {
            $image = $product->thumb_image
                ? '<img src="' . e(asset($product->thumb_image)) . '" alt="' . e($product->name) . '" width="90" style="display:block; width:90px; height:auto; border:0;">'
                : '';

            $html .= '<tr>
                <td style="padding:0 20px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:6px;">
                        <tr>
                            <td width="110" style="padding:12px; vertical-align:top;">' . $image . '</td>
                            <td style="padding:12px 12px 12px 0; vertical-align:top;">
                                <p style="margin:0 0 6px; font-size:15px; color:#222222; font-weight:bold;">' . e($product->name) . '</p>
                                <p style="margin:0 0 10px; font-size:16px; color:#00468c; font-weight:bold;">$' . formatCurrency($product->effectivePrice()) . ' MXN</p>
                                <a href="' . e(route('product-detail', $product->slug)) . '" style="display:inline-block; padding:8px 14px; background-color:#00468c; color:#ffffff; text-decoration:none; font-size:13px; border-radius:4px;">Ver producto</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';
        }

        return $html;
    }

    /**
     * Sección de cupón completa, ya armada condicionalmente — cadena vacía
     * si no hay cupón, para que {{cupon_bloque}} se pueda usar sin que el
     * admin tenga que armar el condicional él mismo.
     */
    public function renderCouponBlock(?Coupon $coupon): string
    {
        if (!$coupon) {
            return '';
        }

        return '<tr>
            <td style="padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#00468c; border-radius:6px;">
                    <tr>
                        <td style="padding:16px; text-align:center; color:#ffffff;">
                            <p style="margin:0 0 6px; font-size:14px;">Usa el código</p>
                            <p style="margin:0 0 6px; font-size:24px; font-weight:bold; letter-spacing:1px;">' . e($coupon->cod) . '</p>
                            <p style="margin:0; font-size:14px;">' . e($this->couponDiscountText($coupon)) . ' de descuento en esta categoría</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>';
    }

    /** Texto corto del descuento del cupón (ej. "10%" o "$100 MXN"), vacío si no hay cupón. */
    public function couponDiscountText(?Coupon $coupon): string
    {
        if (!$coupon) {
            return '';
        }

        return $coupon->discount_type === 'percent'
            ? ((int) $coupon->discount) . '%'
            : '$' . formatCurrency($coupon->discount) . ' MXN';
    }
}
