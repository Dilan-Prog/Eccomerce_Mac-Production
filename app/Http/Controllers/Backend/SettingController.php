<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:settings');
    }

    public function index(){
        $generalSettings = GeneralSetting::first();
        return view('admin-ui.settings.index', compact('generalSettings'));

    }


    public function generalSettingUpdate(Request $request){
        $request->validate([
            'site_name' => ['required', 'max:200'],
            'layout' =>['required', 'max:200'],
            'contact_email' =>['required', 'max:200'],
            'currency_name' =>['required', 'max:200'],
            'currency_icon' =>['required', 'max:200'],
            'time_zone' =>['required', 'max:200'],
            // Exclusivos del módulo de Cotizaciones — ver App\Support\CotizacionExchange.
            'tipo_cambio_usd_mxn' => ['required', 'numeric', 'min:0.0001'],
            'tipo_cambio_mxn_usd' => ['required', 'numeric', 'min:0.0001'],
        ]);

        GeneralSetting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => $request->site_name,
                'layout' => $request->layout,
                'contact_email' => $request->contact_email,
                'currency_name' => $request->currency_name,
                'currency_icon' => $request->currency_icon,
                'time_zone' => $request->time_zone,
                'tipo_cambio_usd_mxn' => $request->tipo_cambio_usd_mxn,
                'tipo_cambio_mxn_usd' => $request->tipo_cambio_mxn_usd,
            ]
        );

        toastr('Guardado Correctamente', 'success', 'Success');
        return redirect()->back();
    }
}
