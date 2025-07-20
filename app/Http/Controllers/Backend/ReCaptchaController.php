<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class ReCaptchaController extends Controller
{
    //
    public function verify(Request $request) 
    {
        $request->validate([
            'token' => 'required|string',
            'action' => 'required|string',
        ]);

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET'),
            'response' => $request->token,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        if (
            isset($result['success']) && $result['success'] === true &&
            isset($result['score']) && $result['score'] >= 0.5 &&
            isset($result['action']) && $result['action'] === $request->action
        ) {
            return response()->json(['message' => 'Validación exitosa'], 200);
        }

        return response()->json(['message' => 'Validación fallida'], 422);
    }
    

}
