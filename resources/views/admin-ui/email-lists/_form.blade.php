{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $emailContactList->name ?? '' }}" placeholder="Ej. Clientes que compraron en 2026" required>
        <span class="au-help-text">Solo para identificarla al armar una campaña.</span>
    </div>

    <div class="au-field">
        <label class="au-label">Descripción</label>
        <textarea class="au-input" name="description" rows="3" placeholder="Opcional — para qué sirve esta lista.">{{ $emailContactList->description ?? '' }}</textarea>
    </div>

    @if (isset($emailContactList))
        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'description' => 'Una lista inactiva no aparece al crear campañas nuevas. Las campañas ya creadas con ella no se ven afectadas.',
            'checked' => (int) $emailContactList->status === 1,
        ])
    @else
        <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
            La lista se crea vacía. Después, desde "Ver contactos", puedes importar clientes del sitio, clientes de Aspel o agregarlos a mano.
        </div>
    @endif
</form>
