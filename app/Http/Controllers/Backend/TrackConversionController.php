<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TrackConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackConversionController extends Controller
{
    //

    public function store(Request $request)
    {
        $data = $request->validate([
            'gclid' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
        ]);

        TrackConversion::create($data);
        Log::info('Conversion Rastreada', [
            'gclid' => $data['gclid'],
            'type' => $data['type'],
            'utm_source' => $data['utm_source'],
            'utm_medium' => $data['utm_medium'],
            'utm_campaign' => $data['utm_campaign'],
            'landing_page' => $data['landing_page'],
        ]);
        return response()->json(['success' => true]);
    }
}
