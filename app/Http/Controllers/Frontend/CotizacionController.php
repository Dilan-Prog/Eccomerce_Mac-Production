<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionRequest;
use App\Models\Cotizacion;
use App\Models\CotizacionPerfil;
use Barryvdh\DomPDF\Facade\Pdf;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CotizacionController extends Controller
{
    /**
     * Muestra el formulario o la pantalla de confirmación según si el usuario
     * ya tiene un perfil fiscal guardado.
     */
    public function formulario(Request $request)
    {
        $user = auth()->user();

        // Si viene con ?nuevo=1, forzamos el formulario sin importar si hay perfil
        if (! $request->boolean('nuevo')) {
            $perfil = CotizacionPerfil::where('user_id', $user->id)->latest()->first();

            if ($perfil) {
                return view('cotizaciones.confirmacion', compact('perfil'));
            }
        }

        return view('cotizaciones.formulario');
    }

    /**
     * Procesa el formulario con datos fiscales nuevos.
     */
    public function store(CotizacionRequest $request)
    {
        $user = auth()->user();

        // Guardar CIF
        $cifPath = $request->file('cif')->store('cif', 'public');

        // Crear perfil fiscal
        $perfil = CotizacionPerfil::create([
            'user_id'          => $user->id,
            'tipo_persona'     => $request->tipo_persona,
            'razon_social'     => $request->tipo_persona === 'empresa' ? $request->razon_social : null,
            'curp'             => $request->tipo_persona === 'fisica' ? $request->curp : null,
            'rfc'              => strtoupper($request->rfc),
            'direccion_fiscal' => $request->direccion_fiscal,
            'cif_path'         => $cifPath,
        ]);

        $cotizacion = $this->generarCotizacion($user, $perfil, $request->telefono);

        return redirect()->route('cotizacion.generada', $cotizacion->id);
    }

    /**
     * Genera cotización usando el perfil fiscal ya existente.
     */
    public function confirmar(Request $request)
    {
        $user   = auth()->user();
        $perfil = CotizacionPerfil::where('user_id', $user->id)->latest()->firstOrFail();

        $cotizacion = $this->generarCotizacion($user, $perfil, $request->input('telefono', $user->phone ?? ''));

        return redirect()->route('cotizacion.generada', $cotizacion->id);
    }

    /**
     * Muestra el modal con la vista previa del PDF generado.
     */
    public function generada(Cotizacion $cotizacion)
    {
        // Solo el dueño puede ver su cotización
        abort_if($cotizacion->user_id !== auth()->id(), 403);

        $pdfUrl = $cotizacion->pdf_path
            ? Storage::url($cotizacion->pdf_path)
            : null;

        return view('cotizaciones.generada', compact('cotizacion', 'pdfUrl'));
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Crea el registro Cotizacion, genera el folio y el PDF.
     */
    private function generarCotizacion($user, CotizacionPerfil $perfil, string $telefono = ''): Cotizacion
    {
        $cartItems = Cart::content();
        $subtotal  = (float) getMainCartTotal();
        $total     = $subtotal;

        // Snapshot del carrito
        $productos = $cartItems->map(function ($item) {
            return [
                'nombre'     => $item->name,
                'sku'        => $item->options->sku ?? '',
                'modelo'     => $item->options->productModel ?? '',
                'marca'      => $item->options->brand_name ?? '',
                'precio'     => $item->price,
                'cantidad'   => $item->qty,
                'subtotal'   => round($item->price * $item->qty, 2),
            ];
        })->values()->toArray();

        // Folio provisional (se actualiza con el ID real)
        $cotizacion = Cotizacion::create([
            'folio'                => 'COT-TEMP-' . time(),
            'user_id'              => $user->id,
            'cotizacion_perfil_id' => $perfil->id,
            'productos_json'       => $productos,
            'subtotal'             => $subtotal,
            'total'                => $total,
            'currency'             => 'MXN',
            'status'               => 'generada',
        ]);

        // Folio definitivo: COT-{AÑO}-{ID con 5 ceros}
        $cotizacion->folio = 'COT-' . now()->year . '-' . str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT);
        $cotizacion->save();

        // Generar PDF
        $pdfPath = $this->generarPdf($cotizacion, $perfil, $user, $telefono);
        $cotizacion->pdf_path = $pdfPath;
        $cotizacion->save();

        return $cotizacion;
    }

    /**
     * Renderiza la vista PDF y la guarda en storage.
     */
    private function generarPdf(Cotizacion $cotizacion, CotizacionPerfil $perfil, $user, string $telefono): string
    {
        Storage::disk('public')->makeDirectory('cotizaciones');

        $subtotalSinIva = round($cotizacion->total / 1.16, 2);
        $iva            = round($cotizacion->total - $subtotalSinIva, 2);

        $pdf = Pdf::loadView('cotizaciones.pdf', [
            'cotizacion'     => $cotizacion,
            'perfil'         => $perfil,
            'user'           => $user,
            'telefono'       => $telefono,
            'subtotalSinIva' => $subtotalSinIva,
            'iva'            => $iva,
        ])->setPaper('letter', 'portrait');

        $filename = $cotizacion->folio . '.pdf';
        Storage::disk('public')->put('cotizaciones/' . $filename, $pdf->output());

        return 'cotizaciones/' . $filename;
    }
}
