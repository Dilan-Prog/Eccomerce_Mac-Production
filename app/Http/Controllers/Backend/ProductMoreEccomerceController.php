<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductMoreEccomerceController extends Controller
{
    function index($productId) {
        $link = ProductMoreEccomerceController::where('product_id', $productId)->get();
        return view('')
        
    }
}
