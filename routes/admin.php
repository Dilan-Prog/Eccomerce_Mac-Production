<?php
/** Admin Panel Routes */

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\BankAccountController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\ShippingRuleController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\StaffUserController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\CustomerListController;
use App\Http\Controllers\Backend\DuplicateImagesController;
use App\Http\Controllers\Backend\FlashSaleController;
use App\Http\Controllers\Backend\MediaLibraryController;
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
use App\Http\Controllers\Backend\AdminCotizacionController;
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
Route::get('customer/table-data', [CustomerListController::class, 'tableData'])->name('customer.table-data');
Route::get('customer/export', [CustomerListController::class, 'export'])->name('customer.export');
Route::get('customer/create-fragment', [CustomerListController::class, 'createFragment'])->name('customer.create-fragment');
Route::post('customer', [CustomerListController::class, 'store'])->name('customer.store');
Route::put('customer/status-change', [CustomerListController::class, 'changeStatus'])->name('customer.status-change');
Route::post('customer/{user}/csf', [CustomerListController::class, 'uploadCsf'])->name('customer.csf.upload');
Route::get('customer/{user}/csf/view', [CustomerListController::class, 'viewCsf'])->name('customer.csf.view');
Route::put('customer/{user}/b2b-status', [CustomerListController::class, 'b2bStatus'])->name('customer.b2b.status');
Route::get('customer/{id}/edit-fragment', [CustomerListController::class, 'editFragment'])->name('customer.edit-fragment');
Route::put('customer/{id}', [CustomerListController::class, 'update'])->name('customer.update');

/**
 * Staff Users route — unified admin-ui replacement for manage-user +
 * admin-list, covering admin/vendor/associate/technician. Not removing the
 * two routes above yet; that deprecation/removal is a separate later step
 * once this module is confirmed working.
 */
Route::put('staff-users/change-status', [StaffUserController::class, 'changeStatus'])->name('staff-users.change-status');
Route::get('staff-users/table-data', [StaffUserController::class, 'tableData'])->name('staff-users.table-data');
Route::get('staff-users/export', [StaffUserController::class, 'export'])->name('staff-users.export');
Route::post('staff-users/bulk', [StaffUserController::class, 'bulkAction'])->name('staff-users.bulk');
Route::get('staff-users/create-fragment', [StaffUserController::class, 'createFragment'])->name('staff-users.create-fragment');
Route::get('staff-users/{id}/edit-fragment', [StaffUserController::class, 'editFragment'])->name('staff-users.edit-fragment');
Route::resource('staff-users', StaffUserController::class);

