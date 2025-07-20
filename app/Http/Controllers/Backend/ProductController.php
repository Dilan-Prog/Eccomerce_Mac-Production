<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\OrderProduct;
use App\Models\ProductImageGallery;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Str;

class ProductController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('admin.product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.product.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'image'=>['required','image','max:3000'],
            'name'=>['required','max:200'],
            'category' => ['required'],
            'brand' => ['required', 'integer', 'exists:brands,id'],
            'price'=>['nullable'],
            'qty'=>['nullable'],
            'short_description'=>['required','max:600'],
            'long_description'=>['required'],
            'seo_title' => ['nullable','max:200'],
            'sep_description' => ['nullable','max:250'],
            'status' => ['required'],
            'url_PDF' => ['nullable'],
            'canonical_url' => ['nullable', 'url'],
            'is_canonical' => ['nullable', 'boolean'],
        ]);
        

        $product = new Product();
        $category = Category::findOrFail($request->category);
        $brand = Brand::findOrFail($request->brand);
        /**Handle the image upload */
        $imagePath =  $this->uploadImage($request,'image','uploads/product/' . Str::slug($brand->name) . '/webp/computers/', 1200, 1200);
        $imagePath =  $this->uploadImage($request,'image','uploads/product/' . Str::slug($brand->name) .'/webp/laptop', 1000, 1000);
        $imagePath =  $this->uploadImage($request,'image','uploads/product/' . Str::slug($brand->name) .'/webp/tablet', 800, 800);
        $imagePath =  $this->uploadImage($request,'image','uploads/product/' . Str::slug($brand->name) .'/webp/phone', 600, 600);
        $imagePath =  $this->uploadImage($request,'image','uploads/product/' . Str::slug($brand->name) .'/webp/carrusel', 600, 600);

        $product->thumb_image = $imagePath;
        $product->name = $request->name;
        $product->slug=Str::slug($request->name);
    /** In case of ocuped vendor plus        $product->vendor_id= Auth::user()->vendor->id;*/
        $product->category_id = $request->category;
        $product->sub_category_id = $request->sub_category;
        $product->child_category_id = $request->child_category;
        $product->brand_id = $request->brand;
        $product->qty = $request->qty;
        $product->short_description = $request->short_description;
        $product->long_description = $request->long_description;
        $product->video_link = $request->video_link;
        $product->url_PDF = $request->url_PDF;
        $product->sku = $request->sku;
        $product->productModel = $request->productModel;
        $product->price = $request->price;
        $product->offert_price = $request->offert_price;
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
        return view('admin.product.edit', compact('product','brands','categories','subCategories','childCategories'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $request->validate([
            'image'=>['nullable','image','max:3000'],
            'name'=>['required','max:200'],
            'category' => ['required'],
            'brand' => ['required', 'integer', 'exists:brands,id'],
            'price'=>['nullable'],
            'qty'=>['nullable'],
            'short_description'=>['required','max:600'],
            'long_description'=>['required'],
            'seo_title' => ['nullable','max:200'],
            'sep_description' => ['nullable','max:250'],
            'status' => ['required'],
            'url_PDF' => ['nullable'],
            'canonical_url' => ['nullable', 'url'],
            'is_canonical' => ['nullable', 'boolean'],

        ]);
        $product = Product::findOrFail($id);

        /**Handle the image upload */
        $imagePath =  $this->updateImage($request, 'image', 'uploads', $product->thumb_image, 800, 800);

        $product->thumb_image = empty(!$imagePath) ? $imagePath : $product->thumb_image;
        $product->name = $request->name;
        $product->slug=Str::slug($request->name);
    /** In case of ocuped vendor plus        $product->vendor_id= Auth::user()->vendor->id;*/
        $product->category_id = $request->category;
        $product->sub_category_id = $request->sub_category;
        $product->child_category_id = $request->child_category;
        $product->brand_id = $request->brand;
        $product->qty = $request->qty;
        $product->short_description = $request->short_description;
        $product->long_description = $request->long_description;
        $product->video_link = $request->video_link;
        $product->url_PDF = $request->url_PDF;
        $product->sku = $request->sku;
        $product->productModel = $request->productModel;
        $product->price = $request->price;
        $product->offert_price = $request->offert_price;
        $product->offer_start_date = $request->offer_start_date;
        $product->offer_end_date = $request->offer_end_date;
        $product->product_type = $request->product_type;
        $product->status = $request->status;
        $product->is_approved = 1;
        $product->seo_title = $request->seo_title;
        $product->seo_description = $request->seo_description;
        $product->canonical_url = $request->canonical_url;
        $product->is_canonical = $request->is_canonical;
        
        $product->save();

        toastr('Producto Actualizado Con exito');
        return redirect()->route('admin.products.index');


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

}
