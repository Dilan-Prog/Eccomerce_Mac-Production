<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'integer'],
            'nameBank' => ['required', 'max:200'],
            'nameTitular' => ['required', 'max:200'],
            'accountNumber' => ['required', 'max:200'],
            'accountTarjet' => ['required', 'max:200'],
            'accountClabe' => ['required', 'max:200'],
            // Opcionales: no todas las cuentas necesitan publicar RFC, y la
            // moneda solo importa si algun dia se da de alta una segunda
            // cuenta en dolares.
            'rfc' => ['nullable', 'max:20'],
            'receiptEmail' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'max:10'],
        ]);
       Transfer::updateOrCreate(
            ['id' => $id],
            [
                'status' => $request->status,
                'nameBank' => $request->nameBank,
                'nameTitular' => $request->nameTitular,
                'accountNumber' => $request->accountNumber,
                'accountTarjet' => $request->accountTarjet,
                'accountClabe' => $request->accountClabe,
                'rfc' => $request->rfc,
                'receiptEmail' => $request->receiptEmail,
                'currency' => $request->currency,
            ]);
            toastr('Actualizado Con exito', 'success', 'Success');
            return redirect()->back();

    }
}