/** Cotizaciones */
Route::get('cotizaciones',          [AdminCotizacionController::class, 'index'])->name('cotizaciones.index');
Route::get('cotizaciones/table-data', [AdminCotizacionController::class, 'tableData'])->name('cotizaciones.table-data');
Route::get('cotizaciones/export', [AdminCotizacionController::class, 'export'])->name('cotizaciones.export');
Route::get('cotizaciones/clients-search', [AdminCotizacionController::class, 'clientsSearch'])->name('cotizaciones.clients-search');
Route::get('cotizaciones/products-search', [AdminCotizacionController::class, 'productsSearch'])->name('cotizaciones.products-search');
Route::get('cotizaciones/create', [AdminCotizacionController::class, 'create'])->name('cotizaciones.create');
Route::post('cotizaciones', [AdminCotizacionController::class, 'store'])->name('cotizaciones.store');
Route::get('cotizaciones/{cotizacion}/edit', [AdminCotizacionController::class, 'edit'])->name('cotizaciones.edit');
Route::post('cotizaciones/{cotizacion}/items', [AdminCotizacionController::class, 'storeItem'])->name('cotizaciones.items.store');
Route::put('cotizaciones/{cotizacion}/items/{item}', [AdminCotizacionController::class, 'updateItem'])->name('cotizaciones.items.update');
Route::delete('cotizaciones/{cotizacion}/items/{item}', [AdminCotizacionController::class, 'destroyItem'])->name('cotizaciones.items.delete');
Route::put('cotizaciones/{cotizacion}/currency', [AdminCotizacionController::class, 'updateCurrency'])->name('cotizaciones.currency');
Route::post('cotizaciones/{cotizacion}/finalize', [AdminCotizacionController::class, 'finalize'])->name('cotizaciones.finalize');
Route::get('cotizaciones/{cotizacion}/pdf', [AdminCotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
Route::get('cotizaciones/{cotizacion}', [AdminCotizacionController::class, 'show'])->name('cotizaciones.show');

/**Slider routes */
Route::get('slider/table-data', [SliderController::class, 'tableData'])->name('slider.table-data');
Route::get('slider/export', [SliderController::class, 'export'])->name('slider.export');
Route::post('slider/bulk', [SliderController::class, 'bulkAction'])->name('slider.bulk');
Route::get('slider/create-fragment', [SliderController::class, 'createFragment'])->name('slider.create-fragment');
Route::get('slider/{id}/edit-fragment', [SliderController::class, 'editFragment'])->name('slider.edit-fragment');
Route::resource('slider',SliderController::class)->except(['create', 'edit']);

/**Media Library routes */
Route::get('media-library', [MediaLibraryController::class, 'index'])->name('media-library.index');
Route::get('media-library/data', [MediaLibraryController::class, 'data'])->name('media-library.data');
Route::post('media-library/upload', [MediaLibraryController::class, 'store'])->name('media-library.store');
Route::delete('media-library/{path}', [MediaLibraryController::class, 'destroy'])->name('media-library.destroy');
Route::post('media-library/{path}/watermark', [MediaLibraryController::class, 'watermark'])->name('media-library.watermark');
Route::get('media-library/{path}/download', [MediaLibraryController::class, 'download'])->name('media-library.download');
Route::post('media-library/bulk', [MediaLibraryController::class, 'bulkAction'])->name('media-library.bulk');

/**Duplicate Images routes */
Route::post('duplicate-images/scan', [DuplicateImagesController::class, 'scan'])->name('duplicate-images.scan');
Route::get('duplicate-images/data', [DuplicateImagesController::class, 'data'])->name('duplicate-images.data');
Route::get('duplicate-images/search', [DuplicateImagesController::class, 'search'])->name('duplicate-images.search');
Route::post('duplicate-images/{group}/discard', [DuplicateImagesController::class, 'discard'])->name('duplicate-images.discard');
Route::post('duplicate-images/{group}/restore', [DuplicateImagesController::class, 'restore'])->name('duplicate-images.restore');
Route::post('duplicate-images/{group}/replace', [DuplicateImagesController::class, 'replace'])->name('duplicate-images.replace');

/**Category Routes */
Route::get('category/table-data', [CategoryController::class, 'tableData'])->name('category.table-data');
Route::get('category/export', [CategoryController::class, 'export'])->name('category.export');
Route::post('category/bulk', [CategoryController::class, 'bulkAction'])->name('category.bulk');
Route::get('category/create-fragment', [CategoryController::class, 'createFragment'])->name('category.create-fragment');
Route::get('category/{id}/edit-fragment', [CategoryController::class, 'editFragment'])->name('category.edit-fragment');
Route::resource('category',CategoryController::class)->except(['create', 'edit']);
Route::put('change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
/**SubCategory Routes */
Route::put('subcategory/change-status', [SubcategoryController::class, 'changeStatus'])->name('sub-category.change-status');
Route::get('sub-category/table-data', [SubcategoryController::class, 'tableData'])->name('sub-category.table-data');
Route::get('sub-category/export', [SubcategoryController::class, 'export'])->name('sub-category.export');
Route::post('sub-category/bulk', [SubcategoryController::class, 'bulkAction'])->name('sub-category.bulk');
Route::get('sub-category/create-fragment', [SubcategoryController::class, 'createFragment'])->name('sub-category.create-fragment');
Route::get('sub-category/{id}/edit-fragment', [SubcategoryController::class, 'editFragment'])->name('sub-category.edit-fragment');
Route::resource('sub-category',SubcategoryController::class)->except(['create', 'edit']);
/**Child Category Routes */
Route::put('child-category/change-status', [ChildCategoryController::class, 'changeStatus'])->name('child-category.change-status');
Route::get('get-subcategory',[ChildCategoryController::class, 'getSubCategories'])->name('get-subcategories');
Route::get('child-category/table-data', [ChildCategoryController::class, 'tableData'])->name('child-category.table-data');
Route::get('child-category/export', [ChildCategoryController::class, 'export'])->name('child-category.export');
Route::post('child-category/bulk', [ChildCategoryController::class, 'bulkAction'])->name('child-category.bulk');
Route::get('child-category/create-fragment', [ChildCategoryController::class, 'createFragment'])->name('child-category.create-fragment');
Route::get('child-category/{id}/edit-fragment', [ChildCategoryController::class, 'editFragment'])->name('child-category.edit-fragment');
Route::resource('child-category',ChildCategoryController::class)->except(['create', 'edit']);


/**Brand route */
Route::put('brand/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
Route::get('brand/table-data', [BrandController::class, 'tableData'])->name('brand.table-data');
Route::get('brand/export', [BrandController::class, 'export'])->name('brand.export');
Route::post('brand/bulk', [BrandController::class, 'bulkAction'])->name('brand.bulk');
Route::get('brand/create-fragment', [BrandController::class, 'createFragment'])->name('brand.create-fragment');
Route::get('brand/{id}/edit-fragment', [BrandController::class, 'editFragment'])->name('brand.edit-fragment');
Route::resource('brand',BrandController::class)->except(['create', 'edit']);

/**Roles route */
Route::get('roles/table-data', [RoleController::class, 'tableData'])->name('roles.table-data');
Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
Route::post('roles/bulk', [RoleController::class, 'bulkAction'])->name('roles.bulk');
Route::get('roles/create-fragment', [RoleController::class, 'createFragment'])->name('roles.create-fragment');
Route::get('roles/{id}/edit-fragment', [RoleController::class, 'editFragment'])->name('roles.edit-fragment');
Route::resource('roles', RoleController::class);

/**Products route */
Route::put('product/change-status', [ProductController::class, 'changeStatus'])->name('product.change-status');
Route::get('products/get-subcategories',[ ProductController::class, 'getSubCategories'])->name('product.get-subcategories');
Route::get('products/get-child-categories',[ ProductController::class, 'getChildCategories'])->name('product.get-child-categories');
Route::get('products/search-sku',[ ProductController::class, 'searchSku'])->name('product.search-sku');
Route::get('products/aspel-prices', [ProductController::class, 'getAspelPrices'])->name('product.aspel-prices');
Route::get('products/table-data', [ProductController::class, 'tableData'])->name('products.table-data');
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('products/bulk', [ProductController::class, 'bulkAction'])->name('products.bulk');
Route::get('products/create-fragment', [ProductController::class, 'createFragment'])->name('products.create-fragment');
Route::get('products/{id}/edit-fragment', [ProductController::class, 'editFragment'])->name('products.edit-fragment');
Route::resource('products',ProductController::class)->except(['create', 'edit']);


/**Product Sync Aspell Route */
Route::get('/sync-aspel', [AspelSyncController::class, 'index'])->name('sync-aspel.index');
Route::get('sync-aspel/table-data', [AspelSyncController::class, 'tableData'])->name('sync-aspel.table-data');
Route::get('sync-aspel/export', [AspelSyncController::class, 'export'])->name('sync-aspel.export');

/**Ads route */
Route::get('track-conversion', [TrackConversionController::class, 'index'])->name('track-conversion.index');
Route::get('track-conversion/table-data', [TrackConversionController::class, 'tableData'])->name('track-conversion.table-data');
Route::get('track-conversion/export', [TrackConversionController::class, 'export'])->name('track-conversion.export');


/**ProductImageGallery route */
Route::get('products-image-gallery/table-data', [ProductImageGalleryController::class, 'tableData'])->name('products-image-gallery.table-data');
Route::get('products-image-gallery/export', [ProductImageGalleryController::class, 'export'])->name('products-image-gallery.export');
Route::post('products-image-gallery/bulk', [ProductImageGalleryController::class, 'bulkAction'])->name('products-image-gallery.bulk');
Route::get('products-image-gallery/create-fragment', [ProductImageGalleryController::class, 'createFragment'])->name('products-image-gallery.create-fragment');
Route::get('products-image-gallery/{productId}', [ProductImageGalleryController::class, 'index'])->name('admin.products-image-gallery.index');
Route::resource('products-image-gallery', ProductImageGalleryController::class);

/**Product Variant */
Route::put('products-variant/change-status', [ProductVariantController::class, 'changeStatus'])->name('products-variant.change-status');
Route::get('products-variant/{product}/table-data', [ProductVariantController::class, 'tableData'])->name('products-variant.table-data');
Route::get('products-variant/export', [ProductVariantController::class, 'export'])->name('products-variant.export');
Route::post('products-variant/bulk', [ProductVariantController::class, 'bulkAction'])->name('products-variant.bulk');
Route::get('products-variant/create-fragment', [ProductVariantController::class, 'createFragment'])->name('products-variant.create-fragment');
Route::get('products-variant/{id}/edit-fragment', [ProductVariantController::class, 'editFragment'])->name('products-variant.edit-fragment');
Route::resource('products-variant', ProductVariantController::class)->except(['create', 'edit']);

/**Product Variant Item */
Route::get('products-variant-item/create-fragment/{productId}/{variantId}', [ProductVariantItemController::class, 'createFragment'])->name('products-variant-item.create-fragment');
Route::get('products-variant-item/{variantItemId}/edit-fragment', [ProductVariantItemController::class, 'editFragment'])->name('products-variant-item.edit-fragment');
Route::get('products-variant-item/{productId}/{variantId}' , [ProductVariantItemController::class, 'index'])->name('products-variant-item.index');
Route::get('products-variant-item/{productId}/{variantId}/table-data', [ProductVariantItemController::class, 'tableData'])->name('products-variant-item.table-data');
Route::get('products-variant-item/{productId}/{variantId}/export', [ProductVariantItemController::class, 'export'])->name('products-variant-item.export');
Route::post('products-variant-item/bulk', [ProductVariantItemController::class, 'bulkAction'])->name('products-variant-item.bulk');
Route::post('products-variant-item' , [ProductVariantItemController::class, 'store'])->name('products-variant-item.store');
Route::put('products-variant-item-update/{variantItemId}' , [ProductVariantItemController::class, 'update'])->name('products-variant-item.update');
Route::delete('products-variant-item/{variantItemId}' , [ProductVariantItemController::class, 'destroy'])->name('products-variant-item.destroy');
Route::put('products-variant-item-status' , [ProductVariantItemController::class, 'changeStatus'])->name('products-variant-item.change-status');

/**Product Variant Combinations*/
// Route::get('products-variant-combinations/{productId}', [ProductVariantCombinationsController::class, 'index'])->name('products-variant-combinations.index');
// Route::get('products-variant-combinations/create/{productId}', [ProductVariantCombinationsController::class, 'create'])->name('products-variant-combinations.create');
Route::get('products-variant-combinations/table-data', [ProductVariantCombinationsController::class, 'tableData'])->name('products-variant-combinations.table-data');
Route::get('products-variant-combinations/export', [ProductVariantCombinationsController::class, 'export'])->name('products-variant-combinations.export');
Route::post('products-variant-combinations/bulk', [ProductVariantCombinationsController::class, 'bulkAction'])->name('products-variant-combinations.bulk');
Route::get('products-variant-combinations/create-fragment', [ProductVariantCombinationsController::class, 'createFragment'])->name('products-variant-combinations.create-fragment');
Route::get('products-variant-combinations/{id}/edit-fragment', [ProductVariantCombinationsController::class, 'editFragment'])->name('products-variant-combinations.edit-fragment');
Route::resource('products-variant-combinations', ProductVariantCombinationsController::class)->except(['create', 'edit']);


/**Producto More Eccomerce */
Route::put('products-more-eccomerce/change-status', [ProductMoreEccomerceController::class, 'changeStatus'])->name('products-more-eccomerce.change-status');
Route::get('products-more-eccomerce/{product}/table-data', [ProductMoreEccomerceController::class, 'tableData'])->name('products-more-eccomerce.table-data');
Route::get('products-more-eccomerce/{product}/export', [ProductMoreEccomerceController::class, 'export'])->name('products-more-eccomerce.export');
Route::post('products-more-eccomerce/{product}/bulk', [ProductMoreEccomerceController::class, 'bulkAction'])->name('products-more-eccomerce.bulk');
Route::get('products-more-eccomerce/create-fragment', [ProductMoreEccomerceController::class, 'createFragment'])->name('products-more-eccomerce.create-fragment');
Route::get('products-more-eccomerce/{id}/edit-fragment', [ProductMoreEccomerceController::class, 'editFragment'])->name('products-more-eccomerce.edit-fragment');
Route::resource('products-more-eccomerce',ProductMoreEccomerceController::class)->except(['create', 'edit']);
// Route::get('product-more-eccomerce-edit/{productId}',[ProductMoreEccomerceController::class, 'edit'])->name('product-more-eccomerce.edit');

/**Flash Sale Routes */
Route::get('flash-sale', [FlashSaleController::class, 'index'])->name('flash-sale.index');
Route::get('flash-sale/table-data', [FlashSaleController::class, 'tableData'])->name('flash-sale.table-data');
Route::get('flash-sale/export', [FlashSaleController::class, 'export'])->name('flash-sale.export');
Route::post('flash-sale/bulk', [FlashSaleController::class, 'bulkAction'])->name('flash-sale.bulk');
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
Route::get('coupons/table-data', [CouponController::class, 'tableData'])->name('coupons.table-data');
Route::get('coupons/export', [CouponController::class, 'export'])->name('coupons.export');
Route::post('coupons/bulk', [CouponController::class, 'bulkAction'])->name('coupons.bulk');
Route::get('coupons/create-fragment', [CouponController::class, 'createFragment'])->name('coupons.create-fragment');
Route::get('coupons/{id}/edit-fragment', [CouponController::class, 'editFragment'])->name('coupons.edit-fragment');
Route::resource('coupons', CouponController::class)->except(['create', 'edit']);
/**Order routes */

Route::get('order-status', [OrderController::class, 'changeOrderStatus'])->name('order.status');
Route::get('payment-status', [OrderController::class, 'changePaymentStatus'])->name('payment.status');

Route::get('pending-orders', [OrderController::class, 'pendingOrders'])->name('pending.orders');
Route::get('processed-orders', [OrderController::class, 'processedOrders'])->name('processed-orders');
Route::get('dropped-off-orders', [OrderController::class, 'droppedOfOrders'])->name('dropped-off-orders');

Route::get('shipped-orders', [OrderController::class, 'shippedOrders'])->name('shipped-orders');
Route::get('out-for-delivery-orders', [OrderController::class, 'outForDeliveryOrders'])->name('out-for-delivery-orders');
Route::get('delivered-orders', [OrderController::class, 'deliveredOrders'])->name('delivered-orders');
Route::get('canceled-orders', [OrderController::class, 'canceledOrders'])->name('canceled-orders');

Route::get('order/table-data', [OrderController::class, 'tableData'])->name('order.table-data');
Route::get('order/export', [OrderController::class, 'export'])->name('order.export');
Route::post('order/bulk', [OrderController::class, 'bulkAction'])->name('order.bulk');
Route::resource('order', OrderController::class);

/** Order Transaction route */
Route::get('transaction', [TransactionController::class, 'index'])->name('transaction');
Route::get('transaction/table-data', [TransactionController::class, 'tableData'])->name('transaction.table-data');
Route::get('transaction/export', [TransactionController::class, 'export'])->name('transaction.export');

/**Shipping Rules */
Route::put('shipping-rule/change-status', [ShippingRuleController::class, 'changeStatus'])->name('shipping-rule.change-status');
Route::get('shipping-rule/table-data', [ShippingRuleController::class, 'tableData'])->name('shipping-rule.table-data');
Route::get('shipping-rule/export', [ShippingRuleController::class, 'export'])->name('shipping-rule.export');
Route::post('shipping-rule/bulk', [ShippingRuleController::class, 'bulkAction'])->name('shipping-rule.bulk');
Route::get('shipping-rule/create-fragment', [ShippingRuleController::class, 'createFragment'])->name('shipping-rule.create-fragment');
Route::get('shipping-rule/{id}/edit-fragment', [ShippingRuleController::class, 'editFragment'])->name('shipping-rule.edit-fragment');
Route::resource('shipping-rule', ShippingRuleController::class)->except(['create', 'edit']);


/**Bank Accounts (Cuentas Bancarias) */
Route::get('bank-account/table-data', [BankAccountController::class, 'tableData'])->name('bank-account.table-data');
Route::get('bank-account/export', [BankAccountController::class, 'export'])->name('bank-account.export');
Route::post('bank-account/bulk', [BankAccountController::class, 'bulkAction'])->name('bank-account.bulk');
Route::get('bank-account/create-fragment', [BankAccountController::class, 'createFragment'])->name('bank-account.create-fragment');
Route::get('bank-account/{id}/edit-fragment', [BankAccountController::class, 'editFragment'])->name('bank-account.edit-fragment');
Route::resource('bank-account', BankAccountController::class)->except(['create', 'edit']);


/**Payment Settings Routes */
Route::get('payment-settings', [PaymentSettingController::class, 'index'])->name('payment-settings.index');
Route::resource('paypal-setting', PaypalSettingController::class);
Route::put('stripe-setting/{id}', [StripeSettingController::class, 'update'])->name('stripe-setting.update');
Route::put('transfer-setting/{id}', [TransferController::class, 'update'])->name('transfer.update');
