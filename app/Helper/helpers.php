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
        $price = \App\Support\CartPricing::resolve($product)['price'];
        $total += ($price * $product->qty);
    }

    return $total;
}

/**
 * Subtotal del carrito limitado a categoria/subcategoria/categoria hija —
 * usado por los cupones restringidos (coupons.category_id/sub_category_id/
 * child_category_id). Cualquiera de los 3 parametros en null significa "no
 * filtrar por ese nivel" (ej. solo categoryId => toda la categoria). Resuelve
 * las combinaciones de variante (id con prefijo 'comb_') a su product_id real
 * antes de comparar, ya que Cart::content() guarda el id de la combinacion,
 * no el del producto.
 */
function getCouponScopedSubTotal($categoryId, $subCategoryId = null, $childCategoryId = null){
    $subTotal = 0;

    foreach (Cart::content() as $item) {
        $productId = null;

        if (strpos((string) $item->id, 'comb_') === 0 && isset($item->options['combination_id'])) {
            $combination = \App\Models\ProductVariantCombinations::find($item->options['combination_id']);
            $productId = $combination->product_id ?? null;
        } else {
            $productId = $item->id;
        }

        if ($productId === null) {
            continue;
        }

        $product = \App\Models\Product::find($productId);
        if (!$product) {
            continue;
        }

        if ($categoryId !== null && (int) $product->category_id !== (int) $categoryId) {
            continue;
        }
        if ($subCategoryId !== null && (int) $product->sub_category_id !== (int) $subCategoryId) {
            continue;
        }
        if ($childCategoryId !== null && (int) $product->child_category_id !== (int) $childCategoryId) {
            continue;
        }

        $price = \App\Support\CartPricing::resolve($item)['price'];
        $subTotal += $price * $item->qty;
    }

    return $subTotal;
}

/** @deprecated usar getCouponScopedSubTotal() — se deja por compatibilidad, mismo comportamiento con solo categoria. */
function getCouponCategorySubTotal($categoryId){
    return getCouponScopedSubTotal($categoryId);
}

/**
 * Calcula el monto de descuento de un cupon (array guardado en la sesion
 * 'coupon' o con la misma forma). Centraliza la logica usada por el carrito
 * (couponCalculation) y por getMainCartTotal()/getCartDiscount() para que el
 * monto mostrado y el realmente cobrado (getFinalPayableAmount) coincidan
 * siempre. Si el cupon no tiene category_id (cupon global) el calculo es
 * identico al de antes, sobre el subtotal completo del carrito. Si tiene
 * category_id (y opcionalmente sub_category_id/child_category_id, el nivel
 * mas especifico que traiga el cupon), el descuento se limita al subtotal de
 * ESE alcance exacto: 'amount' se topa a ese subtotal (no puede exceder lo
 * que hay ahi) y 'percent' se calcula solo sobre ese subtotal.
 */
function resolveCouponDiscount(array $coupon){
    $categoryId = $coupon['category_id'] ?? null;

    if ($categoryId) {
        $scopedSubTotal = getCouponScopedSubTotal(
            $categoryId,
            $coupon['sub_category_id'] ?? null,
            $coupon['child_category_id'] ?? null
        );

        if ($coupon['discount_type'] === 'amount') {
            return min((float) $coupon['discount'], $scopedSubTotal);
        } elseif ($coupon['discount_type'] === 'percent') {
            return $scopedSubTotal * $coupon['discount'] / 100;
        }

        return 0;
    }

    $subTotal = getCartTotal();

    if ($coupon['discount_type'] === 'amount') {
        return $coupon['discount'];
    } elseif ($coupon['discount_type'] === 'percent') {
        return $subTotal * $coupon['discount'] / 100;
    }

    return 0;
}

