<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Frontend\FlashSaleController;
use App\Http\Controllers\Backend\VendorController;
use App\Http\Controllers\Frontend\BrandsMarkController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\FrontendProductController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\UserAddressController;
use App\Http\Controllers\Frontend\UserDashboardController;
use App\Http\Controllers\Frontend\UserProfileController;
use App\Http\Controllers\Frontend\CheckOutController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\UserOrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('price', [HomeController::class, 'price'])->name('price');
Route::get('contact', [HomeController::class, 'contact'])->name('contact');
Route::get('about', [HomeController::class, 'about'])->name('about');
Route::get('associate',[HomeController::class, 'associatePage'])->name('associate');
Route::get('calibracion-puesta', [HomeController::class, 'servicesCalibration'])->name('calibracion-puesta');
Route::get('sistemas', [HomeController::class, 'servicesSistemas'])->name('sistemas');
Route::get('servicio-controladores-temperatura', [HomeController::class, 'servicesControllerTemperature'])->name('servicio-controladores-temperatura');
Route::get('servicio-instalacion-videoregistradores', [HomeController::class, 'servicesVideorecorders'])->name('servicio-instalacion-videoregistradores');
Route::get('servicio-instalacion-medidoresdeflujo', [HomeController::class, 'servicesMedidor'])->name('servicio-instalacion-medidoresdeflujo');
Route::get('servicio-instalacion-plc', [HomeController::class, 'servicesPlc'])->name('servicio-instalacion-plc');
Route::get('servicio-reparacion-videoregistradores', [HomeController::class, 'servicesReparacionvideorecorders'])->name('servicio-reparacion-videoregistradores');
Route::get('servicio-calibracion-ema', [HomeController::class, 'servicesCalibrationEMA'])->name('servicio-calibracion-ema');
Route::get('medicion', [HomeController::class, 'servicesMedicion'])->name('medicion');
Route::get('/googgle-feed_macdelnorte$product-merchant-center',[ProductController::class, 'generateFeedProduct']);


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/**send emails */
Route::post('email', [PaymentController::class, 'emailFormSend'])->name('email-form');

require __DIR__.'/auth.php';



Route::get('flash-sale', [FlashSaleController::class, 'index'])->name('flash-sale');

/**Products details */
Route::get('products', [FrontendProductController::class, 'productsIndex'])->name('products.index');
Route::get('product-detail/{slug}', [FrontendProductController::class, 'showProduct'])->name('product-detail');
Route::get('change-product-list-view', [FrontendProductController::class, 'chageListView'])->name('change-product-list-view');
/**Add to cart route */
Route::post('add-to-cart', [CartController::class, 'addToCart'])->name('add-to-cart');
Route::get('cart-details', [CartController::class, 'cartDetails'])->name('cart-details');
Route::post('cart/update-quantity', [CartController::class, 'updateProductQty'])->name('cart.update-quantity');
Route::get('clear.cart', [CartController::class, 'clearCart'])->name('clear.cart');
Route::get('cart/remove-product/{rowId}', [CartController::class, 'removeProduct'])->name('cart.remove-product');
Route::get('cart-count', [CartController::class, 'getCartCount'])->name('cart-count');
Route::get('cart-products', [CartController::class, 'getCartProducts'])->name('cart-products');
Route::post('cart/remove-sidebar-product', [CartController::class, 'removeSidebarProduct'])->name('cart.remove-sidebar-product');
Route::get('cart/sidebar-product-total', [CartController::class, 'cartTotal'])->name('cart.sidebar-product-total');
Route::get('apply-coupon', [CartController::class, 'applyCoupon'])->name('apply-coupon');
Route::get('coupon-calculation', [CartController::class, 'couponCalculation'])->name('coupon-calculation');



Route::group(['middleware' => ['auth','verified'], 'prefix' => 'user', 'as' => 'user.'],function(){
    Route::get('dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', [UserProfileController::class,'index'])->name('profile');//Perfil de Usuario
    Route::put('profile',[UserProfileController::class,'updateProfile'])->name('profile.update');//Update user
    Route::post('profile',[UserProfileController::class,'updatePassword'])->name('profile.update.password');//Update password
    /**User Address */
    Route::resource('address', UserAddressController::class);

    /**User Order */
    Route::get('orders', [UserOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/show/{id}', [UserOrderController::class, 'show'])->name('orders.show');


     /**Product Reviews routes */
     Route::post('review', [ReviewController::class, 'create'])->name('review.create');



    /**Checkout route */
    Route::get('checkout',[CheckOutController::class, 'index'])->name('checkout');
    Route::post('checkout/address',[CheckOutController::class, 'createAddress'])->name('checkout.address.create');
    Route::post('checkout/form-submit',[CheckOutController::class, 'checkOutFormSumit'])->name('checkout.form-submit');
    /**Payment routes */
    Route::get('payment',[PaymentController::class, 'index'])->name('payment');
    Route::get('payment-success',[PaymentController::class, 'paymentSuccess'])->name('payment.success')->middleware('signed');
    Route::get('payment-transfer-success', [PaymentController::class, 'paymentTransferSuccess'])
    ->name('payment-transfer.success')
    ->middleware('signed');

    /**Paypal route */
    Route::get('paypal/payment',[PaymentController::class, 'paywithPaypal'])->name('paypal.payment');
    Route::get('paypal/success',[PaymentController::class, 'paypalSuccess'])->name('paypal.success');
    Route::get('paypal/cancel',[PaymentController::class, 'paypalCancel'])->name('paypal.cancel');

    // Endpoint to Paypal

    Route::post('paypal/create-order', [PaymentController::class, 'createOrder'])->name('paypal.createOrder');
    Route::post('paypal/capture-order', [PaymentController::class, 'captureOrder'])->name('paypal.captureOrder');

    /**stripe routes */
    Route::post('stripe/payment', [PaymentController::class, 'payWithStripe'])->name('stripe.payment');

    /**transfer route */
    Route::post('/payment/transfer', [PaymentController::class, 'payWithTransfer'])->name('payment.transfer');



});

/**brand-mark  */
// Route::get('brands-mark', [BrandsMarkController::class , 'index'])->name('brands-mark');
