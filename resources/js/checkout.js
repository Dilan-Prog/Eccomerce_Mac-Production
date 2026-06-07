/**
 * checkout.js — lógica visual del checkout
 * Complementa el JS jQuery inline del checkout.blade.php.
 * Solo maneja: panel de nueva dirección y estado de loading del form.
 * La validación, selección y AJAX están en el <script> inline del blade.
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
