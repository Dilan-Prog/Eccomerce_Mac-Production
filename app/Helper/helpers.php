<?php
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Session;
/**Set Sidebar item active */

function setActive(array $route){
    if(is_array($route)){
        foreach($route as $r)
            if (request()->routeIs($r)) {
                return'active';
                # code...
            }
    }
}

/**Check if product have dicount */

function checkDiscount($product){
    $currentDate = date('Y-m-d');
    if($product->offert_price > 0 && $currentDate >= $product->offer_start_date && $currentDate <= $product->offer_end_date){
        return true;
    }

    return false;

}
function checkCombinationDiscount($combination){
    $currentDate = date('Y-m-d');
    if(
        isset($combination->offert_price) && $combination->offert_price > 0 &&
        isset($combination->offer_start_date) && isset($combination->offer_end_date) &&
        $currentDate >= $combination->offer_start_date &&
        $currentDate <= $combination->offer_end_date &&
        $combination->offert_price < $combination->price
    ){
        return true;
    }
    return false;
}

/**Calculate Discount percent */
function calculatedDiscountPercent($originalPrice, $discountPrice){

    $discountAmount = $originalPrice - $discountPrice;
    $discountPercent = ($discountAmount / $originalPrice) * 100;

    return round($discountPercent);

}

/**Check the product type */

function productType(string $type){

    switch($type){
                    case 'new_arrival':
                        return 'New';
                    break;
                    case 'featured_product':
                        return 'Featured';
                    break;
                    case 'top_product':
                        return 'Top';
                    break;
                    case 'best_product':
                        return 'Best';
                    break;
                    default:
                    return '';
                        break;
    }
}

/**Get Total*/

function getCartTotal(){
    $total = 0;
    foreach (Cart::content() as $product) {
        $total += ($product->price * $product->qty);
    }
    
    return $total;
}

function getMainCartTotal(){
    if(Session::has('coupon')){
        $coupon = Session::get('coupon');
        $subTotal = getCartTotal();
        if($coupon['discount_type'] === 'amount'){
            $total = $subTotal - $coupon['discount'];
            return $total;
        }elseif($coupon['discount_type'] === 'percent'){
            $discount = ($subTotal * $coupon['discount'] / 100);
            $total = $subTotal - $discount;
            return $total;
        }
    }else{
        return getCartTotal();
    }
}
function getCartDiscount(){
    if(Session::has('coupon')){
        $coupon = Session::get('coupon');
        $subTotal = getCartTotal();
        if($coupon['discount_type'] === 'amount'){
            return $coupon['discount'];
        }elseif($coupon['discount_type'] === 'percent'){
            $discount = ($subTotal * $coupon['discount'] / 100);
            return $discount;
        }
    }else{
        return 0;
    }
}
/** get selected shipping fee from session */
function getShppingFee(){
    if(Session::has('shipping_method')){
        return Session::get('shipping_method')['cost'];
    }else {
        return 0;
    }
}

/** get payable amount */
function getFinalPayableAmount(){
    return  getMainCartTotal() + getShppingFee();
}

function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

function getUrlcanonical(){
    $Urlcanonical = "https://www.macdelnorte.com/product-detail/";
    return $Urlcanonical;
}