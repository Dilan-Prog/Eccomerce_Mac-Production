@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Cotización {{ $cotizacion->folio }}
@endsection

@push('styles')
<style>
.cot-page { padding: 32px 0 64px; background: var(--gris-fondo); }
/* MODAL */
.cot-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    z-index: 1000; display: flex; align-items: center; justify-content: center;
    padding: 16px;
}
.cot-modal {
    background: var(--blanco); border-radius: 12px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.3);
    width: 100%; max-width: 860px;
    max-height: 92vh; display: flex; flex-direction: column;
    overflow: hidden;
}
.cot-modal-header {
    padding: 20px 24px; border-bottom: 1px solid var(--gris-borde);
    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
}
.cot-modal-header h3 {
    font-size: 18px; font-weight: 800; color: var(--azul-principal); margin: 0;
}
.cot-modal-folio {
    font-size: 13px; font-weight: 700;
    background: var(--azul-claro); color: var(--azul-principal);
    padding: 4px 12px; border-radius: 20px; margin-left: 12px;
}
.cot-modal-close {
    background: none; border: none; font-size: 22px; cursor: pointer;
    color: var(--gris-claro-texto); line-height: 1; padding: 4px;
    transition: color 0.2s;
}
.cot-modal-close:hover { color: var(--azul-principal); }
.cot-modal-body {
    flex: 1; overflow: hidden; padding: 0;
}
.cot-modal-body iframe {
    width: 100%; height: 100%; min-height: 52vh; border: none; display: block;
}
.cot-modal-footer {
    padding: 16px 24px; border-top: 1px solid var(--gris-borde);
    display: flex; gap: 10px; flex-shrink: 0; flex-wrap: wrap;
}
.btn-download {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px; background: var(--accent-cta); color: var(--blanco);
    border: none; border-radius: 8px; font-size: 14px; font-weight: 800;
    cursor: pointer; text-decoration: none; transition: background 0.2s;
}
.btn-download:hover { background: var(--accent-cta-hover); color: var(--blanco); }
.btn-close-modal {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 20px; background: var(--gris-fondo);
    border: 1.5px solid var(--gris-borde); border-radius: 8px;
    font-size: 14px; font-weight: 700; color: var(--azul-principal);
    cursor: pointer; text-decoration: none; transition: all 0.2s; white-space: nowrap;
}
.btn-close-modal:hover { background: var(--azul-claro); border-color: var(--azul-principal); color: var(--azul-principal); }

/* Página de fondo mientras el modal está abierto */
.cot-success-bg {
    text-align: center; padding: 64px 20px;
}
.cot-success-icon {
    width: 80px; height: 80px; background: #F0FDF4; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
    color: #2F855A; font-size: 36px;
}

@media (max-width: 600px) {
    .cot-modal { max-height: 98vh; }
    .cot-modal-body iframe { min-height: 60vh; }
    .cot-modal-footer { flex-direction: column; }
    .btn-download, .btn-close-modal { width: 100%; }
}
</style>
@endpush

@section('content')

<section class="cot-page">
    <div class="container">
        <div class="cot-success-bg">
            <div class="cot-success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="font-size:24px;font-weight:800;color:var(--azul-principal);margin-bottom:8px;">
                Tu cotización formal está lista
            </h2>
            <p style="font-size:15px;color:var(--gris-claro-texto);">
                Cargando vista previa del PDF…
            </p>
        </div>
    </div>
</section>

{{-- MODAL --}}
<div class="cot-modal-overlay" id="cotModal">
    <div class="cot-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="cot-modal-header">
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
                <h3 id="modalTitle">
                    <i class="fas fa-file-pdf" style="color:var(--accent-cta);margin-right:8px;"></i>
                    Tu cotización formal está lista
                </h3>
                <span class="cot-modal-folio">{{ $cotizacion->folio }}</span>
            </div>
            <button type="button" class="cot-modal-close" id="closeModalBtn" aria-label="Cerrar">×</button>
        </div>

        <div class="cot-modal-body">
            @if($pdfUrl)
                <iframe src="{{ $pdfUrl }}" title="Cotización {{ $cotizacion->folio }}"></iframe>
            @else
                <div style="padding:40px;text-align:center;color:var(--gris-claro-texto);">
                    <i class="fas fa-exclamation-circle" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                    No se pudo cargar la vista previa del PDF.
                </div>
            @endif
        </div>

        <div class="cot-modal-footer">
            @if($pdfUrl)
            <a href="{{ $pdfUrl }}" download="{{ $cotizacion->folio }}.pdf" class="btn-download">
                <i class="fas fa-download"></i>
                Descargar PDF
            </a>
            @endif
            <a href="{{ route('cart-details') }}" class="btn-close-modal" id="closeModalLink">
                <i class="fas fa-times"></i>
                Cerrar
            </a>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var modal      = document.getElementById('cotModal');
    var closeBtn   = document.getElementById('closeModalBtn');
    var closeLink  = document.getElementById('closeModalLink');

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.body.style.overflow = 'hidden';

    closeBtn.addEventListener('click', closeModal);

    // Clic fuera del modal cierra
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ESC cierra
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>
@endpush
