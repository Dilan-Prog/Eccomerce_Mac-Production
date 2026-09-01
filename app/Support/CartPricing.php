<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariantCombinations;

/**
 * Resuelve un producto/combinación en vivo, su precio efectivo actual y su
 * stock efectivo actual para una línea de \Cart::content() — la única fuente
 * de verdad sobre "esta fila del carrito es un producto plano o una
 * combinación con prefijo comb_, y cuánto cuesta/cuánto queda en existencia
 * en este momento" — usada por el cálculo de totales del carrito y por el
 * checkout, para que un cambio de precio/stock posterior al add-to-cart se
 * refleje siempre de forma consistente en todos lados, en vez de depender
 * del valor cacheado en el item del carrito al momento de agregarlo.
 */
class CartPricing
{
    /**
     * @param  \Gloudemans\Shoppingcart\CartItem|object  $cartItem  una fila de Cart::content()
     * @param  bool  $lockForUpdate  usar SELECT ... FOR UPDATE al buscar el
     *         producto/combinación — necesario en checkout (PaymentController::storeOrder())
     *         para evitar que dos pagos concurrentes lean el mismo stock antes
     *         de que uno de los dos termine de descontarlo. false (default)
     *         para los usos de solo-lectura (totales del carrito).
     * @return array{product: Product|null, combination: ProductVariantCombinations|null, price: float, stock: int, available: bool}
     */
    public static function resolve($cartItem, bool $lockForUpdate = false): array
    {
        $empty = [
            'product' => null,
            'combination' => null,
            'price' => 0.0,
            'stock' => 0,
            'available' => false,
        ];

        // Misma convención de prefijo que CartController::addToCart() /
        // updateProductQty() y helpers.php::getCouponCategorySubTotal().
        if (strpos((string) $cartItem->id, 'comb_') === 0) {
            $combinationId = $cartItem->options['combination_id'] ?? substr((string) $cartItem->id, 5);

            $query = ProductVariantCombinations::with('product')->where('id', $combinationId);
            if ($lockForUpdate) {
                $query->lockForUpdate();
            }
            $combination = $query->first();

            if (!$combination) {
                return $empty;
            }

            $price = $combination->effectivePrice();

            return [
                'product' => $combination->product,
                'combination' => $combination,
                'price' => $price,
                'stock' => $combination->effectiveStock(),
                'available' => $price > 0,
            ];
        }

        $query = Product::where('id', $cartItem->id);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $product = $query->first();

        if (!$product) {
            return $empty;
        }

        $hasPrice = $product->hasEffectivePrice();

        return [
            'product' => $product,
            'combination' => null,
            'price' => $hasPrice ? $product->effectivePrice() : 0.0,
            'stock' => $product->effectiveStock(),
            'available' => $hasPrice,
        ];
    }
}
