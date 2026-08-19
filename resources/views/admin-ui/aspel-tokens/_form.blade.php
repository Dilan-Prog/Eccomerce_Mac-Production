{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $aspelApiToken->name ?? '' }}" placeholder="Ej. Servidor Aspel Producción" required>
        <span class="au-help-text">Solo para identificarlo en la lista — no afecta el valor del token.</span>
    </div>

    @if (isset($aspelApiToken))
        <div class="au-field">
            <label class="au-label">Key ID</label>
            <input type="text" class="au-input au-mono" value="{{ $aspelApiToken->key_id }}" readonly>
            <span class="au-help-text">El secreto solo se muestra una vez, al crear o regenerar el token — aquí solo se puede renombrar o revocar.</span>
        </div>

        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'description' => 'Un token revocado deja de autenticar las rutas /api/aspel/* de inmediato.',
            'checked' => (int) $aspelApiToken->status === 1,
        ])
    @endif
</form>
