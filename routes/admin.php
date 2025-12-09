<?php
/** Admin Panel Routes */

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\AdminListController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\ShippingRuleController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\CustomerListController;
use App\Http\Controllers\Backend\FlashSaleController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\PaymentSettingController;
use App\Http\Controllers\Backend\PaypalSettingController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\AspelSync\AspelSyncController;
use App\Http\Controllers\Backend\ProductImageGalleryController;
use App\Http\Controllers\Backend\ProductMoreEccomerceController;
use App\Http\Controllers\Backend\ProductVariantController;
use App\Http\Controllers\Backend\ProductVariantItemController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\StripeSettingController;
use App\Http\Controllers\Backend\TrackConversionController;
use App\Http\Controllers\Backend\TransactionController;
use App\Http\Controllers\Backend\TransferController;
use App\Http\Controllers\Backend\UserManageController;
use App\Http\Controllers\Backend\ProductVariantCombinationsController;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Route;

Route::get('dashboard',[AdminController::class,'dashboard'])->name( 'dashboard');

/**Profile routes */
Route::get('profile',[ProfileController::class, 'index'])->name( 'profile');
Route::post('profile/update',[ProfileController::class, 'updateProfile'])->name( 'profile.update');
Route::post('profile/update/password',[ProfileController::class, 'updatePassword'])->name( 'password.update');

/** coustomer list routes */
Route::get('customer', [CustomerListController::class, 'index'])->name('customer.index');
Route::put('customer/status-change', [CustomerListController::class, 'changeStatus'])->name('customer.status-change');

/**Admin List */
Route::get('admin-list', [AdminListController::class, 'index'])->name('admin-list.index');
Route::put('admin-list/status-change', [AdminListController::class, 'changeStatus'])->name('admin-list.status-change');
Route::delete('admin-list/{id}', [AdminListController::class, 'destory'])->name('admin-list.destory');
/**manege User */
Route::get('manage-user', [UserManageController::class, 'index'])->name('manage-user');
Route::post('manage-user', [userManageController::class, 'create'])->name('manage-user.create');

/**Slider routes */
Route::resource('slider',SliderController::class);