function getMainCartTotal(){
    if(Session::has('coupon')){
        $coupon = Session::get('coupon');
        $subTotal = getCartTotal();
        $discount = resolveCouponDiscount($coupon);
        return $subTotal - $discount;
    }else{
        return getCartTotal();
    }
}
function getCartDiscount(){
    if(Session::has('coupon')){
        $coupon = Session::get('coupon');
        return resolveCouponDiscount($coupon);
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

/**
 * Convierte un monto a su representación en letras para el pie de una
 * cotización/factura estilo "TRES MIL DOSCIENTOS VEINTIDOS PESOS 48/100 M.N."
 * Sin acentos a propósito, para igualar el estilo de las cotizaciones Aspel
 * ya usadas por la empresa (ver referencia en resources/views/cotizaciones/pdf.blade.php).
 */
function numeroALetras($numero, string $moneda = 'pesos', string $sufijo = 'm.n.'): string
{
    $numero  = round((float) $numero, 2);
    $entero  = (int) floor($numero);
    $centavos = (int) round(($numero - $entero) * 100);

    $texto = _numALetrasApocope(trim($entero === 0 ? 'cero' : _numALetrasEntero($entero)));

    return mb_strtoupper($texto . ' ' . $moneda . ' ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 ' . $sufijo);
}

function _numALetrasApocope(string $texto): string
{
    if (str_ends_with($texto, 'veintiuno')) {
        return substr($texto, 0, -2) . 'un';
    }
    if (str_ends_with($texto, 'uno')) {
        return substr($texto, 0, -3) . 'un';
    }
    return $texto;
}

function _numALetrasEntero(int $n): string
{
    if ($n <= 0) {
        return 'cero';
    }

    $unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    if ($n < 10) {
        return $unidades[$n];
    }

    $especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve'];
    if ($n < 20) {
        return $especiales[$n - 10];
    }

    if ($n < 30) {
        return $n === 20 ? 'veinte' : 'veinti' . $unidades[$n - 20];
    }

    if ($n < 100) {
        $decenas = ['', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $d = intdiv($n, 10);
        $u = $n % 10;
        return $u === 0 ? $decenas[$d] : $decenas[$d] . ' y ' . $unidades[$u];
    }

    if ($n === 100) {
        return 'cien';
    }

    if ($n < 1000) {
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        $c = intdiv($n, 100);
        $r = $n % 100;
        return $r === 0 ? $centenas[$c] : $centenas[$c] . ' ' . _numALetrasEntero($r);
    }

    if ($n < 2000) {
        $r = $n - 1000;
        return $r === 0 ? 'mil' : 'mil ' . _numALetrasEntero($r);
    }

    if ($n < 1000000) {
        $miles = intdiv($n, 1000);
        $r = $n % 1000;
        $prefijo = _numALetrasApocope(_numALetrasEntero($miles)) . ' mil';
        return $r === 0 ? $prefijo : $prefijo . ' ' . _numALetrasEntero($r);
    }

    $millones = intdiv($n, 1000000);
    $r = $n % 1000000;
    $prefijo = $millones === 1 ? 'un millon' : _numALetrasApocope(_numALetrasEntero($millones)) . ' millones';
    return $r === 0 ? $prefijo : $prefijo . ' ' . _numALetrasEntero($r);
}

/**
 * Resuelve una URL completa de una imagen subida (como las que guarda
 * ImageUploadTrait — ej. http://host/uploads/logo/foo.webp) a un data URI
 * base64, para poder incrustarla en PDFs generados con dompdf sin depender
 * de fetch remoto ni de que la ruta caiga dentro del 'chroot' de dompdf
 * (UPLOADS_BASE_PATH puede vivir fuera de la raíz del proyecto). Devuelve
 * null si no hay URL o el archivo no existe en disco — nunca debe tronar la
 * generación del PDF por un logo/imagen faltante.
 */
function uploadedImageToBase64(?string $fullUrl): ?string
{
    if (!$fullUrl) {
        return null;
    }

    // Extrae todo a partir de "/uploads/" en vez de solo quitar config('app.url'):
    // las URLs guardadas en BD a veces traen www/no-www o http/https distinto
    // al APP_URL del entorno actual (ej. producción vs. este script), y así
    // se resuelve la ruta relativa sin depender de que coincidan exactamente.
    if (preg_match('#/uploads/.+$#', $fullUrl, $m)) {
        $relativePath = ltrim($m[0], '/');
    } else {
        $relativePath = ltrim(str_replace(config('app.url'), '', $fullUrl), '/');
    }

    $path = \App\Support\UploadPath::full($relativePath);

    if (!\Illuminate\Support\Facades\File::exists($path)) {
        return null;
    }

    // Siempre se re-codifica a PNG vía GD sin importar el formato de origen
    // (típicamente WEBP): probado directamente que dompdf (backend CPDF de
    // este proyecto) renderiza WEBP embebido con el canal alfa roto —
    // pierde el fondo y dibuja solo parte del contenido. PNG vía GD sí se
    // ve correcto, así que se normaliza aquí antes de incrustarlo.
    $image = @imagecreatefromstring(\Illuminate\Support\Facades\File::get($path));
    if (!$image) {
        return null;
    }

    imagesavealpha($image, true);
    ob_start();
    imagepng($image);
    $pngData = ob_get_clean();
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($pngData);
}