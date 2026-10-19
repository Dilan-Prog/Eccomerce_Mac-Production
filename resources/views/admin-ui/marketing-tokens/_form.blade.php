{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $marketingApiToken->name ?? '' }}" placeholder="Ej. Flujo n8n Email Marketing" required>
        <span class="au-help-text">Solo para identificarlo en la lista — no afecta el valor del token.</span>
    </div>

    @php
        // Sin token (crear) o sin valor guardado (antes de migrar) = Email Marketing.
        $currentScope = ($marketingApiToken->scope ?? null) ?: 'marketing';
    @endphp
    <div class="au-field">
        <label class="au-label" for="au-mt-scope">Uso del token<span class="au-required-mark">*</span></label>
        <select class="au-select" name="scope" id="au-mt-scope" required>
            <option value="marketing" @selected($currentScope === 'marketing')>Email Marketing (/api/marketing/*)</option>
            <option value="attribution" @selected($currentScope === 'attribution')>Atribución Google Ads (/api/attribution/lookup)</option>
        </select>
        <span class="au-help-text">Cada token sirve solo para su uso: uno de Email Marketing no abre la API de atribución y uno de Atribución no abre las rutas de marketing.</span>
    </div>

    @if (isset($marketingApiToken))
        <div class="au-field">
            <label class="au-label">Key ID</label>
            <input type="text" class="au-input au-mono" value="{{ $marketingApiToken->key_id }}" readonly>
            <span class="au-help-text">El secreto solo se muestra una vez, al crear o regenerar el token — aquí solo se puede renombrar o revocar.</span>
        </div>

        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'description' => 'Un token revocado deja de autenticar sus rutas (/api/marketing/* o /api/attribution/lookup, según su uso) de inmediato.',
            'checked' => (int) $marketingApiToken->status === 1,
        ])
    @endif
</form>
