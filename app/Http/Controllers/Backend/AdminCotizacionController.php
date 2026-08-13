<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\CotizacionPerfil;
use App\Models\Product;
use App\Models\ProductVariantCombinations;
use App\Models\ProductVariantItem;
use App\Models\User;
use App\Support\CotizacionExchange;
use App\Support\CotizacionPricing;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\CotizacionTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class AdminCotizacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:cotizaciones');
    }

    public function index(Request $request)
    {
        return view('admin-ui.cotizaciones.index');
    }

    /** JSON data source for the custom admin table (replaces the hand-rolled paginated query above). */
    public function tableData(Request $request, CotizacionTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every cotización matching the current filter/search. */
    public function export(Request $request, CotizacionTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Cotizaciones',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('cotizaciones.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "cotizaciones.{$extension}", $writerType);
    }

    /**
     * JSON search endpoint used by the cotización builder's client picker.
     * Matches on the same field set as CustomerTableQuery::search() (name,
     * email, id). An empty term still returns the first 20 clients ordered
     * by name, so the picker has something to show on initial load.
     */
    public function clientsSearch(Request $request)
    {
        $term = (string) $request->input('q', '');

        $query = User::where('role', 'user');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('id', $term);
            });
        }

        $results = $query->orderBy('name')->limit(20)->get(['id', 'name', 'email', 'account_type']);

        $userIdsWithProfile = CotizacionPerfil::whereIn('user_id', $results->pluck('id'))
            ->pluck('user_id')
            ->all();

        return response()->json(['results' => $results->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'has_profile' => in_array($u->id, $userIdsWithProfile, true),
        ])]);
    }

    /**
     * JSON search endpoint used by the cotización builder's product picker.
     * Only active products (status = 1, same convention as
     * ProductTableQuery's "Activos" filter) are searchable here. Price/stock
     * are resolved via Product::effectivePrice()/effectiveStock() — never
     * recomputed here — and combination pricing via
     * ProductVariantCombinations::effectivePrice().
     */
    public function productsSearch(Request $request)
    {
        $term = (string) $request->input('q', '');

        $query = Product::where('status', 1)->with('brand');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('productModel', 'like', "%{$term}%");
            });
        }

        $products = $query->limit(20)->get();

        // Batch-load every combination for the matched products in one
        // query, then resolve all their variant item labels in a second
        // single query, to avoid N+1s across products/combinations.
        $combinationsByProduct = \App\Models\ProductVariantCombinations::whereIn('product_id', $products->pluck('id'))
            ->get()
            ->groupBy('product_id');

        $allVariantItemIds = $combinationsByProduct
            ->flatten()
            ->flatMap(fn ($combination) => $combination->variants_item_ids ?? [])
            ->unique()
            ->values();

        $variantItemNames = ProductVariantItem::whereIn('id', $allVariantItemIds)
            ->pluck('name', 'id');

        // Ruta global (sin cotización aún — se usa igual en create.blade.php
        // antes de que exista un borrador), así que solo puede convertir con
        // el tipo de cambio DEFAULT de Configuración General, no con el
        // override de una cotización específica. La conversión real y
        // definitiva ocurre en storeItem(), que sí conoce la cotización.
        $defaultRateUsdMxn = CotizacionExchange::defaultUsdToMxn();

        $results = $products->map(function (Product $product) use ($combinationsByProduct, $variantItemNames, $defaultRateUsdMxn) {
            $combinations = $combinationsByProduct->get($product->id, collect());
            $hasVariants = $combinations->isNotEmpty();

            // Niveles de precio (público/mínimo/liquidación/mayorista) solo existen
            // a nivel de producto base (products.sku = aspel_products.cve_art) —
            // las combinaciones de variante no tienen su propio cve_art, así que
            // nunca traen price_tiers. Vía CotizacionPricing (NO AspelPricing):
            // esta es la lógica de precios propia de cotizaciones, aislada de
            // Aspel — sin IVA, sin conversión de moneda de monedas_aspel (la
            // conversión de moneda si aplica es de CotizacionExchange, también
            // aislada, ver abajo).
            $priceTiers = $product->sku ? CotizacionPricing::optionsForSku($product->sku) : [];
            $nativeCurrency = $product->sku ? CotizacionExchange::nativeCurrencyForSku($product->sku) : null;

            $entry = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'modelo' => $product->productModel,
                'marca' => $product->brand->name ?? null,
                'price' => $product->effectivePrice(),
                'stock' => $product->effectiveStock(),
                'has_variants' => $hasVariants,
                // moneda_origen viaja junto con cada tier para que el picker
                // (antes de agregar la partida) pueda mostrar el precio EXACTO
                // sin convertir cuando coincide con la moneda elegida para la
                // cotización — mismo passthrough que ya aplica a las partidas
                // ya agregadas (Cotizacion::displayItemAmount()). Sin esto, el
                // picker corría TODO por la conversión en bloque y un producto
                // ya en USD se veía distorsionado (doble conversión) en la
                // vista previa aunque la partida real, ya guardada, sí saliera
                // exacta.
                'moneda_origen' => $nativeCurrency,
                'price_tiers' => array_map(fn ($tier) => [
                    'cve_precio' => $tier['cve_precio'],
                    'descripcion' => $tier['descripcion'],
                    'precio' => CotizacionExchange::normalizeToMxn($tier['precio'], $nativeCurrency, $defaultRateUsdMxn),
                    'precio_origen' => round((float) $tier['precio'], 2),
                ], $priceTiers),
                // Referencias para el campo de precio personalizado: piso (mínimo,
                // o 1 si no hay/está en 0) y techo informativo (público).
                'precio_minimo' => $product->sku
                    ? CotizacionExchange::normalizeToMxn(CotizacionPricing::minimoFor($product->sku) ?: 1.0, $nativeCurrency, $defaultRateUsdMxn)
                    : 1.0,
                'precio_publico' => null,
            ];

            if ($product->sku) {
                $publico = CotizacionPricing::publicoFor($product->sku);
                if ($publico) {
                    $entry['precio_publico'] = CotizacionExchange::normalizeToMxn($publico, $nativeCurrency, $defaultRateUsdMxn);
                }
            }

            if ($hasVariants) {
                $entry['combinations'] = $combinations->map(function ($combination) use ($variantItemNames) {
                    $itemIds = $combination->variants_item_ids ?? [];
                    $labelParts = collect($itemIds)
                        ->map(fn ($itemId) => $variantItemNames->get($itemId))
                        ->filter()
                        ->values();

                    $label = $labelParts->isNotEmpty()
                        ? $labelParts->implode(' / ')
                        : "SKU: {$combination->sku}";

                    return [
                        'id' => $combination->id,
                        'sku' => $combination->sku,
                        'price' => $combination->effectivePrice(),
                        'qty' => $combination->qty,
                        'label' => $label,
                    ];
                })->values();
            }

            return $entry;
        });

        return response()->json(['results' => $results]);
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['user', 'perfil', 'items', 'createdByAdmin']);

        // Cache-busting query param (the file's own mtime) so a browser that
        // already cached this exact PDF URL from before a regeneratePdf()
        // call is forced to re-fetch instead of showing a stale copy —
        // Cache-Control headers alone can't invalidate what a browser
        // already has cached from a prior response.
        $pdfUrl = null;
        if ($cotizacion->pdf_path) {
            $version = Storage::disk('public')->exists($cotizacion->pdf_path)
                ? Storage::disk('public')->lastModified($cotizacion->pdf_path)
                : time();
            $pdfUrl = route('admin.cotizaciones.pdf', $cotizacion) . '?v=' . $version;
        }

        return view('admin-ui.cotizaciones.show', compact('cotizacion', 'pdfUrl'));
    }

    /**
     * Serves the already-generated PDF directly from disk instead of a
     * Storage::url() link, so it doesn't depend on the public/storage
     * symlink existing (or being followable) on the server — mirrors
     * CotizacionController::pdf() in the customer-facing flow, minus the
     * ownership check (this is admin-only, already gated by the module's
     * own access middleware on every routes/admin.php route).
     */
    public function pdf(Cotizacion $cotizacion)
    {
        abort_unless($cotizacion->pdf_path && Storage::disk('public')->exists($cotizacion->pdf_path), 404);

        // No cache-control: the file is regenerated in place at the SAME path
        // (see regeneratePdf()), so without this the browser can keep showing
        // a stale copy from before a regeneration instead of re-fetching it.
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

    /**
     * Re-renders the PDF file in place (same folio.pdf path) from the
     * quote's already-frozen data (productos_json/subtotal/total never
     * change here — a finalized quote's numbers are historical fact) but
     * with whatever is CURRENT in the template/settings: the logo file,
     * general_settings.iva_mexico, and the bank_accounts list. Lets an
     * already-finalized quote pick up template/settings changes (e.g. this
     * PDF's own redesign) without re-finalizing or touching its data.
     */
    public function regeneratePdf(Cotizacion $cotizacion)
    {
        abort_if($cotizacion->status === 'borrador' || !$cotizacion->pdf_path, 403);

        $cotizacion->load(['items', 'user', 'perfil']);
        $cotizacion->pdf_path = $this->generateFinalizedPdf($cotizacion);
        $cotizacion->save();

        return response()->json([
            'status' => 'success',
            'message' => 'PDF regenerado con éxito.',
        ]);
    }

    /** Blank ERP-style quote builder page — no Cotizacion exists yet. */
    public function create()
    {
        return view('admin-ui.cotizaciones.create');
    }

    /**
     * Creates the draft Cotizacion "shell" for the builder: no items yet,
     * folio is only a temporary placeholder (the real 'COT-YYYY-00000' folio
     * is assigned later by the finalize step, which is a separate task).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // A quote is only ever attached to a fiscal profile explicitly — the
        // builder UI does not currently offer a control for this, so it is
        // always created without one; a later step can attach one before
        // finalizing.
        $cotizacion = new Cotizacion();
        $cotizacion->folio = 'BORRADOR-' . uniqid();
        $cotizacion->user_id = $validated['user_id'];
        $cotizacion->cotizacion_perfil_id = null;
        $cotizacion->created_by_admin_id = auth()->id();
        $cotizacion->source = 'admin';
        $cotizacion->status = 'borrador';
        $cotizacion->productos_json = [];
        $cotizacion->subtotal = 0;
        $cotizacion->total = 0;
        $cotizacion->currency = 'MXN';
        $cotizacion->save();

        return response()->json([
            'status' => 'success',
            'cotizacion' => ['id' => $cotizacion->id],
        ]);
    }

    /**
     * The builder page for an existing admin-authored draft. Only reachable
     * while the quote is still a 'borrador' and was created via this admin
     * flow — a finalized or customer-originated quote must never be editable
     * here.
     */
    public function edit(Cotizacion $cotizacion)
    {
        abort_if($cotizacion->source !== 'admin' || $cotizacion->status !== 'borrador', 403);

        $cotizacion->load(['items', 'user', 'perfil']);

        return view('admin-ui.cotizaciones.edit', compact('cotizacion'));
    }

    /** Bare form fragment for the "Agregar personalizado" modal (AU.FormModal). */
    public function manualItemFragment(Cotizacion $cotizacion)
    {
        abort_if($cotizacion->status !== 'borrador', 403);

        return view('admin-ui.cotizaciones._manual-item-form', compact('cotizacion'));
    }

    /**
     * Adds one line item to a draft quote. Body must contain exactly one of
     * product_id / product_variant_combination_id, OR neither plus a manual
     * nombre+precio_unitario (a product not in the catalog — price/name/sku/
     * modelo/marca are trusted from the client only in this manual case,
     * everything else is always resolved server-side).
     *
     * Two extra behaviors layered on top of the base "add a line" flow:
     *
     * - Price tier: if the client sends `precio_tier` (a precio_x_product_aspel
     *   cve_precio: 1=público, 2=mínimo, 3=liquidación, 4=mayorista) AND the
     *   product has that price level available, the line uses that amount
     *   (via App\Support\CotizacionPricing — no IVA, isolated from
     *   App\Support\AspelPricing/monedas_aspel on purpose) instead of
     *   Product::effectivePrice(), normalized to MXN via
     *   App\Support\CotizacionExchange if the SKU's native Aspel currency is
     *   USD (see the "Currency" note below). Combos never have tiers (no
     *   cve_art at combination level), so precio_tier is ignored there. An
     *   invalid/unavailable tier silently falls back to the normal effective
     *   price rather than erroring — the tier is a pricing *option*, not a
     *   required input.
     * - Custom price: `precio_personalizado` overrides both the tier and the
     *   effective price. Must be >= the "mínimo" tier (or >= 1 if that SKU has
     *   no mínimo tier / it's 0) — anything lower is rejected with a 422.
     *   Going above the "público" tier is allowed; the builder UI shows an
     *   informational %/amount notice for that case, nothing is enforced
     *   server-side for it.
     * - Stock split: if `cantidad` exceeds what's actually available right
     *   now, the line is split into up to two rows — one for the immediately
     *   available quantity (es_pendiente=false) and one for the remainder
     *   (es_pendiente=true). The backordered half requires a human-entered
     *   `tiempo_entrega` note (the vendor types it — no automatic ETA source
     *   exists in this system yet); if it's missing this returns a 422 with
     *   code `needs_tiempo_entrega` so the UI can prompt for it and resubmit.
     * - Currency: any price sourced from CotizacionPricing (tier or the
     *   mínimo floor for precio_personalizado) gets normalized to MXN via
     *   App\Support\CotizacionExchange when the SKU's native Aspel currency
     *   is USD, using this quote's own exchange_rate (or the Configuración
     *   General default if the quote has none yet). Frozen at add-time like
     *   everything else here: changing the quote's exchange rate later does
     *   NOT retroactively recompute already-added lines.
     * - Manual item: no product_id/product_variant_combination_id, no stock
     *   to split (always fully "disponible"), no precio_tier. The price is
     *   typed directly in the quote's CURRENT currency; if that's USD, it's
     *   stored as moneda_origen='USD'+precio_unitario_origen (raw, for the
     *   same passthrough Cotizacion::displayItemAmount() already does for
     *   Aspel tiers) plus precio_unitario normalized to MXN; if MXN, it's
     *   stored as-is with moneda_origen/precio_unitario_origen left null.
     */
    public function storeItem(Request $request, Cotizacion $cotizacion)
    {
        abort_if($cotizacion->status !== 'borrador', 403);

        $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_variant_combination_id' => ['nullable', 'integer', 'exists:product_variants_combinations,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'precio_tier' => ['nullable', 'integer', 'between:1,4'],
            'precio_personalizado' => ['nullable', 'numeric', 'min:0'],
            'tiempo_entrega' => ['nullable', 'string', 'max:255'],
            // Solo usados por la partida manual (ningún product_id/combination_id) —
            // ver rama "manual" más abajo.
            'nombre' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'precio_unitario' => ['nullable', 'numeric', 'min:0'],
        ]);

        $hasProduct = $request->filled('product_id');
        $hasCombination = $request->filled('product_variant_combination_id');
        $isManual = !$hasProduct && !$hasCombination;

        if ($hasProduct && $hasCombination) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes especificar exactamente un producto o una combinación de variante.',
            ], 422);
        }

        if ($isManual && (!$request->filled('nombre') || !$request->filled('precio_unitario'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selecciona un producto del catálogo o captura nombre y precio unitario del producto personalizado.',
            ], 422);
        }

        $cantidad = (int) $request->input('cantidad');
        $tieredPrecio = null;
        $tieredLabel = null;
        // Moneda nativa Aspel del SKU + su precio CRUDO (sin normalizar a
        // MXN) — solo se llenan en la rama de precio_tier para producto
        // simple (abajo); precio_personalizado y combinaciones los dejan
        // NULL. Sirven para el passthrough exacto de
        // Cotizacion::displayItemAmount() cuando la moneda nativa coincide
        // con la moneda de la cotización — ver ese método para el porqué.
        $monedaOrigen = null;
        $precioUnitarioOrigen = null;

        if ($hasCombination) {
            $combination = ProductVariantCombinations::with('product.brand')
                ->findOrFail($request->input('product_variant_combination_id'));
            $product = $combination->product;

            $productId = $product->id ?? null;
            $combinationId = $combination->id;
            $nombre = $product->name ?? ('Combinación #' . $combination->id);
            $sku = $combination->sku;
            $modelo = $product->productModel ?? null;
            $marca = $product->brand->name ?? null;
            $precioUnitario = $combination->effectivePrice();
            $disponible = (int) $combination->qty;
        } elseif ($hasProduct) {
            $product = Product::with('brand')->findOrFail($request->input('product_id'));

            $productId = $product->id;
            $combinationId = null;
            $nombre = $product->name;
            $sku = $product->sku;
            $modelo = $product->productModel;
            $marca = $product->brand->name ?? null;
            $precioUnitario = $product->effectivePrice();
            $disponible = $product->effectiveStock();

            // Moneda nativa del SKU en Aspel + tipo de cambio efectivo de ESTA
            // cotización (su propio override si lo tiene, si no el default de
            // Configuración General) — ver App\Support\CotizacionExchange.
            // Product::effectivePrice() (rama sin tier/personalizado, arriba)
            // NO necesita esto: ya llega en MXN vía App\Support\AspelPricing.
            // Solo los precios crudos de CotizacionPricing (tier/mínimo) lo
            // necesitan, porque esa clase lee precio_x_product_aspel tal cual,
            // sin saber en qué moneda está.
            $nativeCurrency = $product->sku ? CotizacionExchange::nativeCurrencyForSku($product->sku) : null;
            $rateUsdMxn = CotizacionExchange::effectiveUsdToMxnRate($cotizacion);

            if ($request->filled('precio_personalizado') && $product->sku) {
                $personalizado = (float) $request->input('precio_personalizado');
                $minimo = CotizacionPricing::minimoFor($product->sku);
                $piso = ($minimo === null || $minimo <= 0) ? 1.0 : $minimo;
                $piso = CotizacionExchange::normalizeToMxn($piso, $nativeCurrency, $rateUsdMxn);

                if ($personalizado < $piso) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No puedes poner un precio por debajo del precio mínimo.',
                    ], 422);
                }

                $precioUnitario = $personalizado;
                $tieredPrecio = null;
                $tieredLabel = 'Precio personalizado';
            } elseif ($request->filled('precio_tier') && $product->sku) {
                foreach (CotizacionPricing::optionsForSku($product->sku) as $tier) {
                    if ($tier['cve_precio'] === (int) $request->input('precio_tier')) {
                        $precioUnitario = CotizacionExchange::normalizeToMxn($tier['precio'], $nativeCurrency, $rateUsdMxn);
                        $tieredPrecio = $tier['cve_precio'];
                        $tieredLabel = $tier['descripcion'];
                        $monedaOrigen = $nativeCurrency;
                        $precioUnitarioOrigen = (float) $tier['precio'];
                        break;
                    }
                }
            }
        } else {
            // Partida manual: producto que no está dado de alta en el
            // catálogo. Sin product_id/combination_id, sin stock que
            // partir (ver $disponible abajo), sin precio_tier.
            $productId = null;
            $combinationId = null;
            $nombre = $request->input('nombre');
            $sku = $request->input('sku');
            $modelo = $request->input('modelo');
            $marca = $request->input('marca');

            $rawPrecio = (float) $request->input('precio_unitario');
            $rateUsdMxn = CotizacionExchange::effectiveUsdToMxnRate($cotizacion);

            if ($cotizacion->currency === 'USD') {
                // El vendedor tecleó el precio en USD (moneda ACTUAL de la
                // cotización, no viene de Aspel) -> se guarda crudo en
                // precio_unitario_origen para el passthrough exacto de
                // Cotizacion::displayItemAmount()/toDisplayUnitAmount(), y
                // se normaliza a MXN para precio_unitario (columna siempre
                // en MXN, igual que el resto del sistema).
                $precioUnitario = CotizacionExchange::normalizeToMxn($rawPrecio, 'USD', $rateUsdMxn);
                $monedaOrigen = 'USD';
                $precioUnitarioOrigen = $rawPrecio;
            } else {
                $precioUnitario = round($rawPrecio, 2);
            }

            // Sin stock que verificar: una partida manual siempre está
            // disponible de inmediato (nunca es_pendiente/tiempo_entrega).
            $disponible = $cantidad;
        }

        $disponible = max(0, $disponible);
        $pendienteQty = max(0, $cantidad - $disponible);
        $inmediataQty = $cantidad - $pendienteQty;

        if ($pendienteQty > 0 && !$request->filled('tiempo_entrega')) {
            return response()->json([
                'status' => 'error',
                'code' => 'needs_tiempo_entrega',
                'disponible' => $disponible,
                'pendiente' => $pendienteQty,
                'message' => $disponible > 0
                    ? "Solo hay {$disponible} pieza(s) disponibles ahora mismo. Indica el tiempo de entrega para las {$pendienteQty} pieza(s) restantes."
                    : "No hay piezas disponibles ahora mismo. Indica el tiempo de entrega para las {$pendienteQty} pieza(s).",
            ], 422);
        }

        $nextSortOrder = ((int) $cotizacion->items()->max('sort_order')) + 1;
        $createdItems = [];

        $baseAttributes = [
            'product_id' => $productId,
            'product_variant_combination_id' => $combinationId,
            'nombre' => $nombre,
            'sku' => $sku,
            'modelo' => $modelo,
            'marca' => $marca,
            'precio_unitario' => $precioUnitario,
            'precio_tier' => $tieredPrecio,
            'precio_tier_label' => $tieredLabel,
            'moneda_origen' => $monedaOrigen,
            'precio_unitario_origen' => $precioUnitarioOrigen,
        ];

        if ($inmediataQty > 0) {
            $createdItems[] = $cotizacion->items()->create($baseAttributes + [
                'cantidad' => $inmediataQty,
                'es_pendiente' => false,
                'tiempo_entrega' => null,
                'subtotal' => round($precioUnitario * $inmediataQty, 2),
                'sort_order' => $nextSortOrder++,
            ]);
        }

        if ($pendienteQty > 0) {
            $createdItems[] = $cotizacion->items()->create($baseAttributes + [
                'cantidad' => $pendienteQty,
                'es_pendiente' => true,
                'tiempo_entrega' => $request->input('tiempo_entrega'),
                'subtotal' => round($precioUnitario * $pendienteQty, 2),
                'sort_order' => $nextSortOrder++,
            ]);
        }

        $totals = $this->recalculateTotals($cotizacion);

        return response()->json([
            'status' => 'success',
            'items' => collect($createdItems)->map(fn (CotizacionItem $item) => [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'sku' => $item->sku,
                'modelo' => $item->modelo,
                'marca' => $item->marca,
                'precio_unitario' => (float) $item->precio_unitario,
                'precio_tier_label' => $item->precio_tier_label,
                'moneda_origen' => $item->moneda_origen,
                'precio_unitario_origen' => $item->precio_unitario_origen !== null ? (float) $item->precio_unitario_origen : null,
                'cantidad' => $item->cantidad,
                'es_pendiente' => $item->es_pendiente,
                'tiempo_entrega' => $item->tiempo_entrega,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
            'total' => $totals,
        ]);
    }

    /** Updates only the quantity of an existing line item (re-add to change product). */
    public function updateItem(Request $request, Cotizacion $cotizacion, CotizacionItem $item)
    {
        abort_if($cotizacion->status !== 'borrador', 403);
        abort_unless($item->cotizacion_id === $cotizacion->id, 404);

        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $item->cantidad = (int) $validated['cantidad'];
        $item->subtotal = round($item->precio_unitario * $item->cantidad, 2);
        $item->save();

        $totals = $this->recalculateTotals($cotizacion);

        return response()->json([
            'status' => 'success',
            'item' => [
                'id' => $item->id,
                'cantidad' => $item->cantidad,
                'subtotal' => (float) $item->subtotal,
            ],
            'total' => $totals,
        ]);
    }

    /** Removes a line item from a draft quote. */
    public function destroyItem(Cotizacion $cotizacion, CotizacionItem $item)
    {
        abort_if($cotizacion->status !== 'borrador', 403);
        abort_unless($item->cotizacion_id === $cotizacion->id, 404);

        $item->delete();

        $totals = $this->recalculateTotals($cotizacion);

        return response()->json([
            'status' => 'success',
            'total' => $totals,
        ]);
    }

    /**
     * Sets the quote's display currency (MXN/USD) and/or its two manual
     * exchange rate overrides — per-quote settings, entered by the vendor
     * (or defaulted from Configuración General, see
     * App\Support\CotizacionExchange), entirely separate from Aspel's
     * monedas_aspel rates. cotizacion_items.precio_unitario is never touched
     * by this directly: every price is always stored in raw MXN.
     *
     * `exchange_rate` (pesos por dólar, USD→MXN) is no longer cleared when
     * currency goes back to MXN — unlike before, it's now also used by
     * storeItem() to normalize USD-native SKUs to MXN regardless of which
     * currency the quote displays in, so it needs to persist independently
     * of `currency`. `exchange_rate_mxn_usd` (dólares por peso, MXN→USD) is
     * only ever read by Cotizacion::displayAmount() when currency='USD'.
     */
    public function updateCurrency(Request $request, Cotizacion $cotizacion)
    {
        abort_if($cotizacion->status !== 'borrador', 403);

        $validated = $request->validate([
            'currency' => ['sometimes', 'in:MXN,USD'],
            'exchange_rate' => ['sometimes', 'nullable', 'numeric', 'min:0.0001'],
            'exchange_rate_mxn_usd' => ['sometimes', 'nullable', 'numeric', 'min:0.0001'],
        ]);

        if (array_key_exists('currency', $validated)) {
            $cotizacion->currency = $validated['currency'];
        }
        if (array_key_exists('exchange_rate', $validated)) {
            $cotizacion->exchange_rate = $validated['exchange_rate'];
        }
        if (array_key_exists('exchange_rate_mxn_usd', $validated)) {
            $cotizacion->exchange_rate_mxn_usd = $validated['exchange_rate_mxn_usd'];
        }
        $cotizacion->save();

        return response()->json([
            'status' => 'success',
            'cotizacion' => [
                'currency' => $cotizacion->currency,
                'exchange_rate' => $cotizacion->exchange_rate ? (float) $cotizacion->exchange_rate : null,
                'exchange_rate_mxn_usd' => $cotizacion->exchange_rate_mxn_usd ? (float) $cotizacion->exchange_rate_mxn_usd : null,
            ],
        ]);
    }

    /**
     * Recomputes and persists subtotal/total from the quote's own items —
     * the server is always the source of truth for the persisted amount.
     * No tax is added on top (this project's existing tax-inclusive-pricing
     * convention), so subtotal and total are equal here.
     */
    private function recalculateTotals(Cotizacion $cotizacion): array
    {
        $subtotal = (float) $cotizacion->items()->sum('subtotal');

        $cotizacion->subtotal = $subtotal;
        $cotizacion->total = $subtotal;
        $cotizacion->save();

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'item_count' => $cotizacion->items()->count(),
        ];
    }

    /**
     * Turns an admin-authored draft into a real, immutable quote: assigns the
     * definitive folio (same scheme as the customer-facing flow), snapshots
     * cotizacion_items into productos_json in the EXACT shape
     * CotizacionController::generarCotizacion() already produces (nombre,
     * sku, modelo, marca, precio, cantidad, subtotal — note precio_unitario
     * on the items table becomes `precio` in the JSON, matching the frontend
     * convention exactly), generates the PDF, and marks the quote
     * 'finalizada'. Only a 'borrador' created via this admin builder can be
     * finalized, and only once — a second attempt is rejected by the guard
     * below because status is no longer 'borrador'.
     */
    public function finalize(Cotizacion $cotizacion)
    {
        abort_if($cotizacion->source !== 'admin' || $cotizacion->status !== 'borrador', 403);

        if ($cotizacion->items()->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes finalizar una cotización sin partidas. Agrega al menos un producto.',
            ], 422);
        }

        // Authoritative, final re-sum from the real cotizacion_items rows —
        // never trust any client-sent total.
        $this->recalculateTotals($cotizacion);
        $cotizacion->load(['items', 'user', 'perfil']);

        DB::transaction(function () use ($cotizacion) {
            $cotizacion->folio = Cotizacion::buildFolio($cotizacion->id);

            $cotizacion->productos_json = $cotizacion->items->sortBy('sort_order')->values()->map(function (CotizacionItem $item) {
                return [
                    'nombre' => $item->nombre,
                    'sku' => $item->sku,
                    'modelo' => $item->modelo,
                    'marca' => $item->marca,
                    'precio' => (float) $item->precio_unitario,
                    'precio_tier_label' => $item->precio_tier_label,
                    'moneda_origen' => $item->moneda_origen,
                    'precio_origen' => $item->precio_unitario_origen !== null ? (float) $item->precio_unitario_origen : null,
                    'cantidad' => $item->cantidad,
                    'es_pendiente' => (bool) $item->es_pendiente,
                    'tiempo_entrega' => $item->tiempo_entrega,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->all();

            $cotizacion->status = 'finalizada';
            $cotizacion->save();

            $cotizacion->pdf_path = $this->generateFinalizedPdf($cotizacion);
            $cotizacion->save();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Cotización finalizada correctamente.',
            'folio' => $cotizacion->folio,
            'redirect' => route('admin.cotizaciones.show', $cotizacion->id),
        ]);
    }

    /**
     * Mirrors CotizacionController::generarPdf() exactly: same view, same
     * setPaper() call, same storage path convention. The only difference is
     * that $perfil may be null here (an admin-authored quote is not required
     * to have a fiscal profile) — pdf.blade.php's @if($perfil) wrap covers
     * that case with a "sin datos fiscales" fallback.
     */
    private function generateFinalizedPdf(Cotizacion $cotizacion): string
    {
        Storage::disk('public')->makeDirectory('cotizaciones');

        $subtotalSinIva = round($cotizacion->total, 2);
        $ivaValue = (float) (DB::table('general_settings')->value('iva_mexico') ?? 16.00);
        $iva = round($subtotalSinIva * $ivaValue / 100, 2);
        $totalConIva = $subtotalSinIva + $iva;

        $pdf = Pdf::loadView('cotizaciones.pdf', [
            'cotizacion' => $cotizacion,
            'perfil' => $cotizacion->perfil,
            'user' => $cotizacion->user,
            'telefono' => $cotizacion->user->phone ?? '',
            'subtotalSinIva' => $subtotalSinIva,
            'iva' => $iva,
            'totalConIva' => $totalConIva,
            'ivaValue' => $ivaValue,
        ])->setPaper('letter', 'portrait');

        $filename = $cotizacion->folio . '.pdf';
        Storage::disk('public')->put('cotizaciones/' . $filename, $pdf->output());

        return 'cotizaciones/' . $filename;
    }
}
