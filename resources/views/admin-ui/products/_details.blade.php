{{-- Bare read-only fragment injected via innerHTML by the admin-ui Products
     list's "click a row" details modal (no @extends/layout, no <script> —
     anything injected here never executes, same convention as _form.blade.php).
     Only reuses classes already defined under public/admin-ui/css/**
     (au-card, au-badge, au-table, au-flex, au-mono, au-text-right, au-btn,
     au-image-slot*, au-page-subtitle, au-help-text). --}}
@php
    $statusTone = (int) $product->status === 1 ? 'success' : 'critical';
    $statusLabel = (int) $product->status === 1 ? 'Activo' : 'Inactivo';
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
                    @if ($product->offert_price > 0)
                        <tr>
                            <td>Precio de oferta {{ (int) $product->price_offert_personalizated === 1 ? '(personalizado)' : '(Aspel)' }}</td>
                            <td class="au-text-right au-mono">${{ number_format((float) $product->offert_price, 2) }}</td>
                            <td class="au-text-right au-mono">${{ number_format((float) $effectiveOffertPrice, 2) }}</td>
                        </tr>
                    @endif
                    @if ($aspelTierLabel)
                        <tr>
                            <td>Tier Aspel</td>
                            <td class="au-text-right" colspan="2">{{ $aspelTierLabel }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Cantidad {{ (int) $product->qty_personalizated === 0 ? '(desde Aspel)' : '(manual)' }}</td>
                        <td class="au-text-right au-mono">{{ (int) $product->qty }}</td>
                        <td class="au-text-right au-mono">{{ (int) $effectiveStock }}</td>
                    </tr>
                    <tr>
                        <td>Cantidad Aspel</td>
                        <td class="au-text-right au-mono" colspan="2">{{ (int) $product->qty_aspel }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($product->offert_price > 0)
            <div class="au-card-body" style="border-top:1px solid var(--au-border);font-size:12.5px;color:var(--au-text-subdued)">
                Vigencia de oferta: {{ $product->offer_start_date ?: '—' }} &rarr; {{ $product->offer_end_date ?: '—' }}
            </div>
        @endif
    </div>

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
