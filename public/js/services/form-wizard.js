/* =========================================================
   form-wizard.js — Stepper + Mediciones Manager
   MAC DEL NORTE — Reporte de Servicio Técnico
   ========================================================= */

(function () {
    'use strict';

    var STEPS = ['general', 'cliente', 'equipo', 'mediciones', 'observaciones', 'fotos', 'firma'];

    var TRACKED_FIELDS = [
        'folio', 'fecha', 'tecnico', 'tipoServicio',
        'clienteNombre', 'clienteEmpresa', 'clienteRfc', 'clienteTel', 'clienteDireccion', 'clienteEmail',
        'equipoDesc', 'equipoMarca', 'equipoModelo', 'equipoSerie', 'equipoUbicacion', 'equipoTag',
        'observaciones', 'recomendaciones'
    ];

    var currentStep = 0;
    window.reportForm = {};

    /* ---- helpers ---- */

    function el(id) { return document.getElementById(id); }

    function readStep() {
        var panel = el('step-' + STEPS[currentStep]);
        if (!panel) return;
        panel.querySelectorAll('input[id], select[id], textarea[id]').forEach(function (input) {
            if (input.type !== 'file') {
                window.reportForm[input.id] = input.value;
            }
        });
    }

    function updateProgress() {
        var filled = 0;
        TRACKED_FIELDS.forEach(function (id) {
            if ((window.reportForm[id] || '').trim() !== '') filled++;
        });
        var medOk  = ((window.reportForm.med  || []).some(function (r) { return r.punto && r.punto.trim(); })) ? 1 : 0;
        var fotoOk = ((window.reportForm.fotos || []).length > 0) ? 1 : 0;
        var total  = TRACKED_FIELDS.length + 2;
        var pct    = Math.round(((filled + medOk + fotoOk) / total) * 100);

        var bar   = el('progress-bar');
        var label = el('progress-pct');
        if (bar)   bar.style.width = pct + '%';
        if (label) label.textContent = pct + '%';
    }

    function renderDots() {
        var container = el('step-dots');
        if (!container) return;
        var html = '';
        STEPS.forEach(function (s, i) {
            var bg = i === currentStep ? '#F47920' : (i < currentStep ? '#10B981' : '#CBD5E1');
            html += '<div style="width:10px;height:10px;border-radius:50%;background:' + bg + ';transition:background 0.3s;cursor:pointer;" data-dot="' + i + '"></div>';
        });
        container.innerHTML = html;
        container.querySelectorAll('[data-dot]').forEach(function (dot) {
            dot.addEventListener('click', function () {
                goToStep(parseInt(dot.dataset.dot));
            });
        });
    }

    function renderTabs() {
        document.querySelectorAll('.step-tab').forEach(function (tab) {
            var idx = parseInt(tab.dataset.stepIndex);
            if (idx === currentStep) {
                tab.style.color = '#F47920';
                tab.style.borderBottomColor = '#F47920';
                tab.style.background = 'rgba(244,121,32,0.12)';
            } else {
                tab.style.color = '#94A3B8';
                tab.style.borderBottomColor = 'transparent';
                tab.style.background = 'transparent';
            }
        });
    }

    function renderPanels() {
        STEPS.forEach(function (s, i) {
            var panel = el('step-' + s);
            if (panel) panel.style.display = (i === currentStep) ? 'block' : 'none';
        });
    }

    function updateButtons() {
        var btnPrev = el('btn-prev');
        var btnNext = el('btn-next');
        if (btnPrev) {
            btnPrev.disabled = (currentStep === 0);
        }
        if (btnNext) {
            if (currentStep === STEPS.length - 1) {
                btnNext.textContent = '📄 Generar PDF';
                btnNext.style.background = '#10B981';
            } else {
                btnNext.textContent = 'Siguiente →';
                btnNext.style.background = '';
            }
        }
    }

    function updateSummary() {
        var f = window.reportForm;
        function set(id, val) {
            var node = el(id);
            if (node) node.textContent = val || '—';
        }
        set('sum-folio',   f.folio);
        set('sum-fecha',   f.fecha);
        set('sum-tecnico', f.tecnico);
        set('sum-cliente', [f.clienteNombre, f.clienteEmpresa].filter(Boolean).join(' / '));
        set('sum-equipo',  [f.equipoDesc, f.equipoMarca].filter(Boolean).join(' — '));
        set('sum-tipo',    f.tipoServicio);
    }

    function render() {
        renderPanels();
        renderTabs();
        renderDots();
        updateProgress();
        updateButtons();
        if (currentStep === STEPS.length - 1) updateSummary();
    }

    /* ---- public API ---- */

    function goToStep(i) {
        readStep();
        currentStep = Math.max(0, Math.min(STEPS.length - 1, i));
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.formWizard = {
        goToStep: goToStep,
        updateProgress: updateProgress,
        next: function () {
            readStep();
            if (currentStep === STEPS.length - 1) {
                window.pdfGenerator && window.pdfGenerator.generate();
            } else {
                currentStep++;
                render();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        prev: function () {
            readStep();
            if (currentStep > 0) {
                currentStep--;
                render();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        reset: function () {
            window.reportForm = {};
            currentStep = 0;
            document.querySelectorAll('.step-panel input, .step-panel select, .step-panel textarea').forEach(function (inp) {
                if (inp.type === 'file') return;
                inp.value = '';
            });
            var fechaInp = el('fecha');
            if (fechaInp) fechaInp.value = new Date().toISOString().split('T')[0];
            window.medicionesManager && window.medicionesManager.reset();
            window.photoUpload      && window.photoUpload.reset();
            window.signaturePad     && window.signaturePad.clear();
            render();
            window.showToast('Formulario reiniciado', 'success');
        }
    };

    /* ---- toast ---- */

    window.showToast = function (msg, type) {
        type = type || 'success';
        var container = el('toast-container');
        if (!container) return;
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = msg;
        container.appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3200);
    };

    /* ---- init ---- */

    document.addEventListener('DOMContentLoaded', function () {
        var fechaInp = el('fecha');
        if (fechaInp && !fechaInp.value) {
            fechaInp.value = new Date().toISOString().split('T')[0];
        }

        render();

        /* Wire navigation buttons */
        var btnPrev = el('btn-prev');
        var btnNext = el('btn-next');
        var btnNuevo = el('btn-nuevo');

        if (btnPrev) btnPrev.addEventListener('click', function () { window.formWizard.prev(); });
        if (btnNext) btnNext.addEventListener('click', function () { window.formWizard.next(); });
        if (btnNuevo) btnNuevo.addEventListener('click', function () { window.formWizard.reset(); });

        /* Wire step tabs */
        document.querySelectorAll('.step-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                goToStep(parseInt(tab.dataset.stepIndex));
            });
        });

        /* Track input changes for progress */
        document.querySelectorAll('.step-panel input, .step-panel select, .step-panel textarea').forEach(function (inp) {
            inp.addEventListener('input', function () {
                readStep();
                updateProgress();
            });
        });
    });

}());


/* =========================================================
   Mediciones Manager
   ========================================================= */

(function () {
    'use strict';

    function buildRow(data) {
        data = data || {};
        var tr = document.createElement('tr');

        var fields = [
            { key: 'punto',        placeholder: 'Punto',   width: '130px' },
            { key: 'valorRef',     placeholder: 'Ref.',    width: '80px'  },
            { key: 'valorMedido',  placeholder: 'Medido',  width: '80px'  },
            { key: 'error',        placeholder: 'Error',   width: '70px'  },
            { key: 'tolerancia',   placeholder: 'Tol.',    width: '70px'  },
        ];

        var html = '';
        fields.forEach(function (f) {
            var val = (data[f.key] || '').replace(/"/g, '&quot;');
            html += '<td style="padding:6px 4px;">'
                  + '<input type="text" data-field="' + f.key + '" value="' + val + '" '
                  + 'placeholder="' + f.placeholder + '" style="min-width:' + f.width + ';">'
                  + '</td>';
        });

        var res = data.resultado || '';
        html += '<td style="padding:6px 4px;">'
              + '<select data-field="resultado" style="min-width:95px;">'
              + '<option value=""' + (res === '' ? ' selected' : '') + '>— Estado —</option>'
              + '<option value="OK"' + (res === 'OK' ? ' selected' : '') + '>✓ OK</option>'
              + '<option value="FAIL"' + (res === 'FAIL' ? ' selected' : '') + '>✗ FAIL</option>'
              + '<option value="PENDIENTE"' + (res === 'PENDIENTE' ? ' selected' : '') + '>⏳ Pendiente</option>'
              + '</select>'
              + '</td>';

        html += '<td style="padding:6px 4px;text-align:center;">'
              + '<button class="med-remove-btn" title="Eliminar fila" style="'
              + 'background:var(--red);color:white;border:none;border-radius:50%;'
              + 'width:22px;height:22px;cursor:pointer;font-size:14px;font-weight:700;'
              + 'line-height:1;display:inline-flex;align-items:center;justify-content:center;'
              + '">×</button>'
              + '</td>';

        tr.innerHTML = html;

        tr.querySelectorAll('[data-field]').forEach(function (inp) {
            inp.addEventListener('input', function () { window.medicionesManager.sync(); });
            inp.addEventListener('change', function () { window.medicionesManager.sync(); });
        });

        tr.querySelector('.med-remove-btn').addEventListener('click', function () {
            var tbody = document.getElementById('med-tbody');
            if (tbody && tbody.rows.length > 1) {
                tr.remove();
                window.medicionesManager.sync();
            } else {
                window.showToast && window.showToast('Debe haber al menos una fila', 'warning');
            }
        });

        return tr;
    }

    window.medicionesManager = {
        init: function () {
            if (!window.reportForm.med || window.reportForm.med.length === 0) {
                this.addRow();
            } else {
                this.renderAll();
            }

            var btnAdd = document.getElementById('btn-add-medicion');
            if (btnAdd) {
                btnAdd.addEventListener('click', function () {
                    window.medicionesManager.addRow();
                });
            }
        },

        addRow: function () {
            var tbody = document.getElementById('med-tbody');
            if (!tbody) return;
            tbody.appendChild(buildRow({}));
            window.reportForm.med = window.reportForm.med || [];
            window.reportForm.med.push({ punto: '', valorRef: '', valorMedido: '', error: '', tolerancia: '', resultado: '' });
        },

        sync: function () {
            var tbody = document.getElementById('med-tbody');
            if (!tbody) return;
            window.reportForm.med = [];
            Array.from(tbody.rows).forEach(function (tr) {
                var row = {};
                tr.querySelectorAll('[data-field]').forEach(function (inp) {
                    row[inp.dataset.field] = inp.value;
                });
                window.reportForm.med.push(row);
            });
            window.formWizard && window.formWizard.updateProgress();
        },

        renderAll: function () {
            var tbody = document.getElementById('med-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            (window.reportForm.med || []).forEach(function (rowData) {
                tbody.appendChild(buildRow(rowData));
            });
        },

        reset: function () {
            window.reportForm.med = [];
            var tbody = document.getElementById('med-tbody');
            if (tbody) tbody.innerHTML = '';
            this.addRow();
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.medicionesManager.init();
    });

}());
