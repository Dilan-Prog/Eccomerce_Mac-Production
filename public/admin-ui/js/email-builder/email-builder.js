/*
 * AU.EmailBuilder — editor visual de plantillas de correo.
 *
 * Este código vivía embebido en
 * resources/views/admin-ui/email-templates/form.blade.php. Se sacó a un
 * módulo propio porque el editor ahora se monta de dos maneras:
 *  - como página completa (admin/email-templates/create|edit), y
 *  - inyectado por AJAX dentro de la pantalla de pestañas de Email
 *    Marketing, donde un <script> dentro del fragmento nunca se ejecutaría
 *    (innerHTML no corre scripts).
 *
 * Uso:
 *   AU.EmailBuilder.mount({
 *     root: document.getElementById('eb-editor'),
 *     initialData: {theme, blocks} | null,   // blocks_json guardado
 *     isEdit: bool,
 *     logoUrl: '...',                        // logo para el bloque Logo
 *     previewUrl: '/admin/email-templates/preview-blocks',
 *   });
 *
 * Lo que cambió respecto de la versión embebida, además del empaquetado:
 *  - La vista previa dejó de ser un modal con botón y es el panel derecho
 *    fijo, refrescado solo (con debounce) desde syncJson() y desde el
 *    textarea de HTML crudo.
 *  - El switch "Modo avanzado" es ahora el segmentado "Vista sencilla /
 *    Vista avanzada"; el modo sigue viajando en el mismo input oculto.
 *  - Se agregaron el contador de caracteres y los chips de token.
 * La forma del JSON que termina en #blocks-json-field NO cambió.
 */
window.AU = window.AU || {};

