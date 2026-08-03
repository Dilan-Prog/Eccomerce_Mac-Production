{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $customer->name ?? '' }}" required>
    </div>
    <div class="au-field">
        <label class="au-label" data-email-label>Correo electrónico<span class="au-required-mark" data-email-required-mark>*</span></label>
        <input type="email" class="au-input" name="email" value="{{ $customer->email ?? '' }}" data-contact-field required>
    </div>
    <div class="au-field">
        <label class="au-label">Teléfono</label>
        <input type="text" class="au-input" name="phone" value="{{ $customer->phone ?? '' }}" data-contact-field>
    </div>
    <div class="au-field">
        <label class="au-label">Empresa</label>
        <input type="text" class="au-input" name="company" value="{{ $customer->company ?? '' }}" data-contact-field>
    </div>
    <div class="au-field">
        <label class="au-label">RFC</label>
        <input type="text" class="au-input" name="rfc" value="{{ $customer->rfc ?? '' }}" data-contact-field>
    </div>
    @unless (isset($customer))
        {{-- Solo al crear: permite avanzar sin correo/RFC/teléfono/empresa
             capturados todavía (ej. armando una cotización rápido y el
             vendedor no trae esos datos a la mano). El backend genera un
             correo temporal único para no romper la restricción NOT NULL +
             UNIQUE de `users.email`; el admin lo actualiza después editando
             al cliente. Mismo patrón de "el <script> inyectado no se
             ejecuta" que admin-ui/staff-user/_form.blade.php — por eso el
             toggle va en un onchange inline en vez de un <script>. --}}
        <div class="au-field">
            <label class="au-label" style="display:flex;align-items:center;gap:8px;font-weight:400">
                <input type="checkbox" class="au-checkbox" name="sin_datos_contacto" value="1" onchange="
                    var checked = this.checked;
                    var form = this.form;
                    form.querySelectorAll('[data-contact-field]').forEach(function (el) {
                        el.disabled = checked;
                        if (checked) el.value = '';
                    });
                    var emailField = form.querySelector('[name=email]');
                    if (emailField) emailField.required = !checked;
                    var requiredMark = form.querySelector('[data-email-required-mark]');
                    if (requiredMark) requiredMark.style.display = checked ? 'none' : '';
                ">
                Agregaré correo, RFC, teléfono y empresa más tarde
            </label>
        </div>
    @endunless
    <div class="au-field">
        <label class="au-label">Tipo de cuenta</label>
        <select class="au-select" name="account_type">
            <option value="personal" {{ (!isset($customer) || $customer->account_type !== 'b2b') ? 'selected' : '' }}>Personal</option>
            <option value="b2b" {{ isset($customer) && $customer->account_type === 'b2b' ? 'selected' : '' }}>B2B</option>
        </select>
    </div>
    <div class="au-field">
        <label class="au-label">Perfil de cliente</label>
        <select class="au-select" name="tipo_cliente">
            <option value="" {{ (!isset($customer) || !$customer->tipo_cliente) ? 'selected' : '' }}>Seleccionar</option>
            <option value="revendedor" {{ isset($customer) && $customer->tipo_cliente === 'revendedor' ? 'selected' : '' }}>Revendedor</option>
            <option value="tecnico" {{ isset($customer) && $customer->tipo_cliente === 'tecnico' ? 'selected' : '' }}>Técnico</option>
            <option value="empresa" {{ isset($customer) && $customer->tipo_cliente === 'empresa' ? 'selected' : '' }}>Empresa</option>
            <option value="contratista" {{ isset($customer) && $customer->tipo_cliente === 'contratista' ? 'selected' : '' }}>Contratista</option>
        </select>
    </div>
</form>
