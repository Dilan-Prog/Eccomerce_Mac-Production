/* =========================================================
   photo-upload.js — Preview y Captions de Fotos
   MAC DEL NORTE — Reporte de Servicio Técnico
   ========================================================= */

(function () {
    'use strict';

    var MAX = 8;

    function updateCount() {
        var fotos = window.reportForm.fotos || [];
        var label = document.getElementById('foto-count-label');
        if (label) label.textContent = fotos.length + ' / ' + MAX + ' fotos';
    }

    function updateEmpty() {
        var fotos  = window.reportForm.fotos || [];
        var empty  = document.getElementById('foto-empty');
        var grid   = document.getElementById('foto-grid');
        if (empty) empty.style.display  = fotos.length === 0 ? 'block' : 'none';
        if (grid)  grid.style.display   = fotos.length === 0 ? 'none'  : 'grid';
    }

    function renderGrid() {
        var grid  = document.getElementById('foto-grid');
        if (!grid) return;
        var fotos = window.reportForm.fotos || [];

        grid.innerHTML = '';
        fotos.forEach(function (foto, i) {
            var card = document.createElement('div');
            card.className = 'foto-card';

            var imgWrap = document.createElement('div');
            imgWrap.style.cssText = 'position:relative;';

            var img = document.createElement('img');
            img.src = foto.data;
            img.alt = 'Foto ' + (i + 1);

            var btnRemove = document.createElement('button');
            btnRemove.className = 'foto-remove';
            btnRemove.textContent = '×';
            btnRemove.title = 'Eliminar foto';
            btnRemove.addEventListener('click', function () {
                window.photoUpload.remove(i);
            });

            imgWrap.appendChild(img);
            imgWrap.appendChild(btnRemove);

            var captionWrap = document.createElement('div');
            captionWrap.className = 'foto-caption';

            var captionInput = document.createElement('input');
            captionInput.type = 'text';
            captionInput.placeholder = 'Descripción…';
            captionInput.value = foto.caption || '';
            captionInput.addEventListener('input', function () {
                window.photoUpload.updateCaption(i, captionInput.value);
            });

            captionWrap.appendChild(captionInput);
            card.appendChild(imgWrap);
            card.appendChild(captionWrap);
            grid.appendChild(card);
        });

        updateCount();
        updateEmpty();
        window.formWizard && window.formWizard.updateProgress();
    }

    window.photoUpload = {
        init: function () {
            window.reportForm.fotos = window.reportForm.fotos || [];

            var input  = document.getElementById('fotoInput');
            var btnAdd = document.getElementById('btn-add-foto');

            if (btnAdd && input) {
                btnAdd.addEventListener('click', function () { input.click(); });
            }

            if (input) {
                input.addEventListener('change', function (e) {
                    var files     = Array.from(e.target.files);
                    var current   = window.reportForm.fotos || [];
                    var remaining = MAX - current.length;

                    if (files.length > remaining) {
                        window.showToast && window.showToast('Máximo ' + MAX + ' fotos. Se agregarán las primeras ' + remaining + '.', 'warning');
                    }

                    var toAdd  = files.slice(0, remaining);
                    var loaded = 0;
                    if (toAdd.length === 0) { input.value = ''; return; }

                    toAdd.forEach(function (file) {
                        var reader = new FileReader();
                        reader.onload = function (ev) {
                            window.reportForm.fotos = window.reportForm.fotos || [];
                            window.reportForm.fotos.push({ data: ev.target.result, caption: '' });
                            loaded++;
                            if (loaded === toAdd.length) renderGrid();
                        };
                        reader.readAsDataURL(file);
                    });

                    input.value = '';
                });
            }

            renderGrid();
        },

        remove: function (index) {
            window.reportForm.fotos = window.reportForm.fotos || [];
            window.reportForm.fotos.splice(index, 1);
            renderGrid();
        },

        updateCaption: function (index, caption) {
            if (window.reportForm.fotos && window.reportForm.fotos[index]) {
                window.reportForm.fotos[index].caption = caption;
            }
        },

        reset: function () {
            window.reportForm.fotos = [];
            renderGrid();
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.photoUpload.init();
    });

}());