/**Category Routes */
Route::resource('category',CategoryController::class);
Route::put('change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
/**SubCategory Routes */
Route::put('subcategory/change-status', [SubcategoryController::class, 'changeStatus'])->name('sub-category.change-status');
Route::resource('sub-category',SubcategoryController::class);
/**Child Category Routes */
Route::put('child-category/change-status', [ChildCategoryController::class, 'changeStatus'])->name('child-category.change-status');
Route::get('get-subcategory',[ChildCategoryController::class, 'getSubCategories'])->name('get-subcategories');
Route::resource('child-category',ChildCategoryController::class);


/**Brand route */
Route::put('brand/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
Route::resource('brand',BrandController::class);

/**Products route */
Route::put('product/change-status', [ProductController::class, 'changeStatus'])->name('product.change-status');
Route::get('products/get-subcategories',[ ProductController::class, 'getSubCategories'])->name('product.get-subcategories');
Route::get('products/get-child-categories',[ ProductController::class, 'getChildCategories'])->name('product.get-child-categories');
Route::resource('products',ProductController::class);
Route::get('products/search', [ProductController::class, 'searchProducts'])->name('products.search');

/**Product Sync Aspell Route */
Route::get('/sync-aspel', [AspelSyncController::class, 'index'])->name('sync-aspel.index');

/**Ads route */
Route::get('track-conversion', [TrackConversionController::class, 'index'])->name('track-conversion.index');


/**ProductImageGallery route */
Route::get('products-image-gallery/{productId}', [ProductImageGalleryController::class, 'index'])->name('admin.products-image-gallery.index');
Route::resource('products-image-gallery', ProductImageGalleryController::class);

/**Product Variant */
Route::put('products-variant/change-status', [ProductVariantController::class, 'changeStatus'])->name('products-variant.change-status');
Route::resource('products-variant', ProductVariantController::class);

/**Product Variant Item */
Route::get('products-variant-item/{productId}/{variantId}' , [ProductVariantItemController::class, 'index'])->name('products-variant-item.index');
Route::get('products-variant-item/create/{productId}/{variantId}' , [ProductVariantItemController::class, 'create'])->name('products-variant-item.create');
Route::post('products-variant-item' , [ProductVariantItemController::class, 'store'])->name('products-variant-item.store');
Route::get('products-variant-item-edit/{variantItemId}' , [ProductVariantItemController::class, 'edit'])->name('products-variant-item.edit');
Route::put('products-variant-item-update/{variantItemId}' , [ProductVariantItemController::class, 'update'])->name('products-variant-item.update');
Route::delete('products-variant-item/{variantItemId}' , [ProductVariantItemController::class, 'destroy'])->name('products-variant-item.destroy');
Route::put('products-variant-item-status' , [ProductVariantItemController::class, 'changeStatus'])->name('products-variant-item.change-status');

/**Product Variant Combinations*/
// Route::get('products-variant-combinations/{productId}', [ProductVariantCombinationsController::class, 'index'])->name('products-variant-combinations.index');
// Route::get('products-variant-combinations/create/{productId}', [ProductVariantCombinationsController::class, 'create'])->name('products-variant-combinations.create');
Route::resource('products-variant-combinations', ProductVariantCombinationsController::class);


/**Producto More Eccomerce */
Route::put('products-more-eccomerce/change-status', [ProductMoreEccomerceController::class, 'changeStatus'])->name('products-more-eccomerce.change-status');
Route::resource('products-more-eccomerce',ProductMoreEccomerceController::class);
// Route::get('product-more-eccomerce-edit/{productId}',[ProductMoreEccomerceController::class, 'edit'])->name('product-more-eccomerce.edit');

/**Flash Sale Routes */
Route::get('flash-sale', [FlashSaleController::class, 'index'])->name('flash-sale.index');
Route::put('flash-sale', [FlashSaleController::class, 'update'])->name('flash-sale.update');
Route::post('flash-sale/add-product', [FlashSaleController::class, 'addProduct'])->name('flash-sale.add-product');
Route::put('flash-sale/show_at_home/status-change', [FlashSaleController::class, 'changeShowAtHomeStatus'])->name('flash-sale.show-at-home.change-status');
Route::put('flash-sale-status' , [FlashSaleController::class, 'changeStatus'])->name('flash-sale-status');
Route::delete('flash-sale/{id}', [FlashSaleController::class, 'destroy'])->name('flash-sale.destroy');

/**General Settings */
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('general-setting-update', [SettingController::class, 'generalSettingUpdate'])->name('general-setting-update');

/**Coupon routes */
Route::put('coupons/change-status', [CouponController::class, 'changeStatus'])->name('coupons.change-status');
Route::resource('coupons', CouponController::class);
/**Order routes */

// Route::get('invocie-pdf/{id}', [OrderController::class, 'pdf'])->name('invocie.pdf');
// Route::get('generate-pdf/{id}', [OrderController::class, 'generatePdf'])->name('generate.pdf');
Route::get('order-status', [OrderController::class, 'changeOrderStatus'])->name('order.status');
Route::get('payment-status', [OrderController::class, 'changePaymentStatus'])->name('payment.status');

Route::get('pending-orders', [OrderController::class, 'pendingOrders'])->name('pending.orders');
Route::get('processed-orders', [OrderController::class, 'processedOrders'])->name('processed-orders');
Route::get('dropped-off-orders', [OrderController::class, 'droppedOfOrders'])->name('dropped-off-orders');

Route::get('shipped-orders', [OrderController::class, 'shippedOrders'])->name('shipped-orders');
Route::get('out-for-delivery-orders', [OrderController::class, 'outForDeliveryOrders'])->name('out-for-delivery-orders');
Route::get('delivered-orders', [OrderController::class, 'deliveredOrders'])->name('delivered-orders');
Route::get('canceled-orders', [OrderController::class, 'canceledOrders'])->name('canceled-orders');

Route::resource('order', OrderController::class);

/** Order Transaction route */
Route::get('transaction', [TransactionController::class, 'index'])->name('transaction');

/**Shipping Rules */
Route::put('shipping-rule/change-status', [ShippingRuleController::class, 'changeStatus'])->name('shipping-rule.change-status');
Route::resource('shipping-rule', ShippingRuleController::class);


/**Payment Settings Routes */
Route::get('payment-settings', [PaymentSettingController::class, 'index'])->name('payment-settings.index');
Route::resource('paypal-setting', PaypalSettingController::class);
Route::put('stripe-setting/{id}', [StripeSettingController::class, 'update'])->name('stripe-setting.update');
Route::put('transfer-setting/{id}', [TransferController::class, 'update'])->name('transfer.update');

