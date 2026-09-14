{{-- Segundo carrusel: la línea McDonnell & Miller completa (una tarjeta por
     landing de resources/data/br/mcdonnell). $actual = slug a excluir cuando
     se muestra dentro de una de esas landings. Mismo estilo que
     'relacionados', reutiliza su CSS y su JS. --}}
@php
    $lineaMM = \Illuminate\Support\Facades\Cache::remember('br_linea_mcdonnell', 3600, function () {
        $datos = collect(glob(resource_path('data/br/mcdonnell/*.php')))
            ->mapWithKeys(fn ($f) => [basename($f, '.php') => require $f]);

        // Portada de cada producto en una sola consulta; la landing de marca
        // sirve de imagen de respaldo si el producto no tiene foto.
        $fotos = \App\Models\Product::whereIn('slug', $datos->pluck('catalogo_slug'))
            ->pluck('thumb_image', 'slug');
        $respaldo = config('brasil.landings.mcdonnell-miller.imagen');

        return $datos->map(fn ($d) => [
            'sku' => $d['sku'],
            'nombre' => $d['nombre'],
            'relacao' => $d['relacao'] ?? '',
            'imagen' => $fotos[$d['catalogo_slug']] ?? $respaldo,
        ])->sortBy('sku');
    });
    $tarjetasMM = $lineaMM->except($actual ?? '');
@endphp
@if($tarjetasMM->isNotEmpty())
<section class="br-rel" aria-labelledby="br-rel-mm-titulo">
    <div class="br-rel-head">
        <div>
            <h2 id="br-rel-mm-titulo">Linha McDonnell &amp; Miller</h2>
            <p class="br-sub" style="margin:6px 0 0">Controles de nível, peças de reposição e interruptores de fluxo para caldeiras. <a href="{{ route('br.mcdonnell-miller') }}">Ver visão geral da marca ›</a></p>
        </div>
        <div class="br-rel-nav" aria-hidden="true">
            <button type="button" data-dir="prev" aria-label="Anterior">‹</button>
            <button type="button" data-dir="next" aria-label="Próximo">›</button>
        </div>
    </div>
    <div class="br-rel-track">
        @foreach($tarjetasMM as $slugMM => $t)
            <a href="{{ route('br.mcdonnell-miller.produto', $slugMM) }}" class="br-rel-card">
                <div class="br-rel-img">
                    <img src="{{ $t['imagen'] }}" alt="{{ $t['nombre'] }}" loading="lazy" width="240" height="140">
                </div>
                <div class="br-rel-body">
                    <span class="br-rel-marca">McDonnell &amp; Miller · {{ $t['sku'] }}</span>
                    <h3>{{ $t['nombre'] }}</h3>
                    @if($t['relacao'] !== '')<p>{{ $t['relacao'] }}</p>@endif
                    <span class="br-rel-link">Ver detalhes ›</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif
