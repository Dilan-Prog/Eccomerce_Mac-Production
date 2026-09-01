<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Adverisement;
use App\Models\Coupon;
use App\Models\Brand;
use App\Models\Product;
use App\Support\CartPricing;
use Illuminate\Http\Request;
use Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
class CartController extends Controller
{

    public function cartDetails(){
        \Log::info('Contenido del carrito:', \Cart::content()->toArray());
        \Log::info('Sesión completa:', session()->all());
        $cartItems = Cart::content();
        if(count($cartItems) === 0){
            Session::forget('coupon');
            return redirect()->route('index');
        }

        return view('frontend.pages.cart-details', compact('cartItems'));

    }

    public function addToCart(Request $request)
    {
        // Capa 3 de defensa: validación en controlador además del middleware
        if (!auth()->check()) {
            return response()->json([
                'authenticated' => false,
                'status'        => 'error',
                'message'       => 'Debes iniciar sesión para agregar productos al carrito.',
                'redirect'      => route('login'),
            ], 401);
        }

        // Validación de entrada (Bug 1): qty inválido/negativo no debe pasar
        // silenciosamente las comparaciones de stock más abajo. Se usa
        // Validator::make() en vez de $request->validate() a propósito: este
        // último lanza ValidationException -> respuesta 422, pero el
        // manejador global de error AJAX en scripts.blade.php solo tiene
        // lógica especial para 401, así que un 422 aquí fallaría en
        // silencio. Se devuelve el mismo shape que el resto de rechazos de
        // este método.
        $validator = \Validator::make($request->all(), [
            'qty' => 'required|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
            'combination_id' => 'nullable|exists:product_variants_combinations,id',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        // Si viene combination_id, busca la combinación, si no, el producto base
        if ($request->filled('combination_id')) {
            $combination = \App\Models\ProductVariantCombinations::findOrFail($request->combination_id);
            $product = \App\Models\Product::findOrFail($combination->product_id);

            // Bug 2: no permitir agregar al carrito una combinación o
            // producto desactivado. `status` es booleano/int con 1 =
            // activo (mismo criterio que Product::where('status', 1) en
            // AdminCotizacionController::productsSearch()).
            if (!$combination->status || !$product->status) {
                return response(['status' => 'error', 'message' => 'Producto no disponible']);
            }

            // Validar stock de la combinación
            if ($combination->qty === 0) {
                return response(['status' => 'error', 'message' => 'Producto Agotado']);
            } elseif ($combination->qty < $request->qty) {
                return response(['status' => 'error', 'message' => 'Cantidad agotada']);
            }

            // Precio con descuento si aplica
            $combinationPrice = $combination->offert_price ?? $combination->price;

            $cartData = [];
            $cartData['id'] = 'comb_' . $combination->id; // Prefijo para diferenciar combinaciones
            $cartData['name'] = $product->name . ' ' . $combination->name;
            $cartData['qty'] = $request->qty;
            $cartData['price'] = $combinationPrice;
            $cartData['weight'] = 10;
            $cartData['options']['sku'] = $combination->sku;
            $cartData['options']['productModel'] = $product->productModel;
            $cartData['options']['image'] = $product->thumb_image_carrusel ?: $product->thumb_image;
            $cartData['options']['slug'] = $product->slug;
            $cartData['options']['brand_name'] = $request->brand_name;
            $cartData['options']['combination_id'] = $combination->id;

            \Cart::add($cartData);

            return response(['status' => 'success', 'message' => 'Agregado al carrito con éxito']);
        } else {
            // Producto base
            $product = Product::findOrFail($request->product_id);

            // Bug 2: no permitir agregar al carrito un producto desactivado.
            if (!$product->status) {
                return response(['status' => 'error', 'message' => 'Producto no disponible']);
            }

            // Validar stock del producto base
            $stockQty = $product->qty_personalizated == 0 ? $product->qty_aspel : $product->qty;
            if ($stockQty === 0) {
                return response(['status' => 'error', 'message' => 'Producto Agotado']);
            } elseif ($stockQty < $request->qty) {
                return response(['status' => 'error', 'message' => 'Cantidad agotada']);
            }
            // Determinar precio base usando la lógica de price_personalizated
            $basePrice = $product->price_personalizated == 1 
                ? $product->price 
                : ($product->aspel_price ?? $product->price);
            
            // Determinar precio de oferta usando la lógica de price_offert_personalizated
            $offerPrice = $product->price_offert_personalizated == 1 
                ? $product->offert_price 
                : ($product->aspel_offert_price ?? $product->offert_price);

            // Validar que tengamos un precio válido
            if (empty($basePrice) && empty($offerPrice)) {
                return response(['status' => 'error', 'message' => 'El producto no tiene un precio válido']);
            }

            // Precio con descuento si aplica
            $productPrice = checkDiscount($product) ? $offerPrice : $basePrice;

            $cartData = [];
            $cartData['id'] = $product->id;
            $cartData['name'] = $product->name;
            $cartData['qty'] = $request->qty;
            $cartData['price'] = $productPrice;
            $cartData['weight'] = 10;
            $cartData['options']['sku'] = $product->sku;
            $cartData['options']['productModel'] = $product->productModel;
            $cartData['options']['image'] = $product->thumb_image_carrusel ?: $product->thumb_image;
            $cartData['options']['slug'] = $product->slug;
            $cartData['options']['brand_name'] = $request->brand_name;

            \Cart::add($cartData);

            return response(['status' => 'success', 'message' => 'Agregado al carrito con éxito']);
        }
    }

    // CONTROLADOR ANTIGUO
    // public function addToCart(Request $request){

    //     $product = Product::findOrFail($request->product_id);
    //     $brand = Brand::all();

    //    // check product quantity
    //    if($product->qty === 0){
    //     return response(['status' => 'error', 'message' => 'Producto Agotado']);
    //     }elseif($product->qty < $request->qty){
    //         return response(['status' => 'error', 'message' => 'Cantidad agotada']);
    //     }



    //     $variantTotalAmonut = 0;

    //     /** check discount */
    //     $productPrice = 0;

    //     if(checkDiscount($product)){
    //         $productPrice = $product->offert_price;
    //     }else {
    //         $productPrice = $product->price;
    //     }

    //     $cartData = [];
    //     $cartData['id'] = $product->id;
    //     $cartData['name'] = $product->name;
    //     $cartData['qty'] = $request->qty;
    //     $cartData['price'] = $productPrice;
    //     $cartData['weight'] = 10;
    //     $cartData['options']['sku'] = $product->sku;
    //     $cartData['options']['productModel'] = $product->productModel;
    //     $cartData['options']['image'] = $product->thumb_image;
    //     $cartData['options']['slug'] = $product->slug;
    //     $cartData['options']['brand_name'] = $request->brand_name;



    //     Cart::add($cartData);

    //     return response(['status' => 'success', 'message' => 'Agregado al carrito con exito']);
    // }

    public function updateProductQty(Request $request)
    {
        // Bug 1: validar quantity antes de comparar contra stock (ver nota
        // equivalente en addToCart() sobre por qué se usa Validator::make()
        // en vez de $request->validate()).
        $validator = \Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        // Cart::get() lanza InvalidRowIDException (no devuelve null) si el
        // rowId ya no existe — ej. otra pestaña eliminó la línea, o la
        // sesión del carrito expiró — sin este try/catch tronaba con un 500
        // en vez de la respuesta de error normal del endpoint.
        try {
            $cartItem = Cart::get($request->rowId);
        } catch (\Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException $e) {
            return response(['status' => 'error', 'message' => 'Este producto ya no está en tu carrito']);
        }

        // Si el ID del carrito tiene el prefijo 'comb_', es una combinación
        if (strpos($cartItem->id, 'comb_') === 0 && isset($cartItem->options['combination_id'])) {
            $combinationId = $cartItem->options['combination_id'];
            $combination = \App\Models\ProductVariantCombinations::findOrFail($combinationId);

            // Validar stock de la combinación
            if ($combination->qty === 0) {
                return response(['status' => 'error', 'message' => 'Producto Agotado']);
            } elseif ($combination->qty < $request->quantity) {
                return response(['status' => 'error', 'message' => 'Cantidad máxima en existencias']);
            }
        } else {
            // Producto base
            $productId = $cartItem->id;
            $product = Product::findOrFail($productId);

            // Validar stock del producto base
            $stock = $product->qty_personalizated == 0 ? $product->qty_aspel : $product->qty;

            if ($stock === 0) {
                return response(['status' => 'error', 'message' => 'Producto Agotado']);
            } elseif ($stock < $request->quantity) {
                return response(['status' => 'error', 'message' => 'Cantidad máxima en existencias']);
            }
        }

        Cart::update($request->rowId, $request->quantity);
        $productTotal = $this->getProductTotal($request->rowId);

        return response(['status' => 'success', 'message' => 'Cantidad actualizada con éxito', 'product_total' => $productTotal]);
    }



    //CONTROLADOR ANTIGUO
    // public function updateProductQty(Request $request){

    //     $productId = Cart::get($request->rowId)->id;
    //     $product = Product::findOrFail($productId);

    //     // check product quantity
    //     if($product->qty === 0){
    //         return response(['status' => 'error', 'message' => 'Producto Agotado']);
    //     }elseif($product->qty < $request->quantity){
    //         return response(['status' => 'error', 'message' => 'Cantidad maxima en existencias']);
    //     }


    //     Cart::update($request->rowId, $request->quantity);
    //     $productTotal = $this->getProductTotal($request->rowId);
        

    //     return response(['status' => 'success', 'message' => 'Agregado por exito!', 'product_total' => $productTotal]);
    // }

    /**get Product Total */

    public function getProductTotal($rowId){

        $item = Cart::get($rowId);
        // Bug 3: usar el precio resuelto en vivo (CartPricing) en vez del
        // precio cacheado en el item del carrito, para que el total por
        // línea del sidebar refleje cambios de precio posteriores al
        // add-to-cart, igual que el resto del carrito/checkout.
        $total = CartPricing::resolve($item)['price'] * $item->qty;
        return $total;


    }
    /**Get cart Total */

    public function cartTotal(){

        $total = 0;
        foreach(Cart::content() as $product){
            $total += $this->getProductTotal($product->rowId);
        }

        return $total;

    }

    /**Clear cart all product */

    public function clearCart(){

        Cart::destroy();

        if (auth()->check()) {
            Cart::erase(auth()->id());
        }

        return response(['status' => 'success', 'message' => 'carrito eliminado con exito con exito']);

    }


    /**Remove product */

    public function removeProduct($rowId){

        Cart::remove($rowId);

        return redirect()->back();

    }

    /**Cart Count */

    public function getCartCount(){

        return Cart::content()->count();

    }
    /**get cartProducts */
    public function getCartProducts(){

        return Cart::content();
    }

    public function removeSidebarProduct(Request $request){

        Cart::remove($request->rowId);

        return response(['status' => 'success', 'message' => 'Removido con exito']);

    }
    /**aplly coupon */
    /** Apply coupon */
    public function applyCoupon(Request $request)
    {
        if($request->coupon_code === null){
            return response(['status' => 'error', 'message' => 'Cupon requerido']);
        }

        $coupon = Coupon::where(['cod' => $request->coupon_code, 'status' => 1])->first();

        if($coupon === null){
            return response(['status' => 'error', 'message' => 'Coupon not exist!']);
        }elseif($coupon->start_date > date('Y-m-d')){
            return response(['status' => 'error', 'message' => 'Coupon not exist!']);
        }elseif($coupon->end_date < date('Y-m-d')){
            return response(['status' => 'error', 'message' => 'Coupon is expired']);
        }elseif($coupon->total_used >= $coupon->quantity){
            return response(['status' => 'error', 'message' => 'you can not apply this coupon']);
        }

        if($coupon->category_id !== null && getCouponScopedSubTotal($coupon->category_id, $coupon->sub_category_id, $coupon->child_category_id) <= 0){
            return response(['status' => 'error', 'message' => 'you can not apply this coupon']);
        }

        if($coupon->discount_type === 'amount'){
            Session::put('coupon', [
                'coupon_name' => $coupon->name,
                'coupon_code' => $coupon->cod,
                'discount_type' => 'amount',
                'discount' => $coupon->discount,
                'category_id' => $coupon->category_id,
                'sub_category_id' => $coupon->sub_category_id,
                'child_category_id' => $coupon->child_category_id
            ]);
        }elseif($coupon->discount_type === 'percent'){
            Session::put('coupon', [
                'coupon_name' => $coupon->name,
                'coupon_code' => $coupon->cod,
                'discount_type' => 'percent',
                'discount' => $coupon->discount,
                'category_id' => $coupon->category_id,
                'sub_category_id' => $coupon->sub_category_id,
                'child_category_id' => $coupon->child_category_id
            ]);
        }

        return response(['status' => 'success', 'message' => 'Coupon applied successfully!']);
    }


    /** Calculate coupon discount */
    public function couponCalculation()
    {
        if(Session::has('coupon')){
            $coupon = Session::get('coupon');
            $subTotal = getCartTotal();
            $discount = resolveCouponDiscount($coupon);
            $total = $subTotal - $discount;
            return response(['status' => 'success', 'cart_total' => $total, 'discount' => $discount]);
        }else {
            $total = getCartTotal();
            return response(['status' => 'success', 'cart_total' => $total, 'discount' => 0]);
        }
    }


}
