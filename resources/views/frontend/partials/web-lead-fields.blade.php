{{-- Campos tecnicos de los formularios publicos de solicitud (se envian a route('web-leads.store')).
     Incluir al FINAL del <form>: @include('frontend.partials.web-lead-fields', ['formType' => 'cotizacion'])
     - form_type: identifica el formulario (asociado | calibracion_puesta | medicion | cotizacion).
     - website: honeypot anti-spam (debe llegar vacio).
     - form_started_at: marca de tiempo del render (el backend descarta envios demasiado rapidos).
     - Hidden de origen/atribucion: los rellena public/frontend/js/web-lead-form.js (si falla, quedan vacios). --}}
@csrf
<input type="hidden" name="form_type" value="{{ $formType }}">
<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label>No llenar este campo
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true">
    </label>
</div>
<input type="hidden" name="form_started_at" value="{{ time() }}">
<input type="hidden" name="source_page" value="">
<input type="hidden" name="landing_page" value="">
<input type="hidden" name="referrer" value="">
<input type="hidden" name="session_id" value="">
<input type="hidden" name="gclid" value="">
<input type="hidden" name="gbraid" value="">
<input type="hidden" name="wbraid" value="">
<input type="hidden" name="fbclid" value="">
<input type="hidden" name="utm_source" value="">
<input type="hidden" name="utm_medium" value="">
<input type="hidden" name="utm_campaign" value="">
<input type="hidden" name="utm_term" value="">
<input type="hidden" name="utm_content" value="">
@once
<script src="{{ asset('frontend/js/web-lead-form.js') }}?v={{ @filemtime(public_path('frontend/js/web-lead-form.js')) }}" defer></script>
@endonce
