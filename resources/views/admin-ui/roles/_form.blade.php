{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
@php
    $isSystemRole = isset($role) && $role->is_system;
    $selectedModuleKeys = isset($role) ? $role->allowedModuleKeys() : [];
    $granularKeys = \App\Models\RoleModulePermission::GRANULAR_MODULE_KEYS;
    $actionLabels = \App\Models\RoleModulePermission::GRANULAR_ACTION_LABELS;
    $granularPermissions = isset($role)
        ? $role->permissions()->whereIn('module_key', $granularKeys)->get()->keyBy('module_key')
        : collect();
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
                @continue(in_array($moduleKey, $granularKeys, true))
                <label class="au-flex au-gap-2" style="align-items:center;font-weight:400;">
                    <input class="au-checkbox" type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                        {{ in_array($moduleKey, $selectedModuleKeys, true) ? 'checked' : '' }}
                        {{ $isSystemRole ? 'disabled' : '' }}>
                    {{ $moduleLabel }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">Permisos de Aspel (por acción)</label>
        @foreach ($granularKeys as $moduleKey)
            @php $perm = $granularPermissions->get($moduleKey); @endphp
            <div class="au-flex au-gap-2" style="align-items:center;margin-bottom:6px;">
                <strong style="min-width:180px;">{{ config('admin-modules')[$moduleKey] }}</strong>
                @foreach ($actionLabels as $action => $label)
                    <label class="au-flex au-gap-2" style="align-items:center;font-weight:400;">
                        <input class="au-checkbox" type="checkbox"
                            name="module_actions[{{ $moduleKey }}][{{ $action }}]" value="1"
                            {{ $perm && $perm->{'can_' . $action} ? 'checked' : '' }}
                            {{ $isSystemRole ? 'disabled' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>
</form>
