<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ShippingRule;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    //

    public function index()
    {
        // Caché para los sliders
        $sliders = Cache::remember('sliders', 600, function() {
            return Slider::where('status', 1)->orderBy('serial', 'asc')->get();
        });

        // Slider hero: el primero activo por serial
        $slider = $sliders->first();
        // Caché para la fecha de la venta flash
        $flashSaleDate = Cache::rememberForever('flash_sale_date', function() {
            return FlashSale::first();
        });

        $brands = Cache::remember('brand', 600, function(){
            return Brand::where('status', 1)->get();
        });


        // Caché para los artículos de la venta flash
        Cache::forget('flash_sale_date');
        Cache::forget(key: 'flash_sale_items');
        $flashSaleItems = Cache::rememberForever('flash_sale_items', function() {
            return FlashSaleItem::with(['product', 'product.productImageGalleries', 'product.category','product.reviews'])
                ->where('show_at_home', 1)
                ->where('status', 1)
                ->get();
        });

        $shippingRules = ShippingRule::where('type', 'min_cost')->first();

        //Carrusel Category One
        Cache::forget('category_product_section_one');
        $categoryProductsSectionsOne = Cache::remember('category_product_section_one', 600, function() {
            return Product::with(['productImageGalleries', 'category', 'reviews', 'brand', 'combinations']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->where('price' )
                ->whereIn('category_id', [ 2, 4, 12, 9]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });

        //Carrusel Category two
        Cache::forget('category_product_section_two');
        $categoryProductsSectionsTwo = Cache::remember('category_product_section_two', 600, function() {
            return Product::with(['productImageGalleries', 'category', 'reviews', 'brand', 'combinations']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->whereIn('category_id', [20, 5, 6, 7]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });


        // Carrusel Category Three
        Cache::forget('category_product_section_three');
        $categoryProductsSectionsThree = Cache::remember('category_product_section_three', 600, function() {
            return Product::with(['productImageGalleries', 'category', 'reviews', 'brand', 'combinations']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->whereIn('category_id', [ 2, 4]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });

        return view('frontend.home.home', compact(
            'sliders',
            'slider',
            'flashSaleDate',
            'flashSaleItems',
            'brands',
            'categoryProductsSectionsOne',
            'categoryProductsSectionsTwo',
            'categoryProductsSectionsThree',
            'shippingRules'
        ));
    }


    public function price(){
        return view('frontend.pages.quotes');
    }


    public function contact(){
        return view('frontend.pages.contact');
    }

    public function about(){
        return view('frontend.pages.about');
    }

    public function servicesCalibration(){
        return view('frontend.pages.calibracion-puesta');
    }
    public function servicesSistemas(){
        return view('frontend.pages.sistemas');
    }
    public function servicesMedicion(){
        return view('frontend.pages.medicion');
    }

    public function  associatePage(){
        return view('frontend.pages.associate_page');
    }
    public function  servicesControllerTemperature(){
        return view('frontend.pages.controles');
    }
    public function  servicesVideorecorders(){
        return view('frontend.pages.videoregistradores');
    }
    public function  servicesMedidor(){
        return view('frontend.pages.medidores-flujo');
    }
    public function  servicesPlc(){
        return view('frontend.pages.plc');
    }
    public function  servicesReparacionvideorecorders(){
        return view('frontend.pages.reparacion-videoregistradores');
    }
    public function  servicesCalibrationEMA(){
        return view('frontend.pages.calibracion-ema');
    }
    public function  paypalInfo(){
        return view('frontend.pages.paypal_msi');
    }
    public function  terminosCondiciones(){
        return view('frontend.pages.Terminos-Conditions');
    }
    public function  avisoLegal(){
        return view('frontend.pages.aviso-legal');
    }
    public function  avisoPrivacidad(){
        return view('frontend.pages.aviso-privacidad');
    }
    public function  distribuidorHoneywell(){
        return view('frontend.pages.honeywell-oficial');
    }
    public function  catalogo(){
        return view('frontend.pages.catalogo');
    }
    public function  categorias(){
        $categorias = Category::active()
            ->withCount(['products' => function ($query) {
                $query->where('status', 1)->where('is_approved', 1);
            }])
            ->with(['subCategories' => function ($query) {
                $query->where('status', 1)
                    ->orderBy('name')
                    ->with(['childCategories' => function ($query2) {
                        $query2->where('status', 1)->orderBy('name');
                    }]);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categoriasData = [];
        foreach ($categorias as $categoria) {
            $categoriaUrl = route('categorias.productos', $categoria->slug);

            $subcategoriasData = [];
            foreach ($categoria->subCategories as $sub) {
                $childCategoriasData = [];
                foreach ($sub->childCategories as $child) {
                    $childCategoriasData[] = [
                        'nombre' => $child->name,
                        'url' => $categoriaUrl . '?childcategoria=' . $child->slug,
                    ];
                }

                $subcategoriasData[] = [
                    'nombre' => $sub->name,
                    'url' => $categoriaUrl . '?subcategoria=' . $sub->slug,
                    'childCategorias' => $childCategoriasData,
                ];
            }

            $categoriasData[] = [
                'slug' => $categoria->slug,
                'nombre' => $categoria->name,
                'productos' => $categoria->products_count,
                'url' => $categoriaUrl,
                'subcategorias' => $subcategoriasData,
            ];
        }

        return view('frontend.pages.categorias', compact('categoriasData'));
    }
    public function  categoriaProductos(Request $request, $categoria){
        $category = Category::where('slug', $categoria)->active()->firstOrFail();

        $categorias = Category::active()
            ->with(['subCategories' => function ($query) {
                $query->where('status', 1)
                    ->orderBy('name')
                    ->with(['childCategories' => function ($query2) {
                        $query2->where('status', 1)->orderBy('name');
                    }]);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $subcategoriaSlug = $request->query('subcategoria');
        $childcategoriaSlug = $request->query('childcategoria');
        $filtroNombre = null;

        $productsQuery = Product::with(['brand', 'variants'])
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->where('is_approved', 1);

        if ($childcategoriaSlug) {
            $childCategory = ChildCategory::where('slug', $childcategoriaSlug)->first();
            if ($childCategory) {
                $productsQuery->where('child_category_id', $childCategory->id);
                $filtroNombre = $childCategory->name;
            }
        } elseif ($subcategoriaSlug) {
            $subCategory = Subcategory::where('slug', $subcategoriaSlug)->first();
            if ($subCategory) {
                $productsQuery->where('sub_category_id', $subCategory->id);
                $filtroNombre = $subCategory->name;
            }
        }

        $products = $productsQuery->orderBy('name')->get();

        $categoriaData = [
            'slug' => $category->slug,
            'nombre' => $filtroNombre ?: $category->name,
            'descripcion' => $filtroNombre
                ? 'Productos de ' . $filtroNombre . ' dentro de ' . $category->name . '.'
                : 'Explora nuestra línea de ' . $category->name . '.',
            'filtroActivo' => $filtroNombre,
            'categoriaPadreNombre' => $category->name,
            'categoriaPadreUrl' => route('categorias.productos', $category->slug),
        ];

        $sidebarCategoriasData = [];
        foreach ($categorias as $sidebarCategoria) {
            $subcategoriasData = [];
            foreach ($sidebarCategoria->subCategories as $sub) {
                $childCategoriasData = [];
                foreach ($sub->childCategories as $child) {
                    $childCategoriasData[] = [
                        'nombre' => $child->name,
                        'url' => route('categorias.productos', $sidebarCategoria->slug) . '?childcategoria=' . $child->slug,
                    ];
                }

                $subcategoriasData[] = [
                    'nombre' => $sub->name,
                    'url' => route('categorias.productos', $sidebarCategoria->slug) . '?subcategoria=' . $sub->slug,
                    'childCategorias' => $childCategoriasData,
                ];
            }

            $sidebarCategoriasData[] = [
                'slug' => $sidebarCategoria->slug,
                'nombre' => $sidebarCategoria->name,
                'icono' => $sidebarCategoria->icon,
                'url' => route('categorias.productos', $sidebarCategoria->slug),
                'subcategorias' => $subcategoriasData,
            ];
        }

        $productosData = [];
        foreach ($products as $product) {
            $productosData[] = [
                'modelo' => $product->productModel ?: $product->sku,
                'nombre' => $product->name,
                'descripcion' => $product->short_description,
                'marca' => $product->brand->name ?? null,
                'masModelos' => $product->variants->count() > 1,
                'imagen' => $product->thumb_image ? asset($product->thumb_image) : null,
                'url' => route('product-detail', $product->slug),
            ];
        }

        return view('frontend.pages.categoria-productos', [
            'category' => $category,
            'categoriaData' => $categoriaData,
            'sidebarCategoriasData' => $sidebarCategoriasData,
            'productosData' => $productosData,
        ]);
    }



}
