{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $customer->name ?? '' }}" required>
    </div>
    <div class="au-field">
        <label class="au-label">Correo electrónico<span class="au-required-mark">*</span></label>
        <input type="email" class="au-input" name="email" value="{{ $customer->email ?? '' }}" required>
    </div>
    <div class="au-field">
        <label class="au-label">Teléfono</label>
        <input type="text" class="au-input" name="phone" value="{{ $customer->phone ?? '' }}">
    </div>
    <div class="au-field">
        <label class="au-label">Empresa</label>
        <input type="text" class="au-input" name="company" value="{{ $customer->company ?? '' }}">
    </div>
    <div class="au-field">
        <label class="au-label">RFC</label>
        <input type="text" class="au-input" name="rfc" value="{{ $customer->rfc ?? '' }}">
    </div>
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
