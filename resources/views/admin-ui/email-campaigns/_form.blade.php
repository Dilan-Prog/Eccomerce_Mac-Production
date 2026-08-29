{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
@php
    $campaignTypeLabels = ['individual' => 'Individual', 'campaign' => 'Campaña', 'sequence' => 'Secuencia'];
@endphp
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $emailCampaign->name ?? '' }}" placeholder="Ej. Promoción de agosto — clientes 2026" required>
        <span class="au-help-text">Solo para identificarla en la lista de campañas.</span>
    </div>

    <div class="au-field">
        <label class="au-label">Plantilla<span class="au-required-mark">*</span></label>
        <select class="au-select" name="email_template_id" required>
            <option value="">Selecciona una plantilla…</option>
            @foreach ($templates as $template)
                <option value="{{ $template->id }}" {{ (int) ($emailCampaign->email_template_id ?? 0) === $template->id ? 'selected' : '' }}>
                    {{ $template->name }} ({{ $campaignTypeLabels[$template->type] ?? 'Individual' }})
                </option>
            @endforeach
        </select>
        <span class="au-help-text">Se listan todas las plantillas activas: el "Tipo" es solo una etiqueta para encontrarlas, cualquiera funciona en una campaña.</span>
    </div>

    <div class="au-field">
        <label class="au-label">Lista de contactos<span class="au-required-mark">*</span></label>
        <select class="au-select" name="email_contact_list_id" required>
            <option value="">Selecciona una lista…</option>
            @foreach ($lists as $list)
                <option value="{{ $list->id }}" {{ (int) ($emailCampaign->email_contact_list_id ?? 0) === $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
            @endforeach
        </select>
        <span class="au-help-text">Los destinatarios se congelan al programar la campaña, no ahora — editar la lista después no cambia el envío ya programado.</span>
    </div>

    <div class="au-field">
        <label class="au-label">Fecha programada</label>
        <input type="datetime-local" class="au-input" name="scheduled_at" value="{{ isset($emailCampaign) && $emailCampaign->scheduled_at ? $emailCampaign->scheduled_at->format('Y-m-d\TH:i') : '' }}">
        <span class="au-help-text">Déjala vacía para que salga en cuanto n8n pregunte por campañas pendientes. Con fecha, la campaña no aparece como pendiente hasta que esa fecha pase. La hora exacta del envío la decide n8n, no este panel.</span>
    </div>

    <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        La campaña se guarda como <strong>borrador</strong>. Todavía no se le envía nada a nadie: hay que darle "Programar" desde la lista de campañas, que es cuando se congela la lista de destinatarios.
    </div>
</form>
