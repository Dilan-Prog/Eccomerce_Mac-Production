<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AspelSync;
use App\Models\Brand;
use App\Models\PrecioXProductAspel;
use App\Models\Product;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductTableQuery;
use App\Support\AspelPricing;
use App\Traits\ImageUploadTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\OrderProduct;
use App\Models\ProductImageGallery;
use App\Models\ProductVariant;
use App\Models\ProductVariantItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class ProductController extends Controller
{
    use ImageUploadTrait;

    public function __construct()
    {
        $this->middleware('can-access-module:products');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.products.index');
    }

    /** JSON data source for the custom admin table (replaces ProductDataTable). */
    public function tableData(Request $request, ProductTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every product matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, ProductTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Productos',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('productos.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "productos.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. Mirrors destroy()'s cleanup exactly, skipping products with existing orders. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $products = Product::whereIn('id', $ids)->get();
            $deleted = 0;
            $skipped = 0;

            foreach ($products as $product) {
                if (OrderProduct::where('product_id', $product->id)->count() > 0) {
                    $skipped++;
                    continue;
                }

                /**Delete the main product image */
                $this->deleteImage($product->thumb_image);
                if (!empty($product->thumb_image_laptop)) {
                    $this->deleteImage($product->thumb_image_laptop);
                }
                if (!empty($product->thumb_image_tablet)) {
                    $this->deleteImage($product->thumb_image_tablet);
                }
                if (!empty($product->thumb_image_phone)) {
                    $this->deleteImage($product->thumb_image_phone);
                }
                if (!empty($product->thumb_image_carrusel)) {
                    $this->deleteImage($product->thumb_image_carrusel);
                }

                /**Delete product gallery images */
                $galleryImages = ProductImageGallery::where('product_id', $product->id)->get();
                foreach ($galleryImages as $image) {
                    $this->deleteImage($image->image);
                    $image->delete();
                }

                /**Delete product Variants if exists */
                $variants = ProductVariant::where('product_id', $product->id)->get();
                foreach ($variants as $variant) {
                    $variant->productVariantItems()->delete();
                    $variant->delete();
                }

                $product->delete();
                $deleted++;
            }

            $message = "{$deleted} producto(s) eliminado(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} producto(s) no se eliminaron por tener órdenes asociadas.";
            }

            return response()->json(['status' => 'success', 'message' => $message]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $ivaValue = DB::table('general_settings')->value('iva_mexico');
    
        return view('admin.product.create', compact('categories', 'brands', 'ivaValue'));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        $categories = Category::all();
        $subCategories = Subcategory::all();
        $childCategories = ChildCategory::all();
        $brands = Brand::all();

        return view('admin-ui.products._form', compact('categories', 'subCategories', 'childCategories', 'brands'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. Mirrors edit()'s Aspel lookup exactly. */
    public function editFragment(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $subCategories = Subcategory::all();
        $childCategories = ChildCategory::all();
        $brands = Brand::all();
        // First 3 gallery rows for the sidebar's read-only preview slots (see
        // _form.blade.php's "Galería del producto" block — there is no
        // dedicated DB column for these, they're just this product's own
        // products-image-gallery rows).
        $galleryImages = $product->productImageGalleries()->orderBy('id')->take(3)->get();
        $aspelPriceOptions = [];
        try {
            $aspelPriceOptions = AspelPricing::optionsForSku($product->sku);
        } catch (\Throwable $e) {
            $aspelPriceOptions = [];
        }

        return view('admin-ui.products._form', compact(
            'product', 'brands', 'categories', 'subCategories', 'childCategories',
            'galleryImages', 'aspelPriceOptions'
        ));
    }

    /**
     * Bare, read-only details fragment for the admin-ui Products list's
     * "click a row" details modal. Mirrors editFragment()'s defensive style
     * (Aspel/DB lookups wrapped in try/catch) but never writes anything and
     * loads the FULL image gallery (no take() limit — this isn't a sidebar
     * preview slot, it's the whole gallery).
     */
    public function detailsFragment(string $id)
    {
        $product = Product::findOrFail($id);
        $category = $product->category;
        $brand = $product->brand;
        $subCategory = $product->sub_category_id ? Subcategory::find($product->sub_category_id) : null;
        $childCategory = $product->child_category_id ? ChildCategory::find($product->child_category_id) : null;

        $galleryImages = $product->productImageGalleries()->orderBy('id')->get();

        $aspelPriceOptions = [];
        try {
            $aspelPriceOptions = AspelPricing::optionsForSku((string) $product->sku);
        } catch (\Throwable $e) {
            $aspelPriceOptions = [];
        }

        $aspelTierLabel = null;
        if (!empty($product->aspel_price_tier)) {
            $tier = collect($aspelPriceOptions)->firstWhere('cve_precio', (int) $product->aspel_price_tier);
            $aspelTierLabel = $tier['descripcion'] ?? null;
        }

        $aspelOffertTierLabel = null;
        if (!empty($product->aspel_offert_price_tier)) {
            $tier = collect($aspelPriceOptions)->firstWhere('cve_precio', (int) $product->aspel_offert_price_tier);
            $aspelOffertTierLabel = $tier['descripcion'] ?? null;
        }

        // Datos crudos de Aspel (existencia real, costos, moneda) — distintos
        // de qty_aspel/aspel_price en products, que son una copia sincronizada
        // y pueden estar desactualizados si el job de sync no ha corrido.
        $aspelSync = null;
        $aspelCurrency = null;
        try {
            $aspelSync = AspelSync::where('cve_art', (string) $product->sku)->first();
            if ($aspelSync) {
                $aspelCurrency = DB::table('monedas_aspel')->where('num_moneda', $aspelSync->num_mon)->first();
            }
        } catch (\Throwable $e) {
            $aspelSync = null;
            $aspelCurrency = null;
        }

        // Combinaciones de variante (talla/color/etc.) con su propio precio y
        // stock — mismo patrón de resolución de etiquetas que
        // AdminCotizacionController::productsSearch() usa para el picker de
        // cotizaciones.
        $combinations = collect();
        try {
            $rawCombinations = $product->combinations()->get();
            $variantItemNames = ProductVariantItem::whereIn(
                'id',
                $rawCombinations->flatMap(fn ($c) => $c->variants_item_ids ?? [])->unique()->values()
            )->pluck('name', 'id');

            $combinations = $rawCombinations->map(function ($combination) use ($variantItemNames) {
                $labelParts = collect($combination->variants_item_ids ?? [])
                    ->map(fn ($itemId) => $variantItemNames->get($itemId))
                    ->filter()
                    ->values();

                return [
                    'label' => $labelParts->isNotEmpty() ? $labelParts->implode(' / ') : ('SKU: ' . $combination->sku),
                    'sku' => $combination->sku,
                    'price' => (float) $combination->price,
                    'offert_price' => $combination->offert_price !== null ? (float) $combination->offert_price : null,
                    'qty' => (int) $combination->qty,
                ];
            });
        } catch (\Throwable $e) {
            $combinations = collect();
        }

        // Same fallback logic as effectivePrice()/effectiveStock() (see Product model),
        // wrapped defensively since these accessors touch product state that could be
        // malformed on legacy rows and this view must never break on bad data.
        try {
            $effectivePrice = $product->effectivePrice();
        } catch (\Throwable $e) {
            $effectivePrice = (float) $product->price;
        }

        try {
            $effectiveStock = $product->effectiveStock();
        } catch (\Throwable $e) {
            $effectiveStock = (int) $product->qty;
        }

        try {
            $hasActiveOffer = checkDiscount($product);
        } catch (\Throwable $e) {
            $hasActiveOffer = false;
        }

        // Mirrors the price_offert_personalizated fallback used inside effectivePrice(),
        // shown separately so an admin can see the offer price regardless of whether
        // the offer window is currently active.
        $effectiveOffertPrice = $product->price_offert_personalizated == 1
            ? $product->offert_price
            : ($product->aspel_offert_price ?? $product->offert_price);

        return view('admin-ui.products._details', compact(
            'product', 'category', 'brand', 'subCategory', 'childCategory',
            'galleryImages', 'aspelTierLabel', 'aspelOffertTierLabel', 'aspelPriceOptions',
            'aspelSync', 'aspelCurrency', 'combinations', 'effectivePrice', 'effectiveStock',
            'effectiveOffertPrice', 'hasActiveOffer'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'image'=>[Rule::requiredIf(!$request->filled('image_from_library')), 'nullable', 'image', 'max:3000'],
            'image_from_library'=>['nullable', 'string'],
            'name'=>['required','max:200'],
            'category' => ['required'],
            'brand' => ['required', 'integer', 'exists:brands,id'],
            'price'=>['nullable'],
            'qty'=>['nullable'],
            'qty_aspel' => ['nullable'],
            'short_description'=>['required','max:600'],
            'long_description'=>['required'],
            'seo_title' => ['nullable','max:200'],
            'sep_description' => ['nullable','max:250'],
            'status' => ['required'],
            'url_PDF' => ['nullable'],
            'canonical_url' => ['nullable', 'url'],
            'is_canonical' => ['nullable', 'boolean'],
            'aspel_price' => ['nullable'],
            'price_personalizated' => ['nullable','boolean'],
            'offert_price' => ['nullable'],
            'aspel_offert_price' => ['nullable'],
            'price_offert_personalizated' => ['nullable','boolean'],
        ]);
        

        $product = new Product();
        $category = Category::findOrFail($request->category);
        $brand = Brand::findOrFail($request->brand);
        /**Handle the image upload */
        $imagePathComputers = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) . '/webp/computers/', null, 1200, 1200, true);
        $imagePathLaptop    = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/laptop', null, 1000, 1000, true);
        $imagePathTablet    = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/tablet', null, 800, 800, true);
        $imagePathPhone     = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/phone', null, 600, 600, true);
        $imagePathCarrusel  = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/carrusel', null, 600, 600, true);

        $product->thumb_image          = $imagePathComputers;
        $product->thumb_image_laptop   = $imagePathLaptop;
        $product->thumb_image_tablet   = $imagePathTablet;
        $product->thumb_image_phone    = $imagePathPhone;
        $product->thumb_image_carrusel = $imagePathCarrusel;
        $product->name = $request->name;
        $product->slug=Str::slug($request->name);
    /** In case of ocuped vendor plus        $product->vendor_id= Auth::user()->vendor->id;*/
        $product->category_id = $request->category;
        $product->sub_category_id = $request->sub_category;
        $product->child_category_id = $request->child_category;
        $product->brand_id = $request->brand;
        $product->qty_personalizated = $request->qty_personalizated;
        if($request->qty_personalizated == 0){
            $product->qty = 0;
        }
        else{
            $product->qty = $request->qty;
        }
        $product->qty_aspel = $request->qty_aspel;
        $product->short_description = $request->short_description;
        $product->long_description = $request->long_description;
        $product->video_link = $request->video_link;
        $product->url_PDF = $request->url_PDF;
        $product->sku = $request->sku;
        $product->productModel = $request->productModel;
        $product->price_personalizated = $request->price_personalizated;
        if($request->price_personalizated == 0){
            $product->price = 0;
        }
        else{
            $product->price = $request->price;
        }
        $product->price_offert_personalizated = $request->price_offert_personalizated;
        if($request->price_offert_personalizated == 0){
            $product->offert_price = 0;
        }
        else{
            $product->offert_price = $request->offert_price;
        }
        $this->applyAspelPricing($product, $request);
        $product->offer_start_date = $request->offer_start_date;
        $product->offer_end_date = $request->offer_end_date;
        $product->product_type = $request->product_type;
        $product->status = $request->status;
        $product->is_approved = 1;
        $product->seo_title = $request->seo_title;
        $product->seo_description = $request->seo_description;
        $product->canonical_url = $request->canonical_url;
        $product->is_canonical = $request->is_canonical;
        // Testeo de envio de datos
        // dd($request->all());

        $product->save();



        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Producto creado con éxito.']);
        }

        toastr('Producto Creado Con exito');
        return redirect()->route('admin.products.index');



    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $subCategories = Subcategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::all();
        $aspelPriceOptions = [];
        $aspelProductData = null;
        $aspelCurrency = null;
        $ivaValue = DB::table('general_settings')->value('iva_mexico') ?? 16.00;
        try {
            $aspelProduct = AspelSync::where('cve_art', $product->sku)->first();
            if ($aspelProduct) {
                $aspelPriceOptions = PrecioXProductAspel::with('precio_info')
                    ->where('cve_art', $aspelProduct->cve_art)
                    ->get();
                // Obtener datos del producto Aspel (existencias)
                $aspelProductData = $aspelProduct;
                // Obtener moneda de Aspel para convertir a MXN en la vista
                $aspelCurrency = DB::table('monedas_aspel')
                    ->where('num_moneda', $aspelProduct->num_mon)
                    ->first();
            }
        } catch (\Throwable $e) {
            // Evitar que un fallo en Aspel rompa la vista
            $aspelPriceOptions = [];
            $aspelProductData = null;
            $aspelCurrency = null;
        }


        return view('admin.product.edit', compact('product','brands','categories','subCategories','childCategories', 'aspelPriceOptions', 'aspelProductData', 'aspelCurrency', 'ivaValue'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $request->validate([
            'image'=>['nullable','image','max:3000'],
            'image_from_library'=>['nullable', 'string'],
            'name'=>['required','max:200'],
            'category' => ['required'],
            'brand' => ['required', 'integer', 'exists:brands,id'],
            'price'=>['nullable'],
            'qty'=>['nullable'],
            'qty_aspel' => ['nullable'],
            'qty_personalizated' => ['nullable','boolean'],
            'short_description'=>['required','max:600'],
            'long_description'=>['required'],
            'seo_title' => ['nullable','max:200'],
            'sep_description' => ['nullable','max:250'],
            'status' => ['required'],
            'url_PDF' => ['nullable'],
            'canonical_url' => ['nullable', 'url'],
            'is_canonical' => ['nullable', 'boolean'],
            'aspel_price' => ['nullable'],
            'price_personalizated' => ['nullable','boolean'],
            'offert_price' => ['nullable'],
            'aspel_offert_price' => ['nullable'],
            'price_offert_personalizated' => ['nullable','boolean'],

        ]);
        $product = Product::findOrFail($id);
        $brand = Brand::findOrFail($request->brand);

        /**Handle the image upload */
        if ($request->hasFile('image') || $request->filled('image_from_library')) {
            $product->thumb_image = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) . '/webp/computers/', $product->thumb_image, 1200, 1200, true) ?: $product->thumb_image;
            $product->thumb_image_laptop = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/laptop', $product->thumb_image_laptop, 1000, 1000, true) ?: $product->thumb_image_laptop;
            $product->thumb_image_tablet = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/tablet', $product->thumb_image_tablet, 800, 800, true) ?: $product->thumb_image_tablet;
            $product->thumb_image_phone = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/phone', $product->thumb_image_phone, 600, 600, true) ?: $product->thumb_image_phone;
            $product->thumb_image_carrusel = $this->resolveOrCopyImage($request,'image','image_from_library','uploads/product/' . Str::slug($brand->name) .'/webp/carrusel', $product->thumb_image_carrusel, 600, 600, true) ?: $product->thumb_image_carrusel;
        }

        $product->name = $request->name;
        $product->slug=Str::slug($request->name);
    /** In case of ocuped vendor plus        $product->vendor_id= Auth::user()->vendor->id;*/
        $product->category_id = $request->category;
        $product->sub_category_id = $request->sub_category;
        $product->child_category_id = $request->child_category;
        $product->brand_id = $request->brand;
        $product->qty_personalizated = $request->qty_personalizated;
        if($request->qty_personalizated == 0){
            $product->qty = 0;
        }
        else{
            $product->qty = $request->qty;
        }
        $product->qty_aspel = $request->qty_aspel;
        $product->short_description = $request->short_description;
        $product->long_description = $request->long_description;
        $product->video_link = $request->video_link;
        $product->url_PDF = $request->url_PDF;
        $product->sku = $request->sku;
        $product->productModel = $request->productModel;
        $product->price_personalizated = $request->price_personalizated;
        if($request->price_personalizated == 0){
            $product->price = 0;
        }
        else{
            $product->price = $request->price;
        }
        $product->price_offert_personalizated = $request->price_offert_personalizated;
        if($request->price_offert_personalizated == 0){
            $product->offert_price = 0;
        }
        else{
            $product->offert_price = $request->offert_price;
        }
        $this->applyAspelPricing($product, $request);
        $product->offer_start_date = $request->offer_start_date;
        $product->offer_end_date = $request->offer_end_date;
        $product->product_type = $request->product_type;
        $product->status = $request->status;
        $product->is_approved = 1;
        $product->seo_title = $request->seo_title;
        $product->seo_description = $request->seo_description;
        $product->canonical_url = $request->canonical_url;
        $product->is_canonical = $request->is_canonical;
        
        // dd($request->all());
        
        $product->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Producto actualizado con éxito.']);
        }

        toastr('Producto Actualizado Con exito');
        return redirect()->route('admin.products.index');


    }

    /**
     * Sets aspel_price/aspel_price_tier (and the offer-price equivalents) from
     * whatever the form submitted for aspel_price/aspel_offert_price. That
     * field is a <select> of cve_precio tier ids when the SKU has Aspel price
     * data (_form.blade.php / sku-search.js), or a plain manual-value <input>
     * when it doesn't — so a submitted value only counts as a tier if it
     * actually matches one of this SKU's current tiers; otherwise it's kept
     * as a raw manual price, same as before this method existed.
     */
    private function applyAspelPricing(Product $product, Request $request): void
    {
        $options = collect(AspelPricing::optionsForSku((string) $product->sku))->keyBy('cve_precio');

        foreach (['aspel_price' => 'aspel_price_tier', 'aspel_offert_price' => 'aspel_offert_price_tier'] as $field => $tierField) {
            $submitted = $request->input($field);

            if ($submitted === null || $submitted === '') {
                $product->{$field} = null;
                $product->{$tierField} = null;
                continue;
            }

            $tier = $options->get((int) $submitted);
            if ($tier) {
                $product->{$tierField} = $tier['cve_precio'];
                $product->{$field} = $tier['with_iva'];
            } else {
                $product->{$tierField} = null;
                $product->{$field} = $submitted;
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        if(OrderProduct::where('product_id',$product->id)->count() > 0){
            return response(['status' => 'error', 'message' => 'This product have orders can\'t delete it.']);
        }
        /**Delete the main product image */
        $this->deleteImage($product->thumb_image);
        if (!empty($product->thumb_image_laptop)) {
            $this->deleteImage($product->thumb_image_laptop);
        }
        if (!empty($product->thumb_image_tablet)) {
            $this->deleteImage($product->thumb_image_tablet);
        }
        if (!empty($product->thumb_image_phone)) {
            $this->deleteImage($product->thumb_image_phone);
        }
        if (!empty($product->thumb_image_carrusel)) {
            $this->deleteImage($product->thumb_image_carrusel);
        }

        /**Delete product gallery images */
        $galleryImages = ProductImageGallery::where('product_id', $product->id)->get();
        foreach($galleryImages as $image){
            $this->deleteImage($image->image);
            $image->delete();
        }

        /**Delete product Variants if exists */

        $variants = ProductVariant::where('product_id', $product->id)->get();
        foreach($variants as $variant){
            $variant->productVariantItems()->delete();
            $variant->delete();
        }

        $product->delete();

        return response(['status' => 'success', 'message' => "Eliminado con exito"]);

    }

    public function changeStatus(Request $request){
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['message' =>'Status Changed Successfully!']);

    }
    /**
     * Get all sub categories
     */
    public function getSubCategories(Request $request)
    {
        $subCategories = Subcategory::where('category_id',$request->id)->get();
        return $subCategories;
    }
    public function getChildCategories(Request $request)
    {
        $childCategories = Childcategory::where('sub_category_id',$request->id)->get();
        return $childCategories;
    }

    /**
     * Search SKU in Aspel and Products tables
     */
    public function searchSku(Request $request)
    {
        $sku = $request->sku;
        $results = [];

        // Buscar en aspel_products que EMPIEZAN con el SKU ingresado
        try {
            $aspelProducts = AspelSync::where('cve_art', 'LIKE', $sku . '%')
                ->limit(20)
                ->get();
            
            foreach ($aspelProducts as $aspelProduct) {
                $aspelPrices =  PrecioXProductAspel::with('precio_info')
                    ->where('cve_art', $aspelProduct->cve_art)
                    ->get();

                $prices = [];
                foreach ($aspelPrices as $price) {
                    $prices[] = [
                        'cve_precio' => $price->cve_precio,
                        'precio' => $price->precio,
                        'descripcion' => optional($price->precio_info)->descripcion ?? 'Precio ' . $price->cve_precio
                    ];
                }

                $existsInProducts = Product::where('sku', $aspelProduct->cve_art)->exists();

                $results[] = [
                    'cve_art' => $aspelProduct->cve_art,
                    'descr' => $aspelProduct->descr,
                    'exist' => intval($aspelProduct->exist),
                    'costo_prom' => $aspelProduct->costo_prom,
                    'ult_costo' => $aspelProduct->ult_costo,
                    'aspel_prices' => $prices,
                    'exists_in_products' => $existsInProducts
                ];
            }
        } catch (\Throwable $e) {
            // Error al buscar en Aspel, continuar sin datos
        }

        return response()->json($results);
    }

    
    /**
     * Generate feed for Google Merchant Center
     */

    public function generateFeedProduct()  {
        $products = Product::with(['category', 'brand'])
        ->where('status', 1)
        ->where('is_approved', 1)
        ->where('price', '>', 0)
        ->get();

        

        $xml = new \SimpleXMLElement('<rss xmlns:g="http://base.google.com/ns/1.0"/>');
        $xml->addAttribute('version', '2.0');
        $channel = $xml->addChild('channel');

        $channel->addChild('title', 'Feed de productos de Laravel');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Feed de productos para Google Merchant Center');

        $gNamespace = 'http://base.google.com/ns/1.0';
        foreach ($products as $product) {
            $item = $channel->addChild('item');

            $item->addChild('g:id', htmlspecialchars($product->sku, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:title', htmlspecialchars($product->name, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:description', htmlspecialchars($product->long_description, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:link', htmlspecialchars(url("/product-detail/{$product->slug}"), ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild(
                'g:image_link',
                htmlspecialchars(
                    url('uploads/image-png/' . pathinfo($product->thumb_image, PATHINFO_FILENAME) . '_converted.png'),
                    ENT_XML1 | ENT_QUOTES,
                    'UTF-8'
                ),
                $gNamespace
            );

            foreach ($product->productImageGalleries as $galleryImage) {
                $item->addChild(
                    'g:additional_image_link',
                    htmlspecialchars(
                        url('uploads/image-png/' . pathinfo($galleryImage->image, PATHINFO_FILENAME) . '_converted.png'),
                        ENT_XML1 | ENT_QUOTES,
                        'UTF-8'
                    ),
                    $gNamespace
                );
            }
            $availability = $product->qty > 0 ? 'in stock' : 'out of stock';
            $item->addChild('g:availability', $availability, $gNamespace);

            $price = number_format((float)$product->price, 2, '.', '') . ' MXN';
            $item->addChild('g:price', $price, $gNamespace);

            if ($product->brand) {
                $item->addChild('g:brand', htmlspecialchars($product->brand->name, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
            // if ($product->sku) {
            //     $item->addChild('g:gtin', htmlspecialchars($product->sku, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            // }
            if ($product->productModel) {
                $item->addChild('g:mpn', htmlspecialchars($product->productModel, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
            $item->addChild('g:google_product_category', htmlspecialchars($product->category_id, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);

            if ($product->canonical_url) {
                $item->addChild('g:canonical_link', htmlspecialchars($product->canonical_url, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'text/xml');
    }


    public function generateFeedProductFacebook()  {
        $products = Product::with(['category', 'brand'])
        ->where('is_approved', 1)
        ->where('price', '>', 0)
        ->get();

        

        $xml = new \SimpleXMLElement('<rss xmlns:g="http://base.google.com/ns/1.0"/>');
        $xml->addAttribute('version', '2.0');
        $channel = $xml->addChild('channel');

        $channel->addChild('title', 'Feed de productos de Laravel');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Feed de productos para Google Merchant Center');

        $gNamespace = 'http://base.google.com/ns/1.0';
        foreach ($products as $product) {
            $item = $channel->addChild('item');

            $item->addChild('g:id', htmlspecialchars($product->sku, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:title', htmlspecialchars($product->name, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:description', htmlspecialchars($product->long_description, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild('g:link', htmlspecialchars(url("/product-detail/{$product->slug}"), ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            $item->addChild(
                'g:image_link',
                htmlspecialchars(
                    url('uploads/image-png/' . pathinfo($product->thumb_image, PATHINFO_FILENAME) . '_converted.png'),
                    ENT_XML1 | ENT_QUOTES,
                    'UTF-8'
                ),
                $gNamespace
            );

            foreach ($product->productImageGalleries as $galleryImage) {
                $item->addChild(
                    'g:additional_image_link',
                    htmlspecialchars(
                        url('uploads/image-png/' . pathinfo($galleryImage->image, PATHINFO_FILENAME) . '_converted.png'),
                        ENT_XML1 | ENT_QUOTES,
                        'UTF-8'
                    ),
                    $gNamespace
                );
            }
            $availability = $product->qty > 0 ? 'in stock' : 'out of stock';
            $item->addChild('g:availability', $availability, $gNamespace);

            $price = number_format((float)$product->price, 2, '.', '') . ' MXN';
            $item->addChild('g:price', $price, $gNamespace);
            $status = $product->qty > 0 ? 'active' : 'inactive';
            $item->addChild('g:status', $status, $gNamespace);

            if ($product->brand) {
                $item->addChild('g:brand', htmlspecialchars($product->brand->name, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
            // if ($product->sku) {
            //     $item->addChild('g:gtin', htmlspecialchars($product->sku, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            // }
            if ($product->productModel) {
                $item->addChild('g:mpn', htmlspecialchars($product->productModel, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
            $item->addChild('g:google_product_category', htmlspecialchars($product->category_id, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);

            if ($product->canonical_url) {
                $item->addChild('g:canonical_link', htmlspecialchars($product->canonical_url, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $gNamespace);
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'text/xml');
    }

    public function getAspelPrices(Request $request)
    {
        $sku = (string) $request->sku;
        $aspelProduct = AspelSync::where('cve_art', $sku)->first();
        $aspelCurrency = $aspelProduct
            ? DB::table('monedas_aspel')->where('num_moneda', $aspelProduct->num_mon)->first()
            : null;

        $result = collect(AspelPricing::optionsForSku($sku))->map(fn ($opt) => [
            'desc' => $opt['descripcion'],
            'val' => $opt['raw'],
            'convertedVal' => $opt['converted'],
            'priceWithIva' => $opt['with_iva'],
            'cve_precio' => $opt['cve_precio'],
        ])->values();

        return response()->json([
            'prices' => $result,
            'currency' => $aspelCurrency->cve_moned ?? 'MXN',
            'symbol' => $aspelCurrency->simbolo ?? '$',
            'iva' => DB::table('general_settings')->value('iva_mexico'),
        ]);
    }

}
