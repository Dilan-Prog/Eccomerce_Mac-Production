<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionRequest;
use App\Models\Cotizacion;
use App\Models\CotizacionPerfil;
use App\Models\Product;
use App\Models\ProductVariantCombinations;
use Barryvdh\DomPDF\Facade\Pdf;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Guardar producto en sesión cuando viene desde la página de detalle
        if ($request->filled('producto_id')) {
            $product = Product::with('brand')->find($request->producto_id);
            if ($product) {
                $cantidad = max(1, (int) $request->input('cantidad', 1));
                $precio   = $product->effectivePrice();

                if ($request->filled('comb_id')) {
                    $comb = ProductVariantCombinations::find($request->comb_id);
                    if ($comb && $comb->effectivePrice() > 0) {
                        $precio = $comb->effectivePrice();
                    }
                }

                session(['cotizacion_productos' => [[
                    'nombre'   => $product->name,
                    'sku'      => $product->sku ?? '',
                    'modelo'   => $product->productModel ?? '',
                    'marca'    => optional($product->brand)->name ?? '',
                    'precio'   => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => round($precio * $cantidad, 2),
                ]]]);
            }
        }

        // Si viene con ?nuevo=1, forzamos el formulario sin importar si hay perfil
        if (! $request->boolean('nuevo')) {
            $perfil = CotizacionPerfil::where('user_id', $user->id)->latest()->first();

            if ($perfil) {
                return view('cotizaciones.confirmacion', compact('perfil'));
            }
        }

        return view('cotizaciones.formulario', ['user' => $user]);
    }

    /**
     * Procesa el formulario con datos fiscales nuevos.
     */
    public function store(CotizacionRequest $request)
    {
        if (! $this->hasQuotableItems()) {
            return redirect()->back()->with('error', 'Tu carrito está vacío. Agrega productos antes de generar una cotización.');
        }

        $user = auth()->user();

        // Usa el CIF ya subido en el registro a menos que suban uno nuevo.
        $cifPath = $user->csf_path;
        if ($request->hasFile('cif')) {
            $cifPath = $request->file('cif')->store('cif', 'public');
        }

        $rfc         = strtoupper($user->rfc ?? $request->rfc);
        $tipoPersona = $request->tipo_persona;
        $razonSocial = $tipoPersona === 'empresa' ? ($user->company ?? $request->razon_social) : null;
        $telefono    = $user->phone ?? $request->telefono;

        // Crear perfil fiscal
        $perfil = CotizacionPerfil::create([
            'user_id'          => $user->id,
            'tipo_persona'     => $tipoPersona,
            'razon_social'     => $razonSocial,
            'rfc'              => $rfc,
            'direccion_fiscal' => $request->direccion_fiscal,
            'cif_path'         => $cifPath,
        ]);

        $cotizacion = $this->generarCotizacion($user, $perfil, $telefono);

        return redirect()->route('cotizacion.generada', $cotizacion->id);
    }

    /**
     * Genera cotización usando el perfil fiscal ya existente.
     */
    public function confirmar(Request $request)
    {
        if (! $this->hasQuotableItems()) {
            return redirect()->back()->with('error', 'Tu carrito está vacío. Agrega productos antes de generar una cotización.');
        }

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
        abort_if($cotizacion->user_id !== auth()->id(), 403);

        // Cache-busting query param (the file's own mtime) — see
        // AdminCotizacionController::show() for why this is needed even
        // with the Cache-Control headers on pdf().
        $pdfUrl = null;
        if ($cotizacion->pdf_path) {
            $version = Storage::disk('public')->exists($cotizacion->pdf_path)
                ? Storage::disk('public')->lastModified($cotizacion->pdf_path)
                : time();
            $pdfUrl = route('cotizacion.pdf', $cotizacion->id) . '?v=' . $version;
        }

        return view('cotizaciones.generada', compact('cotizacion', 'pdfUrl'));
    }

    /**
     * Sirve el PDF directamente desde storage (sin depender del symlink).
     */
    public function pdf(Cotizacion $cotizacion)
    {
        abort_if($cotizacion->user_id !== auth()->id(), 403);
        abort_unless(
            $cotizacion->pdf_path && Storage::disk('public')->exists($cotizacion->pdf_path),
            404
        );

        return response()->file(
            Storage::disk('public')->path($cotizacion->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Indica si hay algo que cotizar: ya sea un producto guardado en sesión
     * (flujo "Cotizar este producto") o artículos en el carrito.
     */
    private function hasQuotableItems(): bool
    {
        return session()->has('cotizacion_productos') || Cart::content()->isNotEmpty();
    }

    /**
     * Crea el registro Cotizacion, genera el folio y el PDF.
     */
    private function generarCotizacion($user, CotizacionPerfil $perfil, string $telefono = ''): Cotizacion
    {
        // Si viene desde detalle de producto, usa la sesión; si no, usa el carrito
        if (session()->has('cotizacion_productos')) {
            $productos = session()->pull('cotizacion_productos');
            $subtotal  = (float) collect($productos)->sum('subtotal');
        } else {
            $cartItems = Cart::content();
            $subtotal  = (float) getMainCartTotal();
            $productos = $cartItems->map(function ($item) {
                // Precio en vivo (igual que $subtotal arriba, vía getMainCartTotal())
                // en vez del precio cacheado al momento de agregar al carrito.
                $precio = \App\Support\CartPricing::resolve($item)['price'];

                return [
                    'nombre'   => $item->name,
                    'sku'      => $item->options->sku ?? '',
                    'modelo'   => $item->options->productModel ?? '',
                    'marca'    => $item->options->brand_name ?? '',
                    'precio'   => $precio,
                    'cantidad' => $item->qty,
                    'subtotal' => round($precio * $item->qty, 2),
                ];
            })->values()->toArray();
        }

        $total = $subtotal;

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

        $cotizacion->folio = Cotizacion::buildFolio($cotizacion->id);
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

        $subtotalSinIva = round($cotizacion->total, 2);
        $ivaValue       = (float) (DB::table('general_settings')->value('iva_mexico') ?? 16.00);
        $iva            = round($subtotalSinIva * $ivaValue / 100, 2);
        $totalConIva    = $subtotalSinIva + $iva;

        $pdf = Pdf::loadView('cotizaciones.pdf', [
            'cotizacion'     => $cotizacion,
            'perfil'         => $perfil,
            'user'           => $user,
            'telefono'       => $telefono,
            'subtotalSinIva' => $subtotalSinIva,
            'iva'            => $iva,
            'totalConIva'    => $totalConIva,
            'ivaValue'       => $ivaValue,
        ])->setPaper('letter', 'portrait');

        $filename = $cotizacion->folio . '.pdf';
        Storage::disk('public')->put('cotizaciones/' . $filename, $pdf->output());

        return 'cotizaciones/' . $filename;
    }
}
