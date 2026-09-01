<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserOrderController extends Controller
{
    public function show(string $id)
    {
        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);
        return view('frontend.dashboard.order.show', compact('order'));
    }
}
