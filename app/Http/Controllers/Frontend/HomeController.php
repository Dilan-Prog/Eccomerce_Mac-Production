<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ShippingRule;
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
        // Caché para la fecha de la venta flash
        $flashSaleDate = Cache::rememberForever('flash_sale_date', function() {
            return FlashSale::first();
        });

        $brands = Cache::remember('brand', 600, function(){
            return Brand::where('status', 1)->get();
        });
        

        // Caché para los artículos de la venta flash
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
            return Product::with(['productImageGalleries', 'category','reviews']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->whereIn('category_id', [ 2, 4, 12, 9]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });

        //Carrusel Category two
        Cache::forget('category_product_section_two');
        $categoryProductsSectionsTwo = Cache::remember('category_product_section_two', 600, function() {
            return Product::with(['productImageGalleries', 'category','reviews']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->whereIn('category_id', [20, 5, 6, 7]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });


        // Carrusel Category Three
        Cache::forget('category_product_section_three');
        $categoryProductsSectionsThree = Cache::remember('category_product_section_three', 600, function() {
            return Product::with(['productImageGalleries', 'category','reviews']) // Corrige aquí las relaciones
                ->where('status', 1)
                ->whereIn('category_id', [ 2, 4]) // Categorías específicas
                ->inRandomOrder() // Ordenar de forma aleatoria
                ->take(12)
                ->get();
        });

        return view('frontend.home.home', compact(
            'sliders',
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



}
