<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaypalSetting;
use App\Models\StripeSetting;
use App\Models\Transfer;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    public function index(){
        $paypalSetting = PaypalSetting::first();
        $stripeSetting = StripeSetting::first();
        $transferSetting = Transfer::first();
        return view('admin.payment-settings.index', compact('paypalSetting', 'stripeSetting', 'transferSetting'));

    }
}
