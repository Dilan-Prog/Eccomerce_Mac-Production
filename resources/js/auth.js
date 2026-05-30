/**
 * auth.js — Vanilla ES Module para vistas de autenticación.
 * Sin jQuery, Alpine ni ningún framework externo.
 */

/* =========================================================
   ACCOUNT TYPE SWITCHER
   Usa IDs del HTML de referencia: card-personal, card-b2b,
   form-container (con clase b2b-active), form-title,
   form-subtitle, progress-text, progress-fill, submit-text
   ========================================================= */
function switchAccount(type) {
    const cardPersonal   = document.getElementById('card-personal');
    const cardB2b        = document.getElementById('card-b2b');
    const formContainer  = document.getElementById('form-container');
    const formTitle      = document.getElementById('form-title');
    const formSubtitle   = document.getElementById('form-subtitle');
    const progressText   = document.getElementById('progress-text');
    const progressFill   = document.getElementById('progress-fill');
    const submitText     = document.getElementById('submit-text');
    const radioPersonal  = document.getElementById('type_personal');
    const radioB2B       = document.getElementById('type_b2b');

    if (!cardPersonal || !cardB2b) return;

    if (type === 'personal') {
        cardPersonal.classList.add('active');
        cardB2b.classList.remove('active');
        if (formContainer)  formContainer.classList.remove('b2b-active');
        if (formTitle)      formTitle.textContent     = 'Datos de tu cuenta';
        if (formSubtitle)   formSubtitle.textContent  = 'Llena los siguientes campos para crear tu cuenta';
        if (progressText)   progressText.textContent  = 'Paso 1 de 1 · 30 segundos';
        if (progressFill)   progressFill.style.width  = '33%';
        if (submitText)     submitText.textContent     = 'Crear mi cuenta gratis';
        if (radioPersonal)  radioPersonal.checked      = true;
    } else {
        cardB2b.classList.add('active');
        cardPersonal.classList.remove('active');
        if (formContainer)  formContainer.classList.add('b2b-active');
        if (formTitle)      formTitle.textContent     = 'Datos de tu cuenta B2B';
        if (formSubtitle)   formSubtitle.textContent  = 'Validamos en 24–48 h hábiles. Recibirás acceso a precios especiales una vez aprobado.';
        if (progressText)   progressText.textContent  = 'Paso 1 de 1 · 1 minuto';
        if (progressFill)   progressFill.style.width  = '20%';
        if (submitText)     submitText.textContent     = 'Solicitar acceso B2B';
        if (radioB2B)       radioB2B.checked           = true;
    }
}

/* =========================================================
   FILE UPLOAD — valida PDF ≤ 5 MB, muestra preview
   ========================================================= */
function handleFileUpload(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.type !== 'application/pdf') {
        alert('Por favor sube solo archivos en formato PDF.');
        input.value = '';
        return;
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('El archivo es demasiado grande. El tamaño máximo permitido es 5 MB.');
        input.value = '';
        return;
    }

    const uploadArea = document.getElementById('csf-upload');
    const fileNameEl = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');

    if (fileNameEl) fileNameEl.textContent = file.name;
    if (fileSizeEl) fileSizeEl.textContent = formatFileSize(file.size);
    if (uploadArea) uploadArea.classList.add('has-file');
}

function removeFile() {
    const uploadArea = document.getElementById('csf-upload');
    const input      = document.getElementById('csf-input');
    if (input)      input.value = '';
    if (uploadArea) uploadArea.classList.remove('has-file');
}

function formatFileSize(bytes) {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

/* =========================================================
   DRAG & DROP
   ========================================================= */
function initDragDrop() {
    const uploadArea = document.getElementById('csf-upload');
    if (!uploadArea) return;

    uploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add('dragging');
    });

    uploadArea.addEventListener('dragleave', e => {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('dragging');
    });

    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('dragging');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById('csf-input');
            if (input) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(files[0]);
                    input.files = dt.files;
                } catch (_) {
                    /* DataTransfer no soportado en algunos navegadores */
                }
                handleFileUpload(input);
            }
        }
    });
}

/* =========================================================
   PASSWORD STRENGTH METER
   ========================================================= */
function passwordStrength(input) {
    const val  = input.value;
    const wrap = input.closest('.form-group');
    if (!wrap) return;

    const bars  = wrap.querySelectorAll('.strength-bar');
    const label = wrap.querySelector('.strength-label');

    bars.forEach(b => b.className = 'strength-bar');
    if (label) label.className = 'strength-label';

    if (!val) return;

    let score = 0;
    if (val.length >= 8)               score++;
    if (val.length >= 12)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    if (score <= 2) {
        bars[0]?.classList.add('active-weak');
        if (label) { label.textContent = 'Débil'; label.classList.add('weak'); }
    } else if (score <= 3) {
        bars[0]?.classList.add('active-medium');
        bars[1]?.classList.add('active-medium');
        if (label) { label.textContent = 'Media'; label.classList.add('medium'); }
    } else {
        bars.forEach(b => b.classList.add('active-strong'));
        if (label) { label.textContent = 'Fuerte'; label.classList.add('strong'); }
    }
}

/* =========================================================
   TOGGLE PASSWORD VISIBILITY
   ========================================================= */
function togglePassword(buttonEl) {
    const wrap  = buttonEl.closest('.form-input-wrap') || buttonEl.parentElement;
    const input = wrap?.querySelector('input');
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    buttonEl.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');

    const svg = buttonEl.querySelector('svg');
    if (svg) {
        svg.innerHTML = isPassword
            ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
    }
}

/* =========================================================
   INIT
   ========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    /* Drag & drop */
    initDragDrop();

    /* Password strength */
    document.querySelectorAll('input[data-strength]').forEach(input => {
        input.addEventListener('input', () => passwordStrength(input));
    });

    /* Password visibility toggles */
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => togglePassword(btn));
    });

    /* File input change */
    const csfInput = document.getElementById('csf-input');
    if (csfInput) {
        csfInput.addEventListener('change', () => handleFileUpload(csfInput));
    }

    /* Default: personal activo si existe el selector */
    const cardPersonal = document.getElementById('card-personal');
    if (cardPersonal && !document.querySelector('.account-type-card.active')) {
        cardPersonal.classList.add('active');
        const radio = document.getElementById('type_personal');
        if (radio) radio.checked = true;
    }
});

/* Exponer globalmente para onclick inline en Blade */
window.switchAccount  = switchAccount;
window.removeFile     = removeFile;
window.handleFileUpload = handleFileUpload;
window.togglePassword = togglePassword;
