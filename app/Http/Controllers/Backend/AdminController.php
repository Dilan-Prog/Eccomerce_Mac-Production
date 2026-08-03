<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard is intentionally NOT gated by can-access-module: it's the
    // mandatory landing page every role='admin' account is redirected to
    // immediately after login (see AuthenticatedSessionController::store()),
    // with no fallback page if that were blocked. A custom role whose admin
    // forgot to check "Escritorio" would otherwise 403 on the very first
    // page they land on, with no way in. Every module BEYOND the dashboard
    // is still correctly restricted by each module's own controller.

    public function dashboard()
    {
        $todaysOrder = Order::whereDate('created_at', Carbon::today())->count();
        $todaysPendingOrder = Order::whereDate('created_at', Carbon::today())
        ->where('order_status', 'pending')->count();
        $totalOrders = Order::count();
        $totalPendingOrders = Order::where('order_status', 'pending')->count();
        $totalCanceledOrders = Order::where('order_status', 'canceled')->count();
        $totalCompleteOrders = Order::where('order_status', 'delivered')->count();

        $todaysEarnings = Order::where('order_status','!=', 'canceled')
        ->where('payment_status',1)
        ->whereDate('created_at', Carbon::today())
        ->sum('sub_total');

        $monthEarnings = Order::where('order_status','!=', 'canceled')
        ->where('payment_status',1)
        ->whereMonth('created_at', Carbon::now()->month)
        ->sum('sub_total');

        $yearEarnings = Order::where('order_status','!=', 'canceled')
        ->where('payment_status',1)
        ->whereYear('created_at', Carbon::now()->year)
        ->sum('sub_total');

        $totalBrands = Brand::count();
        $totalCategories = Category::count();


        $totalUsers = User::where('role', 'user')->count();



        return view('admin-ui.dashboard.index', compact(
            'todaysOrder',
            'todaysPendingOrder',
            'totalOrders',
            'totalPendingOrders',
            'totalCanceledOrders',
            'totalCompleteOrders',
            'todaysEarnings',
            'monthEarnings',
            'yearEarnings',
            'totalBrands',
            'totalCategories',
            
            'totalUsers'
        ));
    }

    public function login() {
        return view('admin.auth.login');
    }


}
