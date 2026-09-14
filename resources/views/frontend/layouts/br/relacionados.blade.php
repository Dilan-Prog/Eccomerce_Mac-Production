{{-- Carrusel "Você também pode se interessar". Muestra todas las landings de
     config('brasil.landings') menos la actual ($actual = clave). --}}
@php
    $relacionadas = collect(config('brasil.landings'))->except($actual ?? '');
@endphp
@if($relacionadas->isNotEmpty())
<section class="br-rel" aria-labelledby="br-rel-titulo">
    <div class="br-rel-head">
        <div>
            <h2 id="br-rel-titulo">Você também pode se interessar</h2>
            <p class="br-sub" style="margin:6px 0 0">Outros controladores e equipamentos que atendemos no Brasil.</p>
        </div>
        <div class="br-rel-nav" aria-hidden="true">
            <button type="button" data-dir="prev" aria-label="Anterior">‹</button>
            <button type="button" data-dir="next" aria-label="Próximo">›</button>
        </div>
    </div>
    <div class="br-rel-track">
        @foreach($relacionadas as $clave => $l)
            <a href="{{ route($l['ruta']) }}" class="br-rel-card">
                <div class="br-rel-img">
                    <img src="{{ $l['imagen'] }}" alt="{{ $l['marca'] }} {{ $l['nombre'] }}" loading="lazy" width="240" height="140">
                </div>
                <div class="br-rel-body">
                    <span class="br-rel-marca">{{ $l['marca'] }}</span>
                    <h3>{{ $l['nombre'] }}</h3>
                    <p>{{ $l['resumen'] }}</p>
                    <span class="br-rel-link">Ver especificações ›</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif
