{{-- Aviso de resultado del formulario de solicitud. Incluir como PRIMER hijo del <form class="row ..."> (dentro del form para no alterar el layout flex de la columna):
     @include('frontend.partials.web-lead-alert', ['formType' => 'cotizacion'])
     El formulario debe tener id="web-lead-form" (ancla a la que se hace scroll). --}}
@php
    $wlFlashForm = session('web_lead_form');
    $wlOk = session('web_lead_status') === 'ok' && (empty($wlFlashForm) || $wlFlashForm === ($formType ?? null));
    $wlErr = ! $wlOk && isset($errors) && $errors->any() && old('form_type', $formType ?? null) === ($formType ?? null);
@endphp
@if($wlOk)
    <div class="col-12">
        <div class="alert alert-success mb-0" role="status" data-web-lead-alert>
            &iexcl;Recibimos tu solicitud! Te contactaremos pronto.
        </div>
    </div>
@elseif($wlErr)
    <div class="col-12">
        <div class="alert alert-danger mb-0" role="alert" data-web-lead-alert>
            Revisa los campos marcados e intenta de nuevo.
        </div>
    </div>
@endif
@if($wlOk || $wlErr)
@once
<script>
(function () {
    function go() {
        try {
            var el = document.getElementById('web-lead-form') || document.querySelector('[data-web-lead-alert]');
            if (el && el.scrollIntoView) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        } catch (e) { /* sin scroll: el aviso sigue visible en el formulario */ }
    }
    if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
})();
</script>
@endonce
@endif
