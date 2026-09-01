/**
 * checkout.js — lógica visual del checkout
 * Complementa el JS jQuery inline del checkout.blade.php.
 * Maneja: panel de nueva dirección, validación en vivo de ese formulario
 * (espejo de App\Models\UserAddress::validationRules() en el backend — esta
 * copia en JS es solo para feedback inmediato, el backend sigue siendo la
 * fuente de verdad) y estado de loading del form de checkout.
 * La selección de método de pago/envío y el AJAX están en el <script>
 * inline del blade.
 */

document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('.checkout-page')) return;

    // ── TOGGLE PANEL NUEVA DIRECCIÓN ──────────────────────
    var btnAddAddress    = document.getElementById('btn-add-address');
    var newAddressPanel  = document.getElementById('new-address-panel');
    var btnCancelAddress = document.getElementById('btn-cancel-address');

    if (btnAddAddress && newAddressPanel) {
        btnAddAddress.addEventListener('click', function () {
            newAddressPanel.classList.add('active');
            btnAddAddress.style.display = 'none';
        });
    }
    if (btnCancelAddress && newAddressPanel) {
        btnCancelAddress.addEventListener('click', function () {
            newAddressPanel.classList.remove('active');
            if (btnAddAddress) btnAddAddress.style.display = '';
        });
    }

    // ── VALIDACIÓN EN VIVO DEL FORMULARIO DE NUEVA DIRECCIÓN ──────
    // Mismas reglas que el backend (App\Models\UserAddress::validationRules())
    // pero aplicadas mientras el usuario escribe: filtra caracteres no
    // válidos tecla por tecla (ej. letras en teléfono, símbolos en correo)
    // y muestra el error sin esperar el viaje redondo al servidor.
    var ADDRESS_FIELD_RULES = {
        'address-name': {
            filter: /[^\p{L}\s.'-]/gu,
            test: /^[\p{L}\s.'-]+$/u,
            required: true,
            message: 'Solo letras y espacios.'
        },
        'address-phone': {
            filter: /\D/g,
            test: /^\d{10}$/,
            required: true,
            message: 'Debe tener 10 dígitos (solo números).'
        },
        'address-email': {
            filter: /[^A-Za-z0-9@._+-]/g,
            test: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/,
            required: true,
            message: 'Correo electrónico no válido.'
        },
        'address-zip': {
            filter: /\D/g,
            test: /^\d{5}$/,
            required: true,
            message: 'Debe tener 5 dígitos.'
        },
        'address-city': {
            filter: /[^\p{L}\s.'-]/gu,
            test: /^[\p{L}\s.'-]+$/u,
            required: true,
            message: 'Solo letras y espacios.'
        },
        'address-col': {
            filter: /[^\p{L}\p{N}\s.,#'-]/gu,
            test: /^[\p{L}\p{N}\s.,#'-]+$/u,
            required: true,
            message: 'Contiene caracteres no permitidos.'
        },
        'address-street': {
            filter: /[^\p{L}\p{N}\s.,#'-]/gu,
            test: /^[\p{L}\p{N}\s.,#'-]+$/u,
            required: true,
            message: 'Contiene caracteres no permitidos.'
        }
    };

    function setAddressFieldError(input, errorEl, message) {
        input.classList.add('is-invalid');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
    }

    function clearAddressFieldError(input, errorEl) {
        input.classList.remove('is-invalid');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    }

    function validateAddressField(id) {
        var rule = ADDRESS_FIELD_RULES[id];
        var input = document.getElementById(id);
        if (!rule || !input) return true;
        var errorEl = document.getElementById(id + '-error');
        var value = input.value.trim();

        if (!value) {
            if (rule.required) {
                setAddressFieldError(input, errorEl, 'Este campo es obligatorio.');
                return false;
            }
            clearAddressFieldError(input, errorEl);
            return true;
        }

        if (rule.test && !rule.test.test(value)) {
            setAddressFieldError(input, errorEl, rule.message);
            return false;
        }

        clearAddressFieldError(input, errorEl);
        return true;
    }

    Object.keys(ADDRESS_FIELD_RULES).forEach(function (id) {
        var input = document.getElementById(id);
        if (!input) return;
        var rule = ADDRESS_FIELD_RULES[id];

        input.addEventListener('input', function () {
            if (rule.filter) {
                var cleaned = input.value.replace(rule.filter, '');
                if (cleaned !== input.value) {
                    input.value = cleaned;
                }
            }
            clearAddressFieldError(input, document.getElementById(id + '-error'));
        });

        input.addEventListener('blur', function () {
            validateAddressField(id);
        });
    });

    // Expuesto globalmente porque el submit real del formulario lo dispara
    // el <script> jQuery inline de checkout.blade.php (botón "Guardar
    // dirección"), no este archivo.
    window.validateAddressForm = function () {
        var allValid = true;
        Object.keys(ADDRESS_FIELD_RULES).forEach(function (id) {
            if (!validateAddressField(id)) allValid = false;
        });
        return allValid;
    };

    // ── LOADING STATE (submit via form, no AJAX) ───────────
    var checkoutForm = document.getElementById('checkOutForm');
    var submitBtn    = document.getElementById('submitCheckoutForm');
    if (checkoutForm && submitBtn) {
        checkoutForm.addEventListener('submit', function () {
            submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-icon"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Procesando...';
            submitBtn.disabled = true;
        });
    }
});
