{{-- Bare read-only fragment injected via innerHTML by the admin-ui Products
     list's "click a row" details modal (no @extends/layout, no <script> —
     anything injected here never executes, same convention as _form.blade.php).
     Only reuses classes already defined under public/admin-ui/css/**
     (au-card, au-badge, au-table, au-flex, au-mono, au-text-right, au-btn,
     au-image-slot*, au-page-subtitle, au-help-text). --}}
@php
    $statusTone = (int) $product->status === 1 ? 'success' : 'critical';
    $statusLabel = (int) $product->status === 1 ? 'Activo' : 'Inactivo';
    $productTypeLabels = [
        'new_arrival' => 'Nuevo',
        'featured_product' => 'Producto Destacado',
        'top_product' => 'Más Buscado',
        'best_product' => 'Más Vendido',
    ];
@endphp
<div style="display:flex;flex-direction:column;gap:16px;min-width:0">

    <div class="au-card">
        <div class="au-card-body" style="display:flex;gap:16px;align-items:flex-start">
            <div class="au-image-slot has-image" style="width:140px;height:140px;flex:none">
                @if ($product->thumb_image)
                    <span class="au-image-slot-preview" style="background-image:url('{{ asset($product->thumb_image) }}')"></span>
                @else
                    <div class="au-image-slot-empty">
                        <div class="au-image-slot-empty-icon"><i class="fas fa-image"></i></div>
                    </div>
                @endif
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:16px;font-weight:700;color:var(--au-text)">{{ $product->name }}</div>
                <div class="au-flex" style="gap:8px;flex-wrap:wrap;margin-top:6px">
                    <span class="au-badge au-badge-{{ $statusTone }}"><span class="au-badge-dot"></span>{{ $statusLabel }}</span>
                    @if ($hasActiveOffer)
                        <span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Oferta activa</span>
                    @endif
                    @if (!empty($product->product_type) && isset($productTypeLabels[$product->product_type]))
                        <span class="au-badge au-badge-info"><span class="au-badge-dot"></span>{{ $productTypeLabels[$product->product_type] }}</span>
                    @endif
                </div>
                <div style="margin-top:10px;font-size:13px;color:var(--au-text-subdued);line-height:1.8">
                    <div><strong style="color:var(--au-text)">SKU:</strong> <span class="au-mono">{{ $product->sku ?: '—' }}</span></div>
                    <div><strong style="color:var(--au-text)">Modelo:</strong> {{ $product->productModel ?: '—' }}</div>
                    <div><strong style="color:var(--au-text)">Marca:</strong> {{ $brand->name ?? '—' }}</div>
                    <div>
                        <strong style="color:var(--au-text)">Categoría:</strong>
                        {{ $category->name ?? '—' }}
                        @if ($subCategory)
                            &raquo; {{ $subCategory->name }}
                        @endif
                        @if ($childCategory)
                            &raquo; {{ $childCategory->name }}
                        @endif
                    </div>
                    @if ($product->url_PDF)
                        <div><strong style="color:var(--au-text)">Ficha técnica:</strong> <a href="{{ $product->url_PDF }}" target="_blank" rel="noopener">Ver documento</a></div>
                    @endif
                    @if ($product->video_link)
                        <div><strong style="color:var(--au-text)">Video:</strong> <a href="{{ $product->video_link }}" target="_blank" rel="noopener">Ver video</a></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Precio y existencias</div>
        </div>
        <div class="au-table-wrap">
            <table class="au-table">
                <thead>
                    <tr>
                        <th>Campo</th>
                        <th class="au-text-right">Valor guardado</th>
                        <th class="au-text-right">Valor efectivo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Precio {{ (int) $product->price_personalizated === 1 ? '(personalizado)' : '(Aspel)' }}</td>
                        <td class="au-text-right au-mono">${{ number_format((float) $product->price, 2) }}</td>
                        <td class="au-text-right au-mono">${{ number_format((float) $effectivePrice, 2) }}</td>
                    </tr>
                    @if ($aspelTierLabel)
                        <tr>
                            <td>Tier Aspel (precio)</td>
                            <td class="au-text-right" colspan="2">{{ $aspelTierLabel }}</td>
                        </tr>
                    @endif
                    @if ($product->offert_price > 0)
                        <tr>
                            <td>Precio de oferta {{ (int) $product->price_offert_personalizated === 1 ? '(personalizado)' : '(Aspel)' }}</td>
                            <td class="au-text-right au-mono">${{ number_format((float) $product->offert_price, 2) }}</td>
                            <td class="au-text-right au-mono">${{ number_format((float) $effectiveOffertPrice, 2) }}</td>
                        </tr>
                    @endif
                    @if ($aspelOffertTierLabel)
                        <tr>
                            <td>Tier Aspel (oferta)</td>
                            <td class="au-text-right" colspan="2">{{ $aspelOffertTierLabel }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Cantidad {{ (int) $product->qty_personalizated === 0 ? '(desde Aspel)' : '(manual)' }}</td>
                        <td class="au-text-right au-mono">{{ (int) $product->qty }}</td>
                        <td class="au-text-right au-mono">{{ (int) $effectiveStock }}</td>
                    </tr>
                    <tr>
                        <td>Cantidad Aspel (columna sincronizada)</td>
                        <td class="au-text-right au-mono" colspan="2">{{ (int) $product->qty_aspel }}</td>
                    </tr>
                    @if ($aspelSync)
                        <tr>
                            <td>Existencia real Aspel (en vivo)</td>
                            <td class="au-text-right au-mono" colspan="2">{{ number_format((float) $aspelSync->exist, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Costo promedio</td>
                            <td class="au-text-right au-mono" colspan="2">${{ number_format((float) $aspelSync->costo_prom, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Último costo</td>
                            <td class="au-text-right au-mono" colspan="2">${{ number_format((float) $aspelSync->ult_costo, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Moneda Aspel</td>
                            <td class="au-text-right" colspan="2">{{ $aspelCurrency->cve_moned ?? 'MXN' }} ({{ $aspelCurrency->simbolo ?? '$' }})</td>
                        </tr>
                        @if ($aspelSync->fch_ultcom)
                            <tr>
                                <td>Última compra</td>
                                <td class="au-text-right" colspan="2">{{ $aspelSync->fch_ultcom->format('d/m/Y') }}</td>
                            </tr>
                        @endif
                        @if ($aspelSync->fch_ultvta)
                            <tr>
                                <td>Última venta</td>
                                <td class="au-text-right" colspan="2">{{ $aspelSync->fch_ultvta->format('d/m/Y') }}</td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
        @if ($product->offert_price > 0)
            <div class="au-card-body" style="border-top:1px solid var(--au-border);font-size:12.5px;color:var(--au-text-subdued)">
                Vigencia de oferta: {{ $product->offer_start_date ?: '—' }} &rarr; {{ $product->offer_end_date ?: '—' }}
            </div>
        @endif
    </div>

    @if (count($aspelPriceOptions))
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Niveles de precio Aspel</div>
            </div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>Nivel</th>
                            <th class="au-text-right">Precio (IVA incl.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aspelPriceOptions as $tier)
                            <tr>
                                <td>{{ $tier['descripcion'] }}</td>
                                <td class="au-text-right au-mono">${{ number_format($tier['with_iva'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($combinations->count())
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Variantes / Combinaciones</div>
            </div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>Variante</th>
                            <th>SKU</th>
                            <th class="au-text-right">Precio</th>
                            <th class="au-text-right">Oferta</th>
                            <th class="au-text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($combinations as $combination)
                            <tr>
                                <td>{{ $combination['label'] }}</td>
                                <td><span class="au-mono">{{ $combination['sku'] ?: '—' }}</span></td>
                                <td class="au-text-right au-mono">${{ number_format($combination['price'], 2) }}</td>
                                <td class="au-text-right au-mono">{{ $combination['offert_price'] !== null ? '$' . number_format($combination['offert_price'], 2) : '—' }}</td>
                                <td class="au-text-right au-mono">{{ $combination['qty'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Garantía</div>
        </div>
        <div class="au-card-body" style="font-size:13px;color:var(--au-text-subdued);white-space:pre-line">{{ $product->short_description }}</div>
    </div>

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Descripción larga</div>
        </div>
        <div class="au-card-body" style="font-size:13px;color:var(--au-text-subdued)">
            {!! $product->long_description !!}
        </div>
    </div>

    @if ($product->seo_title || $product->seo_description || $product->canonical_url)
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">SEO / Marketing</div>
            </div>
            <div class="au-card-body" style="font-size:13px;color:var(--au-text-subdued);line-height:1.8">
                @if ($product->seo_title)
                    <div><strong style="color:var(--au-text)">SEO Título:</strong> {{ $product->seo_title }}</div>
                @endif
                @if ($product->seo_description)
                    <div><strong style="color:var(--au-text)">SEO Descripción:</strong> {{ $product->seo_description }}</div>
                @endif
                @if ($product->canonical_url)
                    <div><strong style="color:var(--au-text)">URL Canónica:</strong> {{ $product->canonical_url }} {{ (int) $product->is_canonical === 1 ? '(activa)' : '(inactiva)' }}</div>
                @endif
            </div>
        </div>
    @endif

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Galería del producto</div>
        </div>
        <div class="au-card-body">
            @if ($galleryImages->count())
                <div class="au-image-slot-extra-grid" style="grid-template-columns:repeat(auto-fill, minmax(90px, 1fr))">
                    @foreach ($galleryImages as $galleryImage)
                        <div class="au-image-slot has-image">
                            <span class="au-image-slot-preview" style="background-image:url('{{ asset($galleryImage->image) }}')"></span>
                        </div>
                    @endforeach
                </div>
            @else
                <span class="au-help-text">Este producto no tiene imágenes de galería.</span>
            @endif
        </div>
    </div>
</div>
