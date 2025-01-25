<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Adverisement;
use App\Models\Coupon;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Cart;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{

    public function cartDetails(){

        $cartItems = Cart::content();
        if(count($cartItems) === 0){
            Session::forget('coupon');
            return redirect()->route('index');
        }

        return view('frontend.pages.cart-details', compact('cartItems'));

    }

    public function addToCart(Request $request){

        $product = Product::findOrFail($request->product_id);
        $brand = Brand::all();

       // check product quantity
       if($product->qty === 0){
        return response(['status' => 'error', 'message' => 'Producto Agotado']);
        }elseif($product->qty < $request->qty){
            return response(['status' => 'error', 'message' => 'Cantidad agotada']);
        }



        $variantTotalAmonut = 0;

        /** check discount */
        $productPrice = 0;

        if(checkDiscount($product)){
            $productPrice = $product->offert_price;
        }else {
            $productPrice = $product->price;
        }

        $cartData = [];
        $cartData['id'] = $product->id;
        $cartData['name'] = $product->name;
        $cartData['qty'] = $request->qty;
        $cartData['price'] = $productPrice;
        $cartData['weight'] = 10;
        $cartData['options']['sku'] = $product->sku;
        $cartData['options']['productModel'] = $product->productModel;
        $cartData['options']['image'] = $product->thumb_image;
        $cartData['options']['slug'] = $product->slug;
        $cartData['options']['brand_name'] = $request->brand_name;



        Cart::add($cartData);

        return response(['status' => 'success', 'message' => 'Agregado al carrito con exito']);
    }

    public function updateProductQty(Request $request){

        $productId = Cart::get($request->rowId)->id;
        $product = Product::findOrFail($productId);

        // check product quantity
        if($product->qty === 0){
            return response(['status' => 'error', 'message' => 'Producto Agotado']);
        }elseif($product->qty < $request->quantity){
            return response(['status' => 'error', 'message' => 'Cantidad maxima en existencias']);
        }


        Cart::update($request->rowId, $request->quantity);
        $productTotal = $this->getProductTotal($request->rowId);
        

        return response(['status' => 'success', 'message' => 'Agregado por exito!', 'product_total' => $productTotal]);
    }

    /**get Product Total */

    public function getProductTotal($rowId){

        $product = Cart::get($rowId);
        $total = ($product->price) * $product->qty;
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

        if($coupon->discount_type === 'amount'){
            Session::put('coupon', [
                'coupon_name' => $coupon->name,
                'coupon_code' => $coupon->cod,
                'discount_type' => 'amount',
                'discount' => $coupon->discount
            ]);
        }elseif($coupon->discount_type === 'percent'){
            Session::put('coupon', [
                'coupon_name' => $coupon->name,
                'coupon_code' => $coupon->cod,
                'discount_type' => 'percent',
                'discount' => $coupon->discount
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
            if($coupon['discount_type'] === 'amount'){
                $total = $subTotal - $coupon['discount'];
                return response(['status' => 'success', 'cart_total' => $total, 'discount' => $coupon['discount']]);
            }elseif($coupon['discount_type'] === 'percent'){
                $discount = ($subTotal * $coupon['discount'] / 100);
                $total = $subTotal - $discount;
                return response(['status' => 'success', 'cart_total' => $total, 'discount' => $discount]);
            }
        }else {
            $total = getCartTotal();
            return response(['status' => 'success', 'cart_total' => $total, 'discount' => 0]);
        }
    }


}