(function () {
    AU.EmailBuilder = {
        /**
         * Lee la configuración que el propio fragmento trae embebida como
         * JSON inerte (#eb-config). Devuelve el objeto listo para mount(),
         * con `root` ya resuelto.
         */
        readConfig: function (scope) {
            var host = scope || document;
            var node = host.querySelector('#eb-config');
            var config = {};
            if (node) {
                try {
                    config = JSON.parse(node.textContent) || {};
                } catch (err) {
                    config = {};
                }
            }
            config.root = host.querySelector('#eb-editor');
            return config;
        },

        mount: function (config) {
            var root = config.root || document.getElementById('eb-editor');
            if (!root) return;

            // ================================================================
            // Editor visual por bloques — contrato de datos compartido con el
            // backend (App\Support\BlockEmailRenderer, en construcción en
            // paralelo). NO cambies la forma del JSON sin coordinarlo:
            //
            // {
            //   "theme": { "backgroundColor": "#F4F6F8" },
            //   "blocks": [
            //     { "id": "b1", "type": "logo", "settings": {...} },
            //     { "id": "b2", "type": "heading", "content": "...", "settings": {...} },
            //     ...
            //   ]
            // }
            //
            // La interfaz de abajo (lienzo, capas, inspector) es solo una capa
            // de presentación sobre este mismo estado — la forma del JSON que
            // termina en #blocks-json-field no cambia.
            // ================================================================

            var themeBgInput = document.getElementById('eb-theme-bg');
            var jsonField = document.getElementById('blocks-json-field');
            var bodyHiddenField = document.getElementById('body-hidden');
            var builderModeField = document.getElementById('builder-mode-field');
            var form = jsonField.closest('form');

            // ---- Toggle "Modo avanzado" (HTML crudo en vez del editor de bloques) ----
            var advancedModeValue = document.getElementById('eb-advanced-mode-value');

            var layersHost = document.getElementById('eb-layers-list');
            var layersCountBadge = document.getElementById('eb-layers-count');
            var validationHost = document.getElementById('eb-validation-list');
            var canvasPaper = document.getElementById('eb-canvas-paper');
            var canvasBlocksHost = document.getElementById('eb-canvas-blocks');
            var inspectorHost = document.getElementById('eb-inspector');

            var BLOCK_LABELS = {
                logo: 'Logo',
                heading: 'Encabezado',
                text: 'Texto',
                products: 'Productos recomendados',
                coupon: 'Cupón',
                button: 'Botón',
                divider: 'Divisor',
                spacer: 'Espaciador',
                footer: 'Pie de página'
            };

            var BLOCK_ICONS = {
                logo: 'fa-image',
                heading: 'fa-heading',
                text: 'fa-align-left',
                products: 'fa-boxes-stacked',
                coupon: 'fa-tag',
                button: 'fa-hand-pointer',
                divider: 'fa-minus',
                spacer: 'fa-up-down',
                footer: 'fa-shoe-prints'
            };

            // Dato inicial: null si la plantilla es nueva o vieja (solo "body" plano).
            var initialData = config.initialData || null;
            var hasSavedBlocksJson = !!(initialData && Array.isArray(initialData.blocks));
            var isEditTemplate = !!config.isEdit;

            var state = {
                theme: { backgroundColor: '#F4F6F8' },
                blocks: [],
                selectedId: null
            };

            if (hasSavedBlocksJson) {
                state.theme.backgroundColor = (initialData.theme && initialData.theme.backgroundColor) || '#F4F6F8';
                // Copia profunda simple para no mutar accidentalmente el objeto original.
                state.blocks = initialData.blocks.map(function (b) {
                    var copy = JSON.parse(JSON.stringify(b));
                    // Un "settings" vacío puede llegar como [] en vez de {} (PHP
                    // castea arrays vacíos a [] al codificar JSON) — normalizarlo
                    // para no perder propiedades al reserializar más adelante.
                    if (!copy.settings || Array.isArray(copy.settings) || typeof copy.settings !== 'object') {
                        copy.settings = {};
                    }
                    return copy;
                });
            }

            themeBgInput.value = state.theme.backgroundColor;

            var quillInstances = {};
            var idCounter = 0;

            function nextId() {
                idCounter += 1;
                return 'b' + Date.now().toString(36) + idCounter;
            }

            function escAttr(value) {
                return String(value === null || value === undefined ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function defaultBlock(type) {
                var id = nextId();
                switch (type) {
                    case 'logo':
                        return { id: id, type: type, settings: { align: 'center', width: 160 } };
                    case 'heading':
                        return { id: id, type: type, content: '', settings: { color: '#0B4C87', backgroundColor: '#FFFFFF', align: 'left', fontSize: 22 } };
                    case 'text':
                        return { id: id, type: type, content: '<p></p>', settings: { color: '#333333', backgroundColor: '#FFFFFF' } };
                    case 'products':
                        return { id: id, type: type, settings: { backgroundColor: '#F5F7F9' } };
                    case 'coupon':
                        return { id: id, type: type, settings: { backgroundColor: '#FFF7E6' } };
                    case 'button':
                        return { id: id, type: type, content: 'Ver oferta', settings: { url: 'https://', backgroundColor: '#0B4C87', textColor: '#FFFFFF', align: 'center' } };
                    case 'divider':
                        return { id: id, type: type, settings: { color: '#DDDDDD' } };
                    case 'spacer':
                        return { id: id, type: type, settings: { height: 20 } };
                    case 'footer':
                        return { id: id, type: type, content: '<p></p>', settings: { color: '#8A94A6', backgroundColor: '#FFFFFF' } };
                    default:
                        return { id: id, type: type, settings: {} };
                }
            }

            function setPath(obj, path, value) {
                var parts = path.split('.');
                var target = obj;
                for (var i = 0; i < parts.length - 1; i++) {
                    if (typeof target[parts[i]] !== 'object' || target[parts[i]] === null) {
                        target[parts[i]] = {};
                    }
                    target = target[parts[i]];
                }
                target[parts[parts.length - 1]] = value;
            }

            function findBlock(id) {
                for (var i = 0; i < state.blocks.length; i++) {
                    if (state.blocks[i].id === id) return state.blocks[i];
                }
                return null;
            }

            function syncJson() {
                var payload = {
                    theme: { backgroundColor: state.theme.backgroundColor },
                    blocks: state.blocks
                };
                jsonField.value = JSON.stringify(payload);
                // Mismo criterio que el aviso "editor de texto simple todavía":
                // hay bloques reales -> 'blocks', si no -> 'code'. Se
                // recalcula aquí (en vez de solo al agregar el primer
                // bloque) para que también se actualice si se borran todos.
                builderModeField.value = state.blocks.length ? 'blocks' : 'code';
                renderValidation();
                // Punto único por el que pasa cualquier cambio de bloques — de aquí
                // cuelga el refresco de la vista previa en vivo, en vez de engancharlo
                // acción por acción.
                schedulePreview();
            }

            // ---- Generación de campos (HTML strings), usados por el inspector ----

            function colorField(label, path, value) {
                return '<div class="eb-field"><label>' + label + '</label>' +
                    '<input type="color" class="eb-color-input" data-field="' + path + '" value="' + escAttr(value || '#FFFFFF') + '"></div>';
            }

            function numberField(label, path, value, min, max) {
                return '<div class="eb-field"><label>' + label + '</label>' +
                    '<input type="number" class="au-input eb-number-input" data-field="' + path + '" value="' + escAttr(value) + '" min="' + min + '" max="' + max + '"></div>';
            }

            function textField(label, path, value, placeholder) {
                return '<div class="eb-field eb-field-wide"><label>' + label + '</label>' +
                    '<input type="text" class="au-input" data-field="' + path + '" value="' + escAttr(value) + '" placeholder="' + escAttr(placeholder || '') + '"></div>';
            }

            function urlField(label, path, value) {
                return '<div class="eb-field eb-field-wide"><label>' + label + '</label>' +
                    '<input type="url" class="au-input" data-field="' + path + '" value="' + escAttr(value) + '" placeholder="https://"></div>';
            }

            function alignField(path, value) {
                var opts = [
                    ['left', 'Izquierda'],
                    ['center', 'Centro'],
                    ['right', 'Derecha']
                ].map(function (pair) {
                    return '<option value="' + pair[0] + '" ' + (value === pair[0] ? 'selected' : '') + '>' + pair[1] + '</option>';
                }).join('');
                return '<div class="eb-field"><label>Alineación</label>' +
                    '<select class="au-select" data-field="' + path + '">' + opts + '</select></div>';
            }

            // ---- Contenido de cada bloque, tal como se vería en el correo real.
            // Compartido entre el lienzo central (vista previa en vivo) y el
            // fallback HTML del campo "body" — una sola fuente de verdad. ----
            function renderBlockHtml(block) {
                var s = block.settings || {};
                switch (block.type) {
                    case 'logo':
                        return '<div style="text-align:' + (s.align || 'center') + ';padding:12px"><img src="' + config.logoUrl + '" width="' + (s.width || 160) + '" alt="Logo"></div>';
                    case 'heading':
                        return '<h2 style="margin:0;padding:14px;color:' + (s.color || '#0B4C87') + ';background:' + (s.backgroundColor || '#FFFFFF') + ';text-align:' + (s.align || 'left') + ';font-size:' + (s.fontSize || 22) + 'px">' + (block.content || '') + '</h2>';
                    case 'text':
                        return '<div style="padding:14px;color:' + (s.color || '#333333') + ';background:' + (s.backgroundColor || '#FFFFFF') + '">' + (block.content || '') + '</div>';
                    case 'products':
                        return '<div style="padding:14px;background:' + (s.backgroundColor || '#F5F7F9') + '">{{productos}}</div>';
                    case 'coupon':
                        return '<div style="padding:14px;background:' + (s.backgroundColor || '#FFF7E6') + '">{{cupon_bloque}}</div>';
                    case 'button':
                        return '<div style="padding:14px;text-align:' + (s.align || 'center') + '"><a href="' + (s.url || '#') + '" style="display:inline-block;padding:10px 22px;border-radius:4px;text-decoration:none;background:' + (s.backgroundColor || '#0B4C87') + ';color:' + (s.textColor || '#FFFFFF') + '">' + (block.content || 'Ver oferta') + '</a></div>';
                    case 'divider':
                        return '<hr style="border:none;border-top:1px solid ' + (s.color || '#DDDDDD') + ';margin:0">';
                    case 'spacer':
                        return '<div style="height:' + (s.height || 20) + 'px;line-height:' + (s.height || 20) + 'px">&nbsp;</div>';
                    case 'footer':
                        return '<div style="padding:14px;font-size:12px;color:' + (s.color || '#8A94A6') + ';background:' + (s.backgroundColor || '#FFFFFF') + '">' + (block.content || '') + '</div>';
                    default:
                        return '';
                }
            }

            // ---- Paneles "Contenido" / "Estilo" del inspector, por tipo de bloque ----
            function inspectorPanels(block) {
                var s = block.settings || {};
                switch (block.type) {
                    case 'logo':
                        return {
                            content: '<div class="eb-logo-preview"><img src="' + config.logoUrl + '" alt="Logo" style="max-width:160px;display:block"></div>' +
                                '<p class="eb-inspector-note">Este bloque siempre usa el logo del sistema — ajusta su alineación y ancho en la pestaña Estilo.</p>',
                            style: '<div class="eb-fields-row">' + alignField('settings.align', s.align) + numberField('Ancho (px)', 'settings.width', s.width, 40, 400) + '</div>'
                        };
                    case 'heading':
                        return {
                            content: textField('Contenido', 'content', block.content, 'Ej. Ofertas para ti {{nombre_cliente}}'),
                            style: '<div class="eb-fields-row">' + colorField('Color de texto', 'settings.color', s.color) + colorField('Color de fondo', 'settings.backgroundColor', s.backgroundColor) + alignField('settings.align', s.align) + numberField('Tamaño de fuente', 'settings.fontSize', s.fontSize, 10, 60) + '</div>'
                        };
                    case 'text':
                    case 'footer':
                        return {
                            content: '<div class="eb-quill-mount" id="quill-mount-' + block.id + '"></div>',
                            style: '<div class="eb-fields-row">' + colorField('Color de texto', 'settings.color', s.color) + colorField('Color de fondo', 'settings.backgroundColor', s.backgroundColor) + '</div>'
                        };
                    case 'products':
                        return {
                            content: '<p class="eb-inspector-note">Este bloque se rellena automáticamente con los productos recomendados de cada cliente al momento de enviar.</p>',
                            style: '<div class="eb-fields-row">' + colorField('Color de fondo', 'settings.backgroundColor', s.backgroundColor) + '</div>'
                        };
                    case 'coupon':
                        return {
                            content: '<p class="eb-inspector-note">Este bloque se rellena automáticamente con el cupón de cada cliente al momento de enviar.</p>',
                            style: '<div class="eb-fields-row">' + colorField('Color de fondo', 'settings.backgroundColor', s.backgroundColor) + '</div>'
                        };
                    case 'button':
                        return {
                            content: textField('Texto del botón', 'content', block.content, 'Ej. Ver oferta') + urlField('URL', 'settings.url', s.url),
                            style: '<div class="eb-fields-row">' + colorField('Color de fondo', 'settings.backgroundColor', s.backgroundColor) + colorField('Color de texto', 'settings.textColor', s.textColor) + alignField('settings.align', s.align) + '</div>'
                        };
                    case 'divider':
                        return {
                            content: '<p class="eb-inspector-note">Este bloque no tiene contenido, solo una línea divisoria.</p>',
                            style: '<div class="eb-fields-row">' + colorField('Color', 'settings.color', s.color) + '</div>'
                        };
                    case 'spacer':
                        return {
                            content: '<p class="eb-inspector-note">Este bloque no tiene contenido, solo espacio en blanco.</p>',
                            style: '<div class="eb-fields-row">' + numberField('Alto (px)', 'settings.height', s.height, 4, 200) + '</div>'
                        };
                    default:
                        return { content: '', style: '' };
                }
            }

            function mountQuill(block) {
                var mountEl = document.getElementById('quill-mount-' + block.id);
                if (!mountEl || typeof Quill === 'undefined') return;

                var quill = new Quill(mountEl, {
                    theme: 'snow',
                    placeholder: 'Escribe el contenido…',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ color: [] }, { background: [] }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                if (block.content && block.content.trim() !== '') {
                    quill.clipboard.dangerouslyPasteHTML(block.content);
                }

                quill.on('text-change', function () {
                    block.content = quill.root.innerHTML;
                    syncJson();
                    renderCanvas();
                    renderLayers();
                });

                quillInstances[block.id] = quill;
            }

            // ---- Validación (panel inferior izquierdo) ----
            function computeValidationMessages() {
                var msgs = [];

                if (!state.blocks.length) {
                    msgs.push({ level: 'info', text: 'Agrega al menos un bloque para armar el correo.' });
                    return msgs;
                }

                var emptyText = state.blocks.filter(function (b) {
                    return (b.type === 'heading' || b.type === 'text' || b.type === 'footer') &&
                        (!b.content || b.content.replace(/<[^>]*>/g, '').trim() === '');
                }).length;
                if (emptyText) {
                    msgs.push({ level: 'warning', text: emptyText + ' bloque' + (emptyText > 1 ? 's' : '') + ' de texto sin contenido.' });
                }

                var badButtons = state.blocks.filter(function (b) {
                    return b.type === 'button' && (!b.settings || !b.settings.url || b.settings.url === 'https://' || String(b.settings.url).trim() === '');
                }).length;
                if (badButtons) {
                    msgs.push({ level: 'warning', text: badButtons + ' botón' + (badButtons > 1 ? 'es' : '') + ' sin URL configurada.' });
                }

                if (!msgs.length) {
                    msgs.push({ level: 'success', text: 'Sin advertencias detectadas.' });
                }

                return msgs;
            }

            function renderValidation() {
                var msgs = computeValidationMessages();
                validationHost.innerHTML = msgs.map(function (m) {
                    var icon = m.level === 'warning' ? 'fa-triangle-exclamation' : (m.level === 'success' ? 'fa-circle-check' : 'fa-circle-info');
                    return '<div class="eb-validation-item eb-validation-' + m.level + '"><i class="fas ' + icon + '"></i><span>' + m.text + '</span></div>';
                }).join('');
            }

            // ---- Lista de capas (columna izquierda) ----
            function layerSnippet(block) {
                if (typeof block.content === 'string' && block.content) {
                    var text = block.content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                    if (text) return escAttr(text.length > 30 ? text.slice(0, 30) + '…' : text);
                }
                return '';
            }

            function renderLayers() {
                layersCountBadge.textContent = state.blocks.length;

                if (!state.blocks.length) {
                    layersHost.innerHTML = '<p class="eb-layers-empty">Sin bloques todavía.</p>';
                    return;
                }

                layersHost.innerHTML = state.blocks.map(function (block, index) {
                    var snippet = layerSnippet(block);
                    var isSelected = block.id === state.selectedId;
                    return '<div class="eb-layer-row' + (isSelected ? ' is-selected' : '') + '" data-block-id="' + block.id + '">' +
                            '<span class="eb-layer-index">' + (index + 1) + '</span>' +
                            '<span class="eb-layer-icon"><i class="fas ' + (BLOCK_ICONS[block.type] || 'fa-cube') + '"></i></span>' +
                            '<span class="eb-layer-info">' +
                                '<span class="eb-layer-name">' + (BLOCK_LABELS[block.type] || block.type) + '</span>' +
                                (snippet ? '<span class="eb-layer-snippet">' + snippet + '</span>' : '') +
                            '</span>' +
                        '</div>';
                }).join('');
            }

            // ---- Lienzo central (vista previa en vivo) ----
            function canvasBlockHtml(block, index) {
                var isSelected = block.id === state.selectedId;
                var isFirst = index === 0;
                var isLast = index === state.blocks.length - 1;
                return '<div class="eb-canvas-block' + (isSelected ? ' is-selected' : '') + '" data-block-id="' + block.id + '">' +
                        '<div class="eb-canvas-toolbar">' +
                            '<button type="button" class="eb-canvas-tool-btn" data-action="move-up" ' + (isFirst ? 'disabled' : '') + ' title="Subir"><i class="fas fa-arrow-up"></i></button>' +
                            '<button type="button" class="eb-canvas-tool-btn" data-action="move-down" ' + (isLast ? 'disabled' : '') + ' title="Bajar"><i class="fas fa-arrow-down"></i></button>' +
                            '<button type="button" class="eb-canvas-tool-btn" data-action="duplicate" title="Duplicar"><i class="fas fa-copy"></i></button>' +
                            '<button type="button" class="eb-canvas-tool-btn eb-canvas-tool-critical" data-action="delete" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                        '</div>' +
                        '<div class="eb-canvas-block-inner">' + renderBlockHtml(block) + '</div>' +
                    '</div>';
            }

            function renderCanvas() {
                canvasPaper.style.background = state.theme.backgroundColor;

                if (!state.blocks.length) {
                    canvasBlocksHost.innerHTML = '<p class="eb-canvas-empty">Agrega un bloque desde la izquierda para verlo aquí.</p>';
                    return;
                }

                canvasBlocksHost.innerHTML = state.blocks.map(canvasBlockHtml).join('');
            }

            // ---- Inspector (columna derecha) ----
            function renderInspector() {
                var block = findBlock(state.selectedId);
                quillInstances = {};

                if (!block) {
                    inspectorHost.innerHTML = '<div class="eb-inspector-empty"><i class="fas fa-arrow-pointer"></i><p>Selecciona un bloque del lienzo o de la lista de capas para editarlo.</p></div>';
                    return;
                }

                var panels = inspectorPanels(block);
                var index = state.blocks.indexOf(block);

                inspectorHost.innerHTML =
                    '<div class="eb-inspector-header">' +
                        '<span class="eb-inspector-icon"><i class="fas ' + (BLOCK_ICONS[block.type] || 'fa-cube') + '"></i></span>' +
                        '<div>' +
                            '<div class="eb-inspector-title">' + (BLOCK_LABELS[block.type] || block.type) + '</div>' +
                            '<div class="eb-inspector-sub">Bloque ' + (index + 1) + ' de ' + state.blocks.length + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="eb-tabs">' +
                        '<button type="button" class="eb-tab-btn is-active" data-tab="content">Contenido</button>' +
                        '<button type="button" class="eb-tab-btn" data-tab="style">Estilo</button>' +
                    '</div>' +
                    '<div class="eb-tab-panel" data-tab-panel="content">' + panels.content + '</div>' +
                    '<div class="eb-tab-panel" data-tab-panel="style" style="display:none">' + panels.style + '</div>';

                if (block.type === 'text' || block.type === 'footer') {
                    mountQuill(block);
                }
            }

            // ---- Orquestación de selección / cambios estructurales ----
            function selectBlock(id) {
                if (state.selectedId === id) return;
                state.selectedId = id;
                renderLayers();
                renderCanvas();
                renderInspector();
            }

            function renderAll() {
                renderLayers();
                renderCanvas();
                renderInspector();
                syncJson();
            }

            function swapBlocks(i, j) {
                var tmp = state.blocks[i];
                state.blocks[i] = state.blocks[j];
                state.blocks[j] = tmp;
            }

            function handleBlockAction(action, blockId) {
                var index = state.blocks.findIndex(function (b) { return b.id === blockId; });
                if (index === -1) return;

                if (action === 'move-up' && index > 0) {
                    swapBlocks(index, index - 1);
                    renderAll();
                } else if (action === 'move-down' && index < state.blocks.length - 1) {
                    swapBlocks(index, index + 1);
                    renderAll();
                } else if (action === 'duplicate') {
                    var clone = JSON.parse(JSON.stringify(state.blocks[index]));
                    clone.id = nextId();
                    state.blocks.splice(index + 1, 0, clone);
                    state.selectedId = clone.id;
                    renderAll();
                } else if (action === 'delete') {
                    if (window.confirm('¿Eliminar este bloque? Esta acción no se puede deshacer.')) {
                        state.blocks.splice(index, 1);
                        if (state.selectedId === blockId) {
                            var next = state.blocks[index] || state.blocks[index - 1];
                            state.selectedId = next ? next.id : null;
                        }
                        renderAll();
                    }
                }
            }

            // ---- Eventos: lista de capas (seleccionar) ----
            layersHost.addEventListener('click', function (e) {
                var row = e.target.closest('.eb-layer-row');
                if (!row) return;
                selectBlock(row.getAttribute('data-block-id'));
            });

            // ---- Eventos: lienzo (seleccionar / subir / bajar / duplicar / eliminar) ----
            canvasBlocksHost.addEventListener('click', function (e) {
                var blockEl = e.target.closest('.eb-canvas-block');
                if (!blockEl) return;
                var blockId = blockEl.getAttribute('data-block-id');

                var actionBtn = e.target.closest('[data-action]');
                if (actionBtn) {
                    e.stopPropagation();
                    handleBlockAction(actionBtn.getAttribute('data-action'), blockId);
                    return;
                }

                selectBlock(blockId);
            });

            // ---- Eventos: inspector (pestañas + edición de campos) ----
            inspectorHost.addEventListener('click', function (e) {
                var tabBtn = e.target.closest('.eb-tab-btn');
                if (!tabBtn) return;
                var tab = tabBtn.getAttribute('data-tab');
                inspectorHost.querySelectorAll('.eb-tab-btn').forEach(function (b) {
                    b.classList.toggle('is-active', b === tabBtn);
                });
                inspectorHost.querySelectorAll('.eb-tab-panel').forEach(function (p) {
                    p.style.display = (p.getAttribute('data-tab-panel') === tab) ? 'flex' : 'none';
                });
            });

            function handleFieldEvent(e) {
                var el = e.target;
                var field = el.getAttribute && el.getAttribute('data-field');
                if (!field) return;

                var block = findBlock(state.selectedId);
                if (!block) return;

                var value = el.value;
                if (el.type === 'number') {
                    value = value === '' ? 0 : Number(value);
                }

                setPath(block, field, value);
                syncJson();
                renderCanvas();
                renderLayers();
            }

            inspectorHost.addEventListener('input', handleFieldEvent);
            inspectorHost.addEventListener('change', handleFieldEvent);

            // ---- Barra "Agregar bloque" ----
            document.querySelectorAll('.eb-add-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var type = btn.getAttribute('data-type');
                    var block = defaultBlock(type);
                    state.blocks.push(block);
                    state.selectedId = block.id;
                    renderAll();
                });
            });

            // ---- Color de fondo global ----
            themeBgInput.addEventListener('input', function () {
                state.theme.backgroundColor = themeBgInput.value;
                renderCanvas();
                syncJson();
            });

            // ---- Toggle de dispositivo (Escritorio / Móvil) ----
            // Aplica a las dos caras del panel derecho: el lienzo de bloques (vista
            // sencilla) y el iframe de vista previa (vista avanzada).
            var previewPane = document.getElementById('eb-preview-pane');
            root.querySelectorAll('.eb-device-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var isMobile = btn.getAttribute('data-device') === 'mobile';
                    root.querySelectorAll('.eb-device-btn').forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    canvasPaper.classList.toggle('eb-canvas-paper--mobile', isMobile);
                    if (previewPane) previewPane.classList.toggle('eb-preview-pane--mobile', isMobile);
                });
            });

            // ---- Toggle "Modo avanzado" ----
            // Refleja el estado visual del switch (usado también para activarlo
            // programáticamente al cargar, sin simular un click real).
            function applyAdvancedModeUI(isOn) {
                root.querySelectorAll('.eb-mode-btn').forEach(function (b) {
                    b.classList.toggle('is-active', (b.getAttribute('data-mode') === 'code') === isOn);
                });
                // El panel derecho enseña el lienzo en vista sencilla y el iframe de
                // vista previa en vista avanzada.
                root.classList.toggle('eb-editor--advanced', isOn);
                advancedModeValue.value = isOn ? '1' : '0';
            }

            // Aplica el efecto real de encender/apagar el modo avanzado: muestra u
            // oculta el editor de bloques vs. el textarea de HTML, y vacía el
            // arreglo de bloques al encenderlo (buildFallbackBodyHtml está definido
            // más abajo pero, al ser "function foo(){}", queda disponible en todo
            // este scope gracias al hoisting).
            function setAdvancedMode(isOn) {
                if (isOn) {
                    // Si había bloques armados, se vuelca su HTML equivalente al
                    // textarea en vez de dejar el último "body" guardado — así no se
                    // pierde de vista lo que se veía en el lienzo. Si no había
                    // bloques (plantilla nueva, o plantilla vieja sin blocks_json),
                    // el body original se conserva tal cual.
                    var fallback = buildFallbackBodyHtml();
                    if (fallback !== null) {
                        bodyHiddenField.value = fallback;
                    }
                    state.blocks = [];
                    state.selectedId = null;
                    bodyHiddenField.classList.add('eb-html-textarea');
                } else {
                    bodyHiddenField.classList.remove('eb-html-textarea');
                }
                updateHtmlCount();
                // syncJson() (dentro de renderAll) manda blocks_json vacío y
                // recalcula builder_mode a 'code' cuando no hay bloques.
                renderAll();
            }

            root.querySelectorAll('.eb-mode-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var wantsAdvanced = btn.getAttribute('data-mode') === 'code';
                    if ((advancedModeValue.value === '1') === wantsAdvanced) return;
                    applyAdvancedModeUI(wantsAdvanced);
                    setAdvancedMode(wantsAdvanced);
                });
            });

            // ---- Fallback HTML simple para el campo "body" heredado ----
            // Mientras el backend no consuma blocks_json de forma exclusiva, esto
            // mantiene el campo "body" (todavía required en el backend) con un HTML
            // equivalente a los bloques armados, reutilizando los mismos placeholders
            // {{productos}} / {{cupon_bloque}} que ya sabe reemplazar el renderer
            // actual. Si no hay bloques (plantilla vieja sin migrar), no se toca el
            // body original para no perder su contenido.
            function buildFallbackBodyHtml() {
                if (!state.blocks.length) return null;
                var rows = state.blocks.map(renderBlockHtml).join('\n');
                return '<div style="background:' + state.theme.backgroundColor + ';padding:16px">' + rows + '</div>';
            }

            form.addEventListener('submit', function () {
                syncJson();
                var fallback = buildFallbackBodyHtml();
                if (fallback !== null) {
                    bodyHiddenField.value = fallback;
                }
            });

            // ---- Editor de HTML crudo: contador y chips de token ---------------
            // Los chips solo existen en vista avanzada: en vista sencilla el texto se
            // escribe dentro de los bloques (editor Quill), donde no hay una posición
            // de cursor en el textarea a la cual insertar.
            var htmlCount = document.getElementById('eb-html-count');

            function updateHtmlCount() {
                if (htmlCount) {
                    htmlCount.textContent = (bodyHiddenField.value || '').length.toLocaleString('es-MX') + ' caracteres';
                }
            }

            root.querySelectorAll('.eb-token-chip').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var token = chip.getAttribute('data-token') || '';
                    var start = bodyHiddenField.selectionStart;
                    var end = bodyHiddenField.selectionEnd;

                    // Si el textarea nunca tuvo foco, selectionStart es 0 y el token
                    // caería al principio del documento. Se anexa al final en ese caso.
                    if (document.activeElement !== bodyHiddenField && start === 0 && end === 0) {
                        start = end = bodyHiddenField.value.length;
                    }

                    bodyHiddenField.value = bodyHiddenField.value.slice(0, start) + token + bodyHiddenField.value.slice(end);
                    bodyHiddenField.focus();
                    bodyHiddenField.setSelectionRange(start + token.length, start + token.length);
                    updateHtmlCount();
                    schedulePreview();
                });
            });

            bodyHiddenField.addEventListener('input', function () {
                updateHtmlCount();
                schedulePreview();
            });

            // ---- Eco del asunto en el encabezado de la vista previa -------------
            var subjectInput = root.querySelector('[name="subject"]');
            var subjectEcho = document.getElementById('eb-preview-subject');

            function syncSubjectEcho() {
                if (!subjectEcho) return;
                var value = (subjectInput && subjectInput.value.trim()) || '';
                subjectEcho.textContent = value || '(sin asunto)';
            }

            if (subjectInput) subjectInput.addEventListener('input', syncSubjectEcho);

            // ---- Vista previa en vivo ----------------------------------------
            // Antes esto era un modal que se abría con un botón. Ahora es el panel
            // derecho fijo, y se refresca solo: cualquier cambio de bloques llama a
            // syncJson(), y el textarea de HTML crudo dispara su propio evento.
            //
            // Se refresca con retraso (debounce) porque cada refresco es un POST a
            // preview-blocks: sin eso, escribir HTML a mano dispararía una petición
            // por tecla.
            var previewFrame = document.getElementById('eb-preview-frame');
            var previewLoading = document.getElementById('eb-preview-loading');
            var previewError = document.getElementById('eb-preview-error');
            var previewTimer = null;
            var previewToken = 0;

            function schedulePreview() {
                if (previewTimer) clearTimeout(previewTimer);
                previewTimer = setTimeout(refreshPreview, 500);
            }

            function refreshPreview() {
                if (!previewFrame) return;

                // En modo avanzado no hay bloques (state.blocks queda vacío al
                // activarlo), así que blocks_json no tiene nada que armar — hay que
                // mandar el HTML crudo del textarea directo, o la vista previa sale
                // en blanco aunque el HTML escrito/pegado sea válido.
                var isAdvanced = advancedModeValue.value === '1';
                var payload = isAdvanced
                    ? { html: bodyHiddenField.value }
                    : { blocks_json: jsonField.value };

                previewError.style.display = 'none';
                previewLoading.style.display = 'block';

                // Cada refresco lleva su número: si el usuario sigue escribiendo y se
                // encima otra petición, la respuesta vieja que llegue tarde se ignora
                // en vez de pisar la nueva.
                previewToken += 1;
                var myToken = previewToken;

                fetch(config.previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': AU.csrfToken()
                    },
                    body: JSON.stringify(payload)
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (myToken !== previewToken) return;
                        previewLoading.style.display = 'none';
                        if (!data || typeof data.html !== 'string') {
                            throw new Error('Respuesta inválida del servidor');
                        }
                        previewFrame.srcdoc = data.html;
                        previewFrame.style.display = 'block';
                    })
                    .catch(function (err) {
                        if (myToken !== previewToken) return;
                        previewLoading.style.display = 'none';
                        previewError.style.display = 'block';
                        previewError.textContent = 'No se pudo generar la vista previa' + (err && err.message ? ' (' + err.message + ')' : '') + '. Tu contenido no se pierde — solo no se pudo dibujar la vista previa.';
                    });
            }

            // ---- Render inicial ----
            // Plantilla existente sin bloques guardados (el caso que antes solo
            // mostraba un aviso pasivo): se activa el modo avanzado de una vez, con
            // su "body" actual ya cargado en el textarea, en vez de mostrar un
            // editor de bloques vacío. Una plantilla nueva arranca en modo bloques
            // (comportamiento actual, sin cambios).
            syncSubjectEcho();
            updateHtmlCount();

            if (isEditTemplate && !hasSavedBlocksJson) {
                applyAdvancedModeUI(true);
                setAdvancedMode(true);
            } else {
                applyAdvancedModeUI(false);
                state.selectedId = state.blocks.length ? state.blocks[0].id : null;
                renderAll();
            }

            // Primera vista previa inmediata — sin esperar al debounce, para que el
            // panel derecho no arranque en blanco.
            refreshPreview();

        }
    };
})();
