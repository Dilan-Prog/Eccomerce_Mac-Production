{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
@php
    $isSystemRole = isset($role) && $role->is_system;
    $selectedModuleKeys = isset($role) ? $role->allowedModuleKeys() : [];
@endphp
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $role->name ?? '' }}" required {{ $isSystemRole ? 'readonly' : '' }}>
    </div>

    <div class="au-field">
        <label class="au-label">Módulos permitidos</label>
        @if ($isSystemRole)
            <span class="au-help-text">Los roles del sistema tienen acceso completo y no se pueden editar.</span>
        @endif
        <div class="au-form-grid-3">
            @foreach (config('admin-modules') as $moduleKey => $moduleLabel)
                <label class="au-flex au-gap-2" style="align-items:center;font-weight:400;">
                    <input class="au-checkbox" type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                        {{ in_array($moduleKey, $selectedModuleKeys, true) ? 'checked' : '' }}
                        {{ $isSystemRole ? 'disabled' : '' }}>
                    {{ $moduleLabel }}
                </label>
            @endforeach
        </div>
    </div>
</form>
