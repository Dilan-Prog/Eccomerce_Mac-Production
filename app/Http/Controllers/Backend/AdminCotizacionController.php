<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with(['user', 'perfil'])
            ->latest();

        // Buscador por folio, nombre o RFC
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                      ->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('perfil', fn ($p) => $p->where('rfc', 'like', "%{$search}%"));
            });
        }

        $cotizaciones = $query->paginate(20)->withQueryString();

        return view('admin.cotizaciones.index', compact('cotizaciones', 'search'));
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['user', 'perfil']);
        $pdfUrl = $cotizacion->pdf_path ? Storage::url($cotizacion->pdf_path) : null;

        return view('admin.cotizaciones.show', compact('cotizacion', 'pdfUrl'));
    }
}
