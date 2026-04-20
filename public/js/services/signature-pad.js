/* =========================================================
   signature-pad.js — Canvas de Firma
   MAC DEL NORTE — Reporte de Servicio Técnico
   ========================================================= */

(function () {
    'use strict';

    var canvas, ctx, drawing = false;

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width  / rect.width;
        var scaleY = canvas.height / rect.height;
        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top)  * scaleY
        };
    }

    function onDown(e) {
        drawing = true;
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function onMove(e) {
        if (!drawing) return;
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function onUp() {
        if (drawing) {
            drawing = false;
            save();
        }
    }

    function onTouchStart(e) {
        e.preventDefault();
        onDown(e.touches[0]);
    }

    function onTouchMove(e) {
        e.preventDefault();
        onMove(e.touches[0]);
    }

    function onTouchEnd(e) {
        e.preventDefault();
        onUp();
    }

    function save() {
        window.reportForm = window.reportForm || {};
        window.reportForm.firma = canvas.toDataURL('image/png');
        var status = document.getElementById('sig-status');
        if (status) {
            status.textContent = '✓ Firma capturada';
            status.style.color = '#10B981';
            status.style.fontWeight = '700';
        }
    }

    window.signaturePad = {
        init: function () {
            canvas = document.getElementById('sigCanvas');
            if (!canvas) return;
            ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#0A1628';
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            canvas.addEventListener('mousedown',  onDown);
            canvas.addEventListener('mousemove',  onMove);
            canvas.addEventListener('mouseup',    onUp);
            canvas.addEventListener('mouseleave', onUp);

            canvas.addEventListener('touchstart', onTouchStart, { passive: false });
            canvas.addEventListener('touchmove',  onTouchMove,  { passive: false });
            canvas.addEventListener('touchend',   onTouchEnd,   { passive: false });

            var btnClear = document.getElementById('btn-clear-sig');
            if (btnClear) {
                btnClear.addEventListener('click', function () {
                    window.signaturePad.clear();
                });
            }
        },

        clear: function () {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            window.reportForm = window.reportForm || {};
            window.reportForm.firma = null;
            var status = document.getElementById('sig-status');
            if (status) {
                status.textContent = 'Sin firma';
                status.style.color = '#94A3B8';
                status.style.fontWeight = '400';
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.signaturePad.init();
    });

}());
