<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StripeSetting;
use Illuminate\Http\Request;

class StripeSettingController extends Controller
{
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'integer'],
            'mode' => ['required', 'integer'],
            'country_name' => ['required', 'max:200'],
            'currency_name' => ['required', 'max:200'],
            'currency_rate' => ['required'],
            'client_id' => ['required_if:mode,1'],
            'secret_key' => ['required_if:mode,1'],
            'webhook_secret' => ['nullable', 'max:255'],
            'sandbox_client_id' => ['required_if:mode,0'],
            'sandbox_secret_key' => ['required_if:mode,0'],
            'sandbox_webhook_secret' => ['nullable', 'max:255'],
        ]);
        // dd($request->all());
        StripeSetting::updateOrCreate(
            ['id' => $id],
            [
                'status' => $request->status,
                'mode' => $request->mode,
                'country_name' => $request->country_name,
                'currency_name' => $request->currency_name,
                'currency_rate' => $request->currency_rate,
                'client_id' => $request->client_id,
                'secret_key' => $request->secret_key,
                'webhook_secret' => $request->webhook_secret,
                'sandbox_client_id' => $request->sandbox_client_id,
                'sandbox_secret_key' => $request->sandbox_secret_key,
                'sandbox_webhook_secret' => $request->sandbox_webhook_secret,
            ]
        );

        toastr('Updated Successfully!', 'success', 'Success');
        return redirect()->back();
    }
}
