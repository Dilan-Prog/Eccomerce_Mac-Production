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
    /**
     * Imagenes y codigos de pedido de un modelo, a partir de sus productos.
     *
     * Solo se usa UN producto como fuente de fotos: su portada como imagen
     * principal y su galeria (productImageGalleries) como miniaturas. Antes
     * se juntaban las portadas de todos los codigos de pedido y salia la misma
     * foto repetida hasta 13 veces. Si ningun producto tiene galeria, queda la
     * portada sola y la vista no pinta la tira de miniaturas.
     *
     * $principal permite forzar que producto manda (p. ej. el 150S en la
     * landing de McDonnell & Miller); si no, el primero que tenga galeria.
     * thumb_image guarda URL absoluta; la galeria guarda ruta relativa.
     */
    private function catalogoBr($productos, $principal = null): array
    {
        $productos->load('productImageGalleries');

        $fuente = $principal
            ?? $productos->first(fn ($p) => $p->productImageGalleries->isNotEmpty())
            ?? $productos->first();

        $imagenes = collect();
        if ($fuente) {
            $imagenes->push($fuente->thumb_image);
            foreach ($fuente->productImageGalleries as $galeria) {
                $imagenes->push($galeria->image);
            }
        }

        return [
            'imagenes' => $imagenes
                ->filter()
                ->map(fn ($ruta) => str_starts_with($ruta, 'http') ? $ruta : url($ruta))
                ->unique(fn ($url) => preg_replace('/^media_[0-9a-f]+\./i', '', basename($url)))
                ->values(),
            'codigos' => $productos->pluck('name', 'slug'),
        ];
    }

    public function  brHoneywellDc1040(){
        return view('frontend.pages.br.honeywell-dc1040',
            $this->catalogoBr(\App\Models\Product::where('slug', 'like', '%dc1040%')->get()));
    }
    public function  brHoneywellDc1010(){
        return view('frontend.pages.br.honeywell-dc1010',
            $this->catalogoBr(\App\Models\Product::where('slug', 'like', '%dc1010%')->get()));
    }
    public function  brHoneywellDc1200(){
        // La serie DC1200 son los modelos DC1202, DC1203 y DC120L.
        return view('frontend.pages.br.honeywell-dc1200',
            $this->catalogoBr(\App\Models\Product::where('slug', 'like', '%dc120%')->get()));
    }
    public function  brHoneywellDc2800(){
        return view('frontend.pages.br.honeywell-dc2800',
            $this->catalogoBr(\App\Models\Product::where('slug', 'like', '%dc2800%')->get()));
    }
    public function  brMcdonnellMiller(){
        // Toda la categoria, menos los registros de prueba sin nombre real.
        $productos = \App\Models\Product::whereHas('category', fn ($q) => $q->where('slug', 'mcdonnell-miller'))
            ->where('name', '!=', 'asd')
            ->orderBy('name')
            ->get();
        // El 150S es el producto estrella de la landing: sus fotos mandan.
        $estrella = $productos->first(fn ($p) => str_starts_with($p->slug, '150s-hd'));
        return view('frontend.pages.br.mcdonnell-miller', $this->catalogoBr($productos, $estrella));
    }

    /**
     * Landing individual de un producto McDonnell & Miller.
     *
     * Una sola vista para los 14: lo que cambia (textos pt-BR, ficha, FAQ,
     * relacionados) vive en resources/data/br/mcdonnell/{slug}.php. El slug
     * de la URL es el nombre de ese archivo; si no existe, 404.
     */
    public function  brMcdonnellProduto(string $slug){
        // Solo letras, numeros y guiones: el slug termina en un require.
        abort_unless(preg_match('/^[a-z0-9-]+$/', $slug), 404);

        $archivo = resource_path("data/br/mcdonnell/{$slug}.php");
        abort_unless(is_file($archivo), 404);
        $datos = require $archivo;

        // get() y no first(): catalogoBr() espera una coleccion Eloquent (usa load()).
        $productos = \App\Models\Product::where('slug', $datos['catalogo_slug'])->get();
        $imagenes = $this->catalogoBr($productos, $productos->first())['imagenes'];

        // Catalogo completo de landings de la marca, para "Outros produtos".
        $todos = collect(glob(resource_path('data/br/mcdonnell/*.php')))
            ->mapWithKeys(fn ($f) => [basename($f, '.php') => require $f]);

        return view('frontend.pages.br.mcdonnell-produto', [
            'slug' => $slug,
            'p' => $datos,
            'imagenes' => $imagenes,
            'todos' => $todos,
        ]);
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

        if ($filtroNombre) {
            // Ya viene filtrado a una sola subcategoría/child categoría: un solo grupo, sin encabezado
            // (el nombre del filtro ya se muestra en el banner de arriba).
            $productosData = [[
                'nombre' => null,
                'productos' => $this->mapProductosParaVista($products),
            ]];
        } else {
            // Vista de la categoría completa: agrupar por subcategoría, omitiendo las que no tengan productos.
            $subcategoriasCategoria = Subcategory::where('category_id', $category->id)
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            $productosPorSubcategoria = $products->groupBy('sub_category_id');

            $productosData = [];
            foreach ($subcategoriasCategoria as $sub) {
                $productosSub = $productosPorSubcategoria->get($sub->id);
                if (!$productosSub || $productosSub->isEmpty()) {
                    continue;
                }

                $productosData[] = [
                    'nombre' => $sub->name,
                    'productos' => $this->mapProductosParaVista($productosSub),
                ];
            }

            // Productos de la categoría sin subcategoría asignada (o con una inactiva).
            $idsSubcategoriasActivas = $subcategoriasCategoria->pluck('id')->all();
            $productosSinSubcategoria = $products->filter(function ($product) use ($idsSubcategoriasActivas) {
                return !in_array($product->sub_category_id, $idsSubcategoriasActivas);
            });

            if ($productosSinSubcategoria->isNotEmpty()) {
                $productosData[] = [
                    'nombre' => 'Otros productos',
                    'productos' => $this->mapProductosParaVista($productosSinSubcategoria),
                ];
            }
        }

        return view('frontend.pages.categoria-productos', [
            'category' => $category,
            'categoriaData' => $categoriaData,
            'sidebarCategoriasData' => $sidebarCategoriasData,
            'productosData' => $productosData,
        ]);
    }

    private function mapProductosParaVista($products){
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'modelo' => $product->productModel ?: $product->sku,
                'nombre' => $product->name,
                'descripcion' => $product->short_description,
                'marca' => $product->brand->name ?? null,
                'masModelos' => $product->variants->count() > 1,
                'imagen' => $product->thumb_image_carrusel ? asset($product->thumb_image_carrusel) : ($product->thumb_image ? asset($product->thumb_image) : null),
                'url' => route('product-detail', $product->slug),
            ];
        }
        return $data;
    }



}
