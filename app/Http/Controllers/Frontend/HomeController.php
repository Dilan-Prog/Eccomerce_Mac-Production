<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
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
            return FlashSaleItem::with(['product', 'product.productImageGalleries', 'product.category'])
                ->where('show_at_home', 1)
                ->where('status', 1)
                ->get();
        });
        return view('frontend.home.home', compact(
            'sliders',
            'flashSaleDate',
            'flashSaleItems',
            'brands'
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
    
}
