<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaypalSetting;
use Illuminate\Http\Request;

class PaypalSettingController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'integer'],
            'mode' => ['required', 'integer'],
            'country_name' => ['required', 'max:200'],
            'currency_name' => ['required', 'max:200'],
            'currency_rate' => ['required', 'integer'],
            'client_id' => ['required_if:mode,1'],
            'secret_key' => ['required_if:mode,1'],
            'webhook_id' => ['nullable', 'max:255'],
            'sandbox_client_id' => ['required_if:mode,0'],
            'sandbox_secret_key' => ['required_if:mode,0'],
            'sandbox_webhook_id' => ['nullable', 'max:255'],
        ]);
       PaypalSetting::updateOrCreate(
            ['id' => $id],
            [
                'status' => $request->status,
                'mode' => $request->mode,
                'country_name' => $request->country_name,
                'currency_name' => $request->currency_name,
                'currency_rate' => $request->currency_rate,
                'client_id' => $request->client_id,
                'secret_key' => $request->secret_key,
                'webhook_id' => $request->webhook_id,
                'sandbox_client_id' => $request->sandbox_client_id,
                'sandbox_secret_key' => $request->sandbox_secret_key,
                'sandbox_webhook_id' => $request->sandbox_webhook_id,

            ]);
            toastr('Actualizado Con exito', 'success', 'Success');
            return redirect()->back();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
