{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form.
     Field list mirrors UserManageController's actual validation rules
     (name/last_name/company/rfc/phone/email/password), plus the `role`
     select now covers all 3 staff roles (admin/associate/technician —
     technician previously had no creation UI at all). "Vendedor" was
     removed from this select (and from STAFF_ROLES/ROLE_LABELS in
     StaffUserController) — it mapped to a dead, separately-retired legacy
     vendor panel and only confused admins who meant to pick Administrador +
     a custom "Rol personalizado" instead.

     "Rol personalizado" only means anything when role=admin (see
     User::customRole() / role_id). Rather than always showing it with a
     disclaimer, it's shown/hidden via a plain inline onchange handler on the
     role select — same technique category/subcategory cascading already
     uses in admin-ui/products/_form.blade.php — since <script> tags injected
     via innerHTML by form-modal.js do not execute. The backend independently
     ignores/nulls role_id server-side whenever role !== 'admin' regardless
     of what is submitted here, so this is UX only, not the actual guard. --}}
<form>
    @csrf
    <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
        <div class="au-form-grid-3">
            <div class="au-field">
                <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
                <input type="text" placeholder="Juan Perez" class="au-input" name="name" value="{{ $staffUser->name ?? '' }}" required>
            </div>
            <div class="au-field">
                <label class="au-label">Apellido</label>
                <input type="text" placeholder="Benitez Aragon" class="au-input" name="last_name" value="{{ $staffUser->last_name ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">Empresa</label>
                <input type="text" placeholder="Honeywell" class="au-input" name="company" value="{{ $staffUser->company ?? '' }}">
            </div>
        </div>

        <div class="au-form-grid-3">
            <div class="au-field">
                <label class="au-label">RFC</label>
                <input type="text" placeholder="RFC123456789" class="au-input" name="rfc" value="{{ $staffUser->rfc ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">Telefono</label>
                <input type="text" placeholder="555-123-4567" class="au-input" name="phone" value="{{ $staffUser->phone ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">Correo Electrónico<span class="au-required-mark">*</span></label>
                <input type="email" placeholder="correo@ejemplo.com" class="au-input" name="email" value="{{ $staffUser->email ?? '' }}" required>
            </div>
        </div>

        <div class="au-form-grid-3">
            <div class="au-field">
                <label class="au-label">Rol<span class="au-required-mark">*</span></label>
                <select class="au-select" name="role" required onchange="
                    var wrap = this.form.querySelector('[data-role-id-wrap]');
                    if (wrap) { wrap.style.display = this.value === 'admin' ? '' : 'none'; }
                ">
                    <option value="">Seleccionar</option>
                    <option value="admin" {{ isset($staffUser) && $staffUser->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="associate" {{ isset($staffUser) && $staffUser->role === 'associate' ? 'selected' : '' }}>Asociado</option>
                    <option value="technician" {{ isset($staffUser) && $staffUser->role === 'technician' ? 'selected' : '' }}>Técnico</option>
                </select>
            </div>
            <div class="au-field" data-role-id-wrap style="{{ (isset($staffUser) ? $staffUser->role : null) === 'admin' ? '' : 'display:none' }}">
                <label class="au-label">Rol personalizado</label>
                <select class="au-select" name="role_id">
                    <option value="">Sin rol personalizado (acceso completo)</option>
                    @foreach ($customRoles as $customRole)
                        <option value="{{ $customRole->id }}" {{ isset($staffUser) && (int) $staffUser->role_id === $customRole->id ? 'selected' : '' }}>{{ $customRole->name }}</option>
                    @endforeach
                </select>
                <span class="au-help-text">Solo aplica si el rol es Administrador.</span>
            </div>
        </div>

        <div class="au-form-grid-3">
            <div class="au-field">
                <label class="au-label">Contraseña{{ isset($staffUser) ? '' : '*' }}</label>
                <input type="password" placeholder="**************" class="au-input" name="password" {{ isset($staffUser) ? '' : 'required' }}>
                @if (isset($staffUser))
                    <span class="au-help-text">Deja en blanco para mantener la contraseña actual.</span>
                @endif
            </div>
            <div class="au-field">
                <label class="au-label">Confirmar Contraseña{{ isset($staffUser) ? '' : '*' }}</label>
                <input type="password" placeholder="**************" class="au-input" name="password_confirmation" {{ isset($staffUser) ? '' : 'required' }}>
            </div>
        </div>

        @include('admin-ui.partials._toggle-field', ['name' => 'status', 'label' => 'Estado', 'checked' => isset($staffUser) ? $staffUser->status === 'active' : true])
    </div>
</form>
