/**
 * checkout.js — lógica visual del checkout
 * Complementa el JS jQuery inline (AJAX, submit) del checkout.blade.php.
 * Maneja: selección visual de tarjetas, nuevo panel de dirección, validación.
 */

document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('.checkout-page')) return;

    // ── 1. SELECCIÓN VISUAL DE DIRECCIÓN ──────────────────────
    const addressCards = document.querySelectorAll('.address-card');
    addressCards.forEach(function (card) {
        card.addEventListener('click', function () {
            addressCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                // Sincroniza con el hidden input que usa el AJAX existente
                const hiddenInput = document.getElementById('shipping_address_id');
                if (hiddenInput) hiddenInput.value = radio.value;
            }
            validateSubmitBtn();
        });
        // Init: si ya viene checked (primer elemento)
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.checked) card.classList.add('selected');
    });

    // ── 2. SELECCIÓN VISUAL DE ENVÍO ──────────────────────────
    const shippingOptions = document.querySelectorAll('.shipping-option');
    shippingOptions.forEach(function (opt) {
        opt.addEventListener('click', function () {
            shippingOptions.forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            // El AJAX jQuery ya maneja la actualización del total vía .shipping_method
            validateSubmitBtn();
        });
    });

    // ── 3. TOGGLE PANEL NUEVA DIRECCIÓN ───────────────────────
    const btnAddAddress  = document.getElementById('btn-add-address');
    const newAddressPanel = document.getElementById('new-address-panel');
    const btnCancelAddress = document.getElementById('btn-cancel-address');

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

    // ── 4. VALIDACIÓN DEL BOTÓN CONFIRMAR ─────────────────────
    function validateSubmitBtn() {
        const btn = document.getElementById('submitCheckoutForm');
        if (!btn) return;

        const hasAddress  = !!document.getElementById('shipping_address_id')?.value;
        const hasShipping = !!document.getElementById('shipping_method_id')?.value;
        const hasTerms    = document.getElementById('flexCheckChecked3')?.checked;

        const isValid = hasAddress && hasShipping && hasTerms;
        btn.classList.toggle('btn-ready', isValid);
    }

    // Escucha cambios en checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', validateSubmitBtn);
    });

    // Valida al inicio
    validateSubmitBtn();

    // ── 5. LOADING STATE EN EL BOTÓN SUBMIT ───────────────────
    // El submit real lo maneja el jQuery inline.
    // Solo añadimos un observer en caso de que sea un submit estándar.
    const checkoutForm = document.getElementById('checkOutForm');
    const submitBtn    = document.getElementById('submitCheckoutForm');
    if (checkoutForm && submitBtn) {
        checkoutForm.addEventListener('submit', function () {
            submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-icon"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Procesando...';
            submitBtn.disabled = true;
        });
    }
});
