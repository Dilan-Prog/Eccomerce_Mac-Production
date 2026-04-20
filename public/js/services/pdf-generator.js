/* =========================================================
   pdf-generator.js — Generación de PDF con jsPDF
   MAC DEL NORTE — Reporte de Servicio Técnico
   ========================================================= */

(function () {
    'use strict';

    var EMPRESA = {
        nombre:  'MAC DEL NORTE',
        full:    'MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.',
        rfc:     'NMA180313M46',
        address: 'C. Castaño 718, Col. Ebanos Norte 2do Sector, Apodaca N.L. CP.66612',
        phones:  '+81-3582-5559 · 81-2473-8768',
        email:   'contacto@macdelnorte.com',
        web:     'www.macdelnorte.com'
    };

    var MARGIN   = 16;
    var PAGE_W   = 215.9;
    var PAGE_H   = 279.4;
    var CONT_W   = PAGE_W - MARGIN * 2;

    /* ---- colors [r,g,b] ---- */
    var NAVY      = [10, 22, 40];
    var NAVY_MID  = [30, 51, 85];
    var ORANGE    = [244, 121, 32];
    var GREEN_C   = [16, 185, 129];
    var RED_C     = [239, 68, 68];
    var AMBER_C   = [245, 158, 11];
    var AMBER_BG  = [255, 251, 235];
    var G50       = [248, 250, 252];
    var G100      = [241, 245, 249];
    var G300      = [203, 213, 225];
    var G500      = [100, 116, 139];
    var G600      = [71, 85, 105];
    var G700      = [51, 65, 85];
    var WHITE     = [255, 255, 255];

    var doc, curY, pageNum;

    /* ---- utilities ---- */

    function imgFmt(dataUrl) {
        var m = dataUrl.match(/^data:image\/(\w+);/);
        if (!m) return 'JPEG';
        var t = m[1].toLowerCase();
        return t === 'png' ? 'PNG' : (t === 'webp' ? 'WEBP' : 'JPEG');
    }

    function footer() {
        var y = PAGE_H - 9;
        doc.setFillColor.apply(doc, NAVY);
        doc.rect(0, PAGE_H - 14, PAGE_W, 14, 'F');
        doc.setFontSize(6.5);
        doc.setTextColor.apply(doc, WHITE);
        doc.setFont(undefined, 'normal');
        doc.text(EMPRESA.nombre + ' | RFC: ' + EMPRESA.rfc + ' | ' + EMPRESA.phones, MARGIN, y);
        doc.text('Página ' + pageNum, PAGE_W - MARGIN, y, { align: 'right' });
        doc.text(EMPRESA.address + ' | ' + EMPRESA.email, PAGE_W / 2, y - 4, { align: 'center' });
    }

    function checkBreak(needed) {
        if (curY + needed > PAGE_H - 18) {
            footer();
            doc.addPage();
            pageNum++;
            curY = MARGIN;
        }
    }

    function sectionHeader(title, color) {
        color = color || NAVY;
        checkBreak(12);
        doc.setFillColor.apply(doc, color);
        doc.rect(MARGIN, curY, CONT_W, 8, 'F');
        doc.setFontSize(8.5);
        doc.setTextColor.apply(doc, WHITE);
        doc.setFont(undefined, 'bold');
        doc.text(title.toUpperCase(), MARGIN + 4, curY + 5.5);
        curY += 8 + 5;
        doc.setFont(undefined, 'normal');
    }

    function field(lbl, val, x, y, w) {
        w = w || CONT_W;
        doc.setFontSize(7);
        doc.setTextColor.apply(doc, G500);
        doc.setFont(undefined, 'bold');
        doc.text((lbl || '').toUpperCase(), x, y);
        doc.setFontSize(9);
        doc.setTextColor.apply(doc, G700);
        doc.setFont(undefined, 'normal');
        var lines = doc.splitTextToSize(val || '—', w - 6);
        doc.text(lines, x, y + 5);
        return y + 5 + (lines.length - 1) * 4.2;
    }

    function twoCol(pairs, startY) {
        var hw = CONT_W / 2;
        checkBreak(14);
        var leftY = startY, rightY = startY;
        pairs.forEach(function (pair, i) {
            var x = MARGIN + (i % 2) * (hw + 2);
            if (i % 2 === 0 && i > 0) {
                startY += 14;
                checkBreak(14);
            }
            field(pair[0], pair[1], x, startY, hw - 2);
        });
        curY = startY + 14;
    }

    /* ---- main builder ---- */

    function build(jsPDF) {
        var f = window.reportForm;

        /* --- validations --- */
        if (!f.folio || !f.folio.trim()) {
            window.showToast && window.showToast('El folio es requerido', 'error');
            return;
        }
        if (!f.tecnico || !f.tecnico.trim()) {
            window.showToast && window.showToast('El nombre del técnico es requerido', 'error');
            return;
        }
        var medRows = (f.med || []).filter(function (r) { return r.punto && r.punto.trim(); });
        if (medRows.length === 0) {
            window.showToast && window.showToast('Agrega al menos un punto de medición', 'error');
            return;
        }
        if (!f.firma) {
            window.showToast && window.showToast('Sin firma — el PDF se generará con línea en blanco', 'warning');
        }

        doc    = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });
        curY   = 0;
        pageNum = 1;

        /* ========== HEADER ========== */
        var hH = 38;
        doc.setFillColor.apply(doc, NAVY);
        doc.rect(0, 0, PAGE_W, hH, 'F');
        /* orange bottom accent */
        doc.setFillColor.apply(doc, ORANGE);
        doc.rect(0, hH - 2, PAGE_W, 2, 'F');

        /* folio box top-right */
        var bW = 52, bH = 24, bX = PAGE_W - MARGIN - bW, bY = 6;
        doc.setFillColor.apply(doc, ORANGE);
        doc.rect(bX, bY, bW, bH, 'F');
        doc.setFontSize(7.5);
        doc.setTextColor.apply(doc, WHITE);
        doc.setFont(undefined, 'bold');
        doc.text('REPORTE DE SERVICIO', bX + bW / 2, bY + 8, { align: 'center' });
        doc.setFontSize(11);
        doc.text(f.folio || '', bX + bW / 2, bY + 17, { align: 'center' });

        /* company name */
        doc.setFontSize(17);
        doc.setTextColor.apply(doc, ORANGE);
        doc.setFont(undefined, 'bold');
        doc.text('MAC DEL NORTE', MARGIN, 16);
        doc.setFontSize(7);
        doc.setTextColor(190, 205, 220);
        doc.setFont(undefined, 'normal');
        doc.text(EMPRESA.full, MARGIN, 23);

        curY = hH + 4;

        /* ========== RESUMEN BAND ========== */
        var bndH = 20;
        doc.setFillColor.apply(doc, G100);
        doc.rect(MARGIN, curY, CONT_W, bndH, 'F');
        var c3 = CONT_W / 3;
        [
            ['Fecha',           f.fecha     || '—'],
            ['Tipo de Servicio', f.tipoServicio || '—'],
            ['Técnico',         f.tecnico   || '—']
        ].forEach(function (item, i) {
            var x = MARGIN + i * c3 + 5;
            doc.setFontSize(7);
            doc.setTextColor.apply(doc, G500);
            doc.setFont(undefined, 'bold');
            doc.text(item[0].toUpperCase(), x, curY + 6);
            doc.setFontSize(9);
            doc.setTextColor.apply(doc, G700);
            doc.setFont(undefined, 'bold');
            doc.text(item[1], x, curY + 14);
        });
        curY += bndH + 8;

        /* ========== CLIENTE ========== */
        sectionHeader('Cliente');
        var hw = CONT_W / 2;
        var clienteRows = [
            [['Nombre / Contacto', f.clienteNombre], ['Empresa', f.clienteEmpresa]],
            [['RFC',               f.clienteRfc],    ['Teléfono', f.clienteTel]],
        ];
        clienteRows.forEach(function (row) {
            checkBreak(14);
            row.forEach(function (pair, i) { field(pair[0], pair[1], MARGIN + i * (hw + 2), curY, hw - 2); });
            curY += 14;
        });
        checkBreak(14);
        field('Dirección', f.clienteDireccion, MARGIN, curY, CONT_W);
        curY += 14;
        checkBreak(14);
        field('Email', f.clienteEmail, MARGIN, curY, CONT_W);
        curY += 14 + 4;

        /* ========== EQUIPO ========== */
        sectionHeader('Equipo');
        checkBreak(14);
        field('Descripción', f.equipoDesc, MARGIN, curY, CONT_W);
        curY += 14;
        var c3w = CONT_W / 3;
        checkBreak(14);
        [['Marca', f.equipoMarca], ['Modelo', f.equipoModelo], ['No. de Serie', f.equipoSerie]]
            .forEach(function (p, i) { field(p[0], p[1], MARGIN + i * c3w, curY, c3w - 2); });
        curY += 14;
        checkBreak(14);
        field('Ubicación', f.equipoUbicacion, MARGIN, curY, hw - 2);
        field('TAG', f.equipoTag, MARGIN + hw + 2, curY, hw - 2);
        curY += 14 + 4;

        /* ========== MEDICIONES ========== */
        sectionHeader('Mediciones', NAVY);

        var cols    = ['Punto', 'Valor Ref.', 'Medido', 'Error', 'Tolerancia', 'Resultado'];
        var colW    = [36, 26, 26, 22, 26, 26];
        var tableW  = colW.reduce(function (a, b) { return a + b; }, 0);

        /* table header */
        checkBreak(8);
        doc.setFillColor.apply(doc, ORANGE);
        doc.rect(MARGIN, curY, tableW, 7, 'F');
        var cx = MARGIN;
        cols.forEach(function (c, i) {
            doc.setFontSize(7.5);
            doc.setTextColor.apply(doc, WHITE);
            doc.setFont(undefined, 'bold');
            doc.text(c, cx + 2, curY + 5);
            cx += colW[i];
        });
        curY += 7;

        /* data rows */
        medRows.forEach(function (row, ri) {
            checkBreak(7);
            doc.setFillColor.apply(doc, ri % 2 === 0 ? WHITE : G50);
            doc.rect(MARGIN, curY, tableW, 7, 'F');
            cx = MARGIN;
            [row.punto, row.valorRef, row.valorMedido, row.error, row.tolerancia, row.resultado]
                .forEach(function (val, ci) {
                    doc.setFontSize(8);
                    doc.setFont(undefined, 'normal');
                    if (ci === 5) {
                        doc.setTextColor.apply(doc, val === 'OK' ? GREEN_C : val === 'FAIL' ? RED_C : G600);
                        doc.setFont(undefined, 'bold');
                    } else {
                        doc.setTextColor.apply(doc, G700);
                    }
                    doc.text(val || '', cx + 2, curY + 5);
                    cx += colW[ci];
                });
            curY += 7;
        });

        /* summary row */
        var allOk   = medRows.every(function (r) { return r.resultado === 'OK'; });
        var anyFail = medRows.some(function (r)  { return r.resultado === 'FAIL'; });
        checkBreak(8);
        doc.setFillColor.apply(doc, anyFail ? RED_C : allOk ? GREEN_C : AMBER_C);
        doc.rect(MARGIN, curY, tableW, 7, 'F');
        doc.setFontSize(7.5);
        doc.setTextColor.apply(doc, WHITE);
        doc.setFont(undefined, 'bold');
        var sumTxt = anyFail ? 'PUNTOS FUERA DE TOLERANCIA' : allOk ? 'TODOS EN TOLERANCIA' : 'VERIFICACIÓN PENDIENTE';
        doc.text(sumTxt, MARGIN + tableW / 2, curY + 5, { align: 'center' });
        curY += 7 + 8;

        /* ========== OBSERVACIONES ========== */
        var obs = (f.observaciones || '').trim();
        var rec = (f.recomendaciones || '').trim();
        if (obs || rec) {
            sectionHeader('Observaciones y Recomendaciones');
            if (obs) {
                doc.setFontSize(7);
                doc.setTextColor.apply(doc, G500);
                doc.setFont(undefined, 'bold');
                doc.text('OBSERVACIONES', MARGIN, curY);
                curY += 5;
                doc.setFontSize(9);
                doc.setTextColor.apply(doc, G700);
                doc.setFont(undefined, 'normal');
                var obsL = doc.splitTextToSize(obs, CONT_W - 4);
                checkBreak(obsL.length * 4.5 + 4);
                doc.text(obsL, MARGIN, curY);
                curY += obsL.length * 4.5 + 6;
            }
            if (rec) {
                checkBreak(14);
                doc.setFontSize(7);
                doc.setTextColor.apply(doc, G500);
                doc.setFont(undefined, 'bold');
                doc.text('RECOMENDACIONES', MARGIN, curY);
                curY += 5;
                doc.setFontSize(9);
                doc.setTextColor.apply(doc, G700);
                doc.setFont(undefined, 'normal');
                var recL = doc.splitTextToSize(rec, CONT_W - 4);
                checkBreak(recL.length * 4.5 + 4);
                doc.text(recL, MARGIN, curY);
                curY += recL.length * 4.5 + 6;
            }
            curY += 4;
        }

        /* ========== FOTOS ========== */
        var fotos = f.fotos || [];
        if (fotos.length > 0) {
            sectionHeader('Fotografías del Servicio');
            var imgW  = (CONT_W - 8) / 2;
            var imgH  = 58;
            var col   = 0;

            fotos.forEach(function (foto) {
                if (col === 0) checkBreak(imgH + 18);
                var x = MARGIN + col * (imgW + 8);
                try {
                    doc.addImage(foto.data, imgFmt(foto.data), x, curY, imgW, imgH);
                    if (foto.caption) {
                        doc.setFontSize(7.5);
                        doc.setTextColor.apply(doc, G600);
                        doc.setFont(undefined, 'italic');
                        doc.text(foto.caption, x, curY + imgH + 5, { maxWidth: imgW });
                    }
                } catch (e) { /* skip corrupted image */ }
                col++;
                if (col >= 2) {
                    col = 0;
                    curY += imgH + 14;
                }
            });
            if (col > 0) curY += imgH + 14;
            curY += 4;
        }

        /* ========== FIRMA ========== */
        sectionHeader('Firmas de Conformidad');
        checkBreak(52);
        var sigW = (CONT_W / 2) - 8;

        if (f.firma) {
            try { doc.addImage(f.firma, 'PNG', MARGIN, curY, sigW, 26); } catch (e) {}
        }

        doc.setDrawColor.apply(doc, G300);
        doc.setLineWidth(0.4);
        doc.line(MARGIN, curY + 30, MARGIN + sigW, curY + 30);
        doc.setLineDashPattern([1, 2], 0);
        doc.line(MARGIN + sigW + 12, curY + 30, PAGE_W - MARGIN, curY + 30);
        doc.setLineDashPattern([], 0);

        doc.setFontSize(8);
        doc.setTextColor.apply(doc, G700);
        doc.setFont(undefined, 'bold');
        doc.text(f.tecnico || 'Técnico Responsable', MARGIN, curY + 36);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(7.5);
        doc.setTextColor.apply(doc, G500);
        doc.text(EMPRESA.nombre, MARGIN, curY + 41);

        doc.setFontSize(8);
        doc.setTextColor.apply(doc, G700);
        doc.setFont(undefined, 'bold');
        doc.text('Representante del Cliente', MARGIN + sigW + 12, curY + 36);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(7.5);
        doc.setTextColor.apply(doc, G500);
        var clienteLabel = f.clienteEmpresa || f.clienteNombre || '';
        if (clienteLabel) doc.text(clienteLabel, MARGIN + sigW + 12, curY + 41);

        curY += 52;

        /* ========== GARANTÍA ========== */
        var garantiaTxt = 'El servicio de ' + (f.tipoServicio || 'Servicio Técnico') +
            ' cuenta con una garantía de 30 días naturales a partir de la fecha de entrega. ' +
            'Esta garantía cubre exclusivamente las fallas relacionadas con el trabajo realizado en esta orden. ' +
            'No cubre daños por mal uso, accidentes, negligencia o condiciones externas.';
        var gLines = doc.splitTextToSize(garantiaTxt, CONT_W - 12);
        var gH = gLines.length * 4.2 + 14;
        checkBreak(gH);
        doc.setFillColor.apply(doc, AMBER_BG);
        doc.rect(MARGIN, curY, CONT_W, gH, 'F');
        doc.setDrawColor.apply(doc, AMBER_C);
        doc.setLineWidth(0.6);
        doc.rect(MARGIN, curY, CONT_W, gH, 'S');
        doc.setFontSize(8);
        doc.setTextColor.apply(doc, G700);
        doc.setFont(undefined, 'bold');
        doc.text('GARANTÍA', MARGIN + 5, curY + 8);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        doc.text(gLines, MARGIN + 5, curY + 14);
        curY += gH + 6;

        /* final footer */
        footer();

        /* ---- save ---- */
        var folio = (f.folio || 'SIN-FOLIO').replace(/[^a-zA-Z0-9\-_]/g, '');
        var fecha = (f.fecha || new Date().toISOString().split('T')[0]).replace(/-/g, '');
        doc.save('Reporte-MAC-' + folio + '-' + fecha + '.pdf');

        window.showToast && window.showToast('PDF generado y descargado correctamente', 'success');
    }

    /* ---- load jsPDF on demand ---- */

    function loadAndBuild() {
        if (window.jspdf && window.jspdf.jsPDF) {
            build(window.jspdf.jsPDF);
            return;
        }
        if (window.jsPDF) {
            build(window.jsPDF);
            return;
        }
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.2/jspdf.umd.min.js';
        script.onload = function () {
            var cls = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF;
            if (cls) { build(cls); }
            else { window.showToast && window.showToast('Error: no se pudo cargar jsPDF', 'error'); }
        };
        script.onerror = function () {
            window.showToast && window.showToast('Error al cargar la librería de PDF', 'error');
        };
        document.head.appendChild(script);
    }

    window.pdfGenerator = {
        generate: function () { loadAndBuild(); }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('btn-generate-pdf');
        if (btn) {
            btn.addEventListener('click', function () { loadAndBuild(); });
        }
    });

}());
