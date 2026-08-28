@extends('admin-ui.layouts.master')

@php
    $isEdit = isset($emailTemplate) && $emailTemplate;
    $ebLogoUrl = asset('uploads/logo/2k-blanco-azul.png');
@endphp

@section('title', $isEdit ? 'Editar plantilla de correo' : 'Nueva plantilla de correo')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $isEdit ? 'Editar plantilla de correo' : 'Nueva plantilla de correo',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Plantillas de correo', 'url' => route('admin.email-templates.index')],
            ['label' => $isEdit ? $emailTemplate->name : 'Nueva'],
        ],
        'actions' => '<a href="' . route('admin.email-templates.index') . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver</a>',
    ])

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start">
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">{{ $isEdit ? 'Editar plantilla' : 'Nueva plantilla' }}</div>
            </div>
            <div class="au-card-body">
                <form action="{{ $isEdit ? route('admin.email-templates.update', $emailTemplate->id) : route('admin.email-templates.store') }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="au-field">
                        <label class="au-label">Nombre interno<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="name" value="{{ old('name', $emailTemplate->name ?? '') }}" placeholder="Ej. Oferta categoría Laptops" required>
                        <span class="au-help-text">Solo para identificarla en la lista — no aparece en el correo enviado.</span>
                    </div>

                    <div class="au-field">
                        <label class="au-label">Categoría</label>
                        <select class="au-select" name="category_id">
                            <option value="" {{ !old('category_id', $emailTemplate->category_id ?? null) ? 'selected' : '' }}>General / todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id', $emailTemplate->category_id ?? 0) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <span class="au-help-text">Se usa cuando esta es la categoría dominante del cliente. Deja "General / todas" para la plantilla de respaldo — se usa cuando no hay una específica para esa categoría (solo puede existir una plantilla activa "General / todas" a la vez de forma efectiva; si hay varias, se usa la primera).</span>
                    </div>

                    <div class="au-field">
                        <label class="au-label">Asunto del correo<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="subject" value="{{ old('subject', $emailTemplate->subject ?? '') }}" placeholder="Ej. Ofertas en @{{categoria}} para ti — Mac Del Norte" required>
                        <span class="au-help-text">Admite los mismos placeholders que el cuerpo (ver panel a la derecha).</span>
                    </div>

                    <div class="au-field">
                        <label class="au-label">Diseño del correo<span class="au-required-mark">*</span></label>
                        <span class="au-help-text" style="display:block;margin-bottom:12px">Arma el cuerpo del correo agregando bloques de contenido. Selecciona un bloque en el lienzo o en la lista de capas para editar sus propiedades a la derecha. Usa los placeholders del panel de la derecha dentro del texto de los bloques donde quieras que aparezca cada dato.</span>

                        <div id="eb-editor" class="eb-editor">
                            <div class="eb-topbar">
                                <div class="eb-topbar-left">
                                    <span class="eb-topbar-title"><i class="fas fa-layer-group"></i> Editor visual</span>
                                    <div class="eb-device-toggle">
                                        <button type="button" class="eb-device-btn is-active" data-device="desktop"><i class="fas fa-desktop"></i> Escritorio</button>
                                        <button type="button" class="eb-device-btn" data-device="mobile"><i class="fas fa-mobile-screen"></i> Móvil</button>
                                    </div>
                                </div>
                                <div class="eb-topbar-right">
                                    <div class="eb-field eb-field-inline" id="eb-advanced-toggle-wrap">
                                        <label>Modo avanzado</label>
                                        <label class="au-toggle" id="eb-advanced-toggle" data-au-toggle>
                                            <input type="checkbox" class="au-toggle-input" hidden>
                                            <span class="au-toggle-track"><span class="au-toggle-knob"></span></span>
                                        </label>
                                        <input type="hidden" id="eb-advanced-mode-value" data-au-toggle-value value="0">
                                    </div>
                                    <div class="eb-field eb-field-inline">
                                        <label>Fondo del correo</label>
                                        <input type="color" id="eb-theme-bg" class="eb-color-input" value="#F4F6F8">
                                    </div>
                                    <button type="button" id="eb-preview-btn" class="eb-btn eb-btn-primary"><i class="fas fa-eye"></i> Vista previa</button>
                                </div>
                            </div>

                            <div class="eb-workspace">
                                {{-- Columna izquierda: paleta de bloques + capas + validación --}}
                                <aside class="eb-col eb-col-left">
                                    <div class="eb-panel-section">
                                        <div class="eb-panel-label">Agregar bloque</div>
                                        <div class="eb-block-grid">
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="logo"><i class="fas fa-image"></i><span>Logo</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="heading"><i class="fas fa-heading"></i><span>Encabezado</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="text"><i class="fas fa-align-left"></i><span>Texto</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="products"><i class="fas fa-boxes-stacked"></i><span>Productos</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="coupon"><i class="fas fa-tag"></i><span>Cupón</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="button"><i class="fas fa-hand-pointer"></i><span>Botón</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="divider"><i class="fas fa-minus"></i><span>Divisor</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="spacer"><i class="fas fa-up-down"></i><span>Espaciador</span></button>
                                            <button type="button" class="eb-block-btn eb-add-btn" data-type="footer"><i class="fas fa-shoe-prints"></i><span>Pie de página</span></button>
                                        </div>
                                    </div>

                                    <div class="eb-panel-section eb-layers-section">
                                        <div class="eb-panel-label">Capas <span id="eb-layers-count" class="eb-count-badge">0</span></div>
                                        <div id="eb-layers-list" class="eb-layers-list"></div>
                                    </div>

                                    <div class="eb-panel-section eb-validation-section">
                                        <div class="eb-panel-label">Validación</div>
                                        <div id="eb-validation-list" class="eb-validation-list"></div>
                                    </div>
                                </aside>

                                {{-- Columna central: lienzo de vista previa en vivo --}}
                                <section class="eb-col eb-col-center">
                                    <div id="eb-canvas-scroll" class="eb-canvas-scroll">
                                        <div id="eb-canvas-paper" class="eb-canvas-paper">
                                            <div id="eb-canvas-blocks" class="eb-canvas-blocks"></div>
                                        </div>
                                    </div>
                                </section>

                                {{-- Columna derecha: inspector del bloque seleccionado --}}
                                <aside class="eb-col eb-col-right">
                                    <div id="eb-inspector" class="eb-inspector"></div>
                                </aside>
                            </div>
                        </div>

                        {{-- El textarea real que se envía al backend. En modo bloques queda
                        oculto (display:none por CSS) y aquí se mantiene una copia HTML
                        equivalente generada a partir de los bloques (ver
                        buildFallbackBodyHtml en el script de abajo), para no romper la
                        validación "required" del campo body ni el envío real de correos.
                        En "Modo avanzado" este mismo textarea se muestra tal cual (clase
                        eb-html-textarea) y el admin edita su HTML directamente — ver
                        setAdvancedMode() en el script de abajo. --}}
                        <textarea name="body" id="body-hidden" required>{{ old('body', $emailTemplate->body ?? '') }}</textarea>
                        <input type="hidden" name="blocks_json" id="blocks-json-field" value="">
                        <input type="hidden" name="builder_mode" id="builder-mode-field" value="{{ old('builder_mode', $emailTemplate->builder_mode ?? 'code') }}">
                    </div>

                    @include('admin-ui.partials._toggle-field', [
                        'name' => 'status',
                        'label' => 'Estado',
                        'description' => 'Una plantilla inactiva nunca se usa para armar correos, aunque sea la única de su categoría.',
                        'checked' => $isEdit ? (bool) $emailTemplate->status : true,
                    ])

                    <button type="submit" class="au-btn au-btn-primary">{{ $isEdit ? 'Guardar cambios' : 'Crear plantilla' }}</button>
                </form>
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Placeholders disponibles</div>
            </div>
            <div class="au-card-body">
                <p class="au-help-text" style="margin:0 0 12px">Se reemplazan automáticamente al momento de enviar el correo. Cópialos y pégalos donde los necesites: en el Asunto, o dentro del texto de los bloques de Encabezado, Texto, Botón y Pie de página.</p>

                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{nombre_cliente}}</code>
                        <div class="au-help-text" style="margin-top:4px">Nombre del cliente.</div>
                    </div>
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{categoria}}</code>
                        <div class="au-help-text" style="margin-top:4px">Nombre de la categoría dominante de compras del cliente.</div>
                    </div>
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{productos}}</code>
                        <div class="au-help-text" style="margin-top:4px">Bloque HTML ya armado con las tarjetas de los productos recomendados (imagen, nombre, precio y botón "Ver producto"). Usa el bloque "Productos recomendados" del editor para insertarlo.</div>
                    </div>
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{cupon_codigo}}</code>
                        <div class="au-help-text" style="margin-top:4px">Código del cupón (vacío si no hay cupón para esta categoría).</div>
                    </div>
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{cupon_descuento}}</code>
                        <div class="au-help-text" style="margin-top:4px">Texto del descuento, ej. "10%" o "$100 MXN" (vacío si no hay cupón).</div>
                    </div>
                    <div>
                        <code class="au-mono" style="background:var(--au-neutral-bg, #F5F7FA);padding:2px 6px;border-radius:4px">@{{cupon_bloque}}</code>
                        <div class="au-help-text" style="margin-top:4px">Sección completa del cupón, ya armada (código + descuento con su diseño). Usa el bloque "Cupón" del editor para insertarlo — si no hay cupón, no muestra nada.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="eb-preview-modal" class="eb-modal" style="display:none">
        <div class="eb-modal-backdrop"></div>
        <div class="eb-modal-dialog">
            <div class="eb-modal-header">
                <strong><i class="fas fa-eye"></i> Vista previa del correo</strong>
                <button type="button" id="eb-preview-close" class="au-btn au-btn-sm au-btn-icon au-btn-plain"><i class="fas fa-times"></i></button>
            </div>
            <div class="eb-modal-body">
                <div id="eb-preview-loading">Generando vista previa…</div>
                <div id="eb-preview-error" class="au-help-text" style="display:none;color:var(--au-critical, #b3261e)"></div>
                <iframe id="eb-preview-frame" title="Vista previa del correo" style="display:none"></iframe>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
        <style>
            /* ==========================================================
               Editor visual por bloques (email-templates) — paleta y
               tipografía inspiradas en el mockup "Maildeck" compartido
               por el dueño del negocio. IBM Plex Sans / Mono ya se cargan
               globalmente en admin-ui/layouts/master.blade.php, aquí solo
               se referencian. Todo queda contenido a #eb-editor y al
               modal de vista previa para no afectar el resto del panel.
               ========================================================== */
            #eb-editor,
            .eb-modal {
                --eb-bg: #EEF1F6;
                --eb-surface: #FFFFFF;
                --eb-ink: #0F2138;
                --eb-ink-strong: #16202A;
                --eb-ink-soft: #4A5A72;
                --eb-ink-muted: #64748B;
                --eb-accent: #1B3A6B;
                --eb-accent-hover: #27508F;
                --eb-accent-soft: #E9EEF7;
                --eb-line: #D5DCE7;
                --eb-line-soft: #E1E7F0;
                --eb-warning: #8a6d1f;
                --eb-warning-bg: #FFF7E6;
                --eb-warning-border: #F3D999;
                --eb-critical: #b3383c;
                --eb-critical-bg: #fdf0f0;
                --eb-success: #1a7040;
                --eb-success-bg: #eafaf0;
                --eb-font-sans: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                --eb-font-mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                font-family: var(--eb-font-sans);
                color: var(--eb-ink);
            }

            .eb-editor {
                border: 1px solid var(--eb-line);
                border-radius: 10px;
                background: var(--eb-bg);
                padding: 14px;
            }

            .eb-mono-label {
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-weight: 600;
            }

            /* ---- Barra superior ---- */
            .eb-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 12px;
            }

            .eb-topbar-left {
                display: flex;
                align-items: center;
                gap: 14px;
                flex-wrap: wrap;
            }

            .eb-topbar-title {
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-size: 12px;
                font-weight: 600;
                color: var(--eb-ink-soft);
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .eb-device-toggle {
                display: inline-flex;
                background: var(--eb-surface);
                border: 1px solid var(--eb-line);
                border-radius: 8px;
                padding: 3px;
                gap: 2px;
            }

            .eb-device-btn {
                border: none;
                background: transparent;
                color: var(--eb-ink-soft);
                font-family: var(--eb-font-sans);
                font-size: 12.5px;
                font-weight: 600;
                padding: 6px 12px;
                border-radius: 6px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: background 0.12s ease, color 0.12s ease;
            }

            .eb-device-btn:hover {
                background: var(--eb-accent-soft);
                color: var(--eb-accent);
            }

            .eb-device-btn.is-active {
                background: var(--eb-accent);
                color: #fff;
            }

            .eb-topbar-right {
                display: flex;
                align-items: center;
                gap: 14px;
                flex-wrap: wrap;
            }

            .eb-field-inline {
                flex-direction: row !important;
                align-items: center;
                gap: 8px !important;
            }

            .eb-field-inline label {
                margin: 0;
            }

            .eb-btn {
                font-family: var(--eb-font-sans);
                font-size: 12.5px;
                font-weight: 600;
                border-radius: 7px;
                border: 1px solid var(--eb-line);
                background: var(--eb-surface);
                color: var(--eb-ink);
                padding: 8px 14px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 7px;
                transition: background 0.12s ease, border-color 0.12s ease, color 0.12s ease;
            }

            .eb-btn:hover {
                border-color: var(--eb-accent);
                color: var(--eb-accent);
            }

            .eb-btn-primary {
                background: var(--eb-accent);
                border-color: var(--eb-accent);
                color: #fff;
            }

            .eb-btn-primary:hover {
                background: var(--eb-accent-hover);
                border-color: var(--eb-accent-hover);
                color: #fff;
            }

            /* ---- Modo avanzado: textarea de HTML crudo (reemplaza al lienzo) ---- */
            #body-hidden {
                display: none;
            }

            #body-hidden.eb-html-textarea {
                display: block;
                width: 100%;
                min-height: 420px;
                margin-top: 12px;
                font-family: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                font-size: 13px;
                line-height: 1.6;
                color: #16202A;
                background: #FFFFFF;
                border: 1px solid #D5DCE7;
                border-radius: 10px;
                padding: 14px;
                box-sizing: border-box;
                resize: vertical;
            }

            /* ---- Layout de 3 columnas tipo IDE ---- */
            .eb-workspace {
                display: grid;
                grid-template-columns: 248px minmax(200px, 1fr) 296px;
                border: 1px solid var(--eb-line);
                border-radius: 10px;
                overflow: hidden;
                background: var(--eb-surface);
            }

            .eb-editor {
                overflow-x: auto;
                min-width: 0;
            }

            .eb-col {
                min-height: 0;
            }

            .eb-col-left {
                border-right: 1px solid var(--eb-line-soft);
                background: var(--eb-surface);
                display: flex;
                flex-direction: column;
                max-height: 640px;
                overflow-y: auto;
            }

            .eb-col-right {
                border-left: 1px solid var(--eb-line-soft);
                background: var(--eb-surface);
                max-height: 640px;
                overflow-y: auto;
            }

            .eb-col-center {
                background: repeating-conic-gradient(#F1F3F8 0% 25%, #EAEDF3 0% 50%) 50% / 16px 16px;
                max-height: 640px;
                overflow: hidden;
                display: flex;
                min-width: 0;
            }

            .eb-panel-section {
                padding: 14px;
                border-bottom: 1px solid var(--eb-line-soft);
            }

            .eb-panel-section:last-child {
                border-bottom: none;
            }

            .eb-panel-label {
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-size: 11px;
                font-weight: 600;
                color: var(--eb-ink-muted);
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .eb-count-badge {
                font-family: var(--eb-font-mono);
                background: var(--eb-accent-soft);
                color: var(--eb-accent);
                border-radius: 999px;
                padding: 1px 7px;
                font-size: 10.5px;
                font-weight: 700;
            }

            /* ---- Cuadrícula "agregar bloque" ---- */
            .eb-block-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .eb-block-btn {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 12px 6px;
                border: 1px solid var(--eb-line);
                border-radius: 8px;
                background: var(--eb-surface);
                color: var(--eb-ink-soft);
                cursor: pointer;
                font-family: var(--eb-font-sans);
                font-size: 11px;
                font-weight: 600;
                text-align: center;
                line-height: 1.2;
                transition: border-color 0.12s ease, background 0.12s ease, color 0.12s ease, transform 0.08s ease;
            }

            .eb-block-btn i {
                font-size: 15px;
                color: var(--eb-accent);
            }

            .eb-block-btn:hover {
                border-color: var(--eb-accent);
                background: var(--eb-accent-soft);
                color: var(--eb-accent);
                transform: translateY(-1px);
            }

            /* ---- Lista de capas ---- */
            .eb-layers-section {
                flex: 1 1 auto;
            }

            .eb-layers-list {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .eb-layers-empty {
                margin: 0;
                font-size: 12px;
                color: var(--eb-ink-muted);
            }

            .eb-layer-row {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 7px 8px;
                border-radius: 7px;
                border: 1px solid transparent;
                cursor: pointer;
                transition: background 0.12s ease, border-color 0.12s ease;
            }

            .eb-layer-row:hover {
                background: var(--eb-bg);
            }

            .eb-layer-row.is-selected {
                background: var(--eb-accent-soft);
                border-color: var(--eb-accent);
            }

            .eb-layer-index {
                font-family: var(--eb-font-mono);
                font-size: 10.5px;
                color: var(--eb-ink-muted);
                width: 16px;
                flex: 0 0 auto;
            }

            .eb-layer-icon {
                width: 22px;
                height: 22px;
                border-radius: 6px;
                background: var(--eb-bg);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--eb-accent);
                font-size: 11px;
                flex: 0 0 auto;
            }

            .eb-layer-row.is-selected .eb-layer-icon {
                background: var(--eb-accent);
                color: #fff;
            }

            .eb-layer-info {
                display: flex;
                flex-direction: column;
                min-width: 0;
                flex: 1 1 auto;
            }

            .eb-layer-name {
                font-size: 12.5px;
                font-weight: 600;
                color: var(--eb-ink);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .eb-layer-snippet {
                font-size: 11px;
                color: var(--eb-ink-muted);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* ---- Validación ---- */
            .eb-validation-list {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .eb-validation-item {
                display: flex;
                align-items: flex-start;
                gap: 7px;
                font-size: 11.5px;
                line-height: 1.4;
                padding: 7px 8px;
                border-radius: 7px;
                border: 1px solid transparent;
            }

            .eb-validation-item i {
                margin-top: 1px;
                flex: 0 0 auto;
            }

            .eb-validation-warning {
                background: var(--eb-warning-bg);
                border-color: var(--eb-warning-border);
                color: var(--eb-warning);
            }

            .eb-validation-success {
                background: var(--eb-success-bg);
                color: var(--eb-success);
            }

            .eb-validation-info {
                background: var(--eb-bg);
                color: var(--eb-ink-soft);
            }

            /* ---- Lienzo central ---- */
            .eb-canvas-scroll {
                flex: 1 1 auto;
                overflow: auto;
                padding: 28px 20px;
                display: flex;
                justify-content: flex-start;
            }

            .eb-canvas-paper {
                margin: 0 auto;
            }

            .eb-canvas-paper {
                width: 680px;
                max-width: 680px;
                flex: 0 0 auto;
                background: #F4F6F8;
                border-radius: 4px;
                box-shadow: 0 4px 18px rgba(15, 33, 56, 0.12);
                min-height: 240px;
                height: fit-content;
                transition: width 0.15s ease, max-width 0.15s ease;
            }

            .eb-canvas-paper.eb-canvas-paper--mobile {
                width: 390px;
                max-width: 390px;
            }

            .eb-canvas-blocks {
                min-height: 240px;
            }

            .eb-canvas-empty {
                margin: 0;
                padding: 48px 20px;
                text-align: center;
                font-size: 12.5px;
                color: var(--eb-ink-muted);
            }

            .eb-canvas-block {
                position: relative;
                border: 2px solid transparent;
                transition: border-color 0.12s ease;
                cursor: pointer;
            }

            .eb-canvas-block:hover {
                border-color: var(--eb-line);
            }

            .eb-canvas-block.is-selected {
                border-color: var(--eb-accent);
                z-index: 1;
            }

            .eb-canvas-block-inner {
                pointer-events: none;
            }

            .eb-canvas-block-inner img {
                max-width: 100%;
            }

            .eb-canvas-toolbar {
                position: absolute;
                top: -14px;
                right: 6px;
                display: none;
                gap: 3px;
                background: var(--eb-ink-strong);
                border-radius: 7px;
                padding: 3px;
                box-shadow: 0 4px 10px rgba(15, 33, 56, 0.25);
                z-index: 2;
            }

            .eb-canvas-block:hover .eb-canvas-toolbar,
            .eb-canvas-block.is-selected .eb-canvas-toolbar {
                display: inline-flex;
            }

            .eb-canvas-tool-btn {
                border: none;
                background: transparent;
                color: #fff;
                width: 24px;
                height: 24px;
                border-radius: 5px;
                font-size: 11px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .eb-canvas-tool-btn:hover {
                background: rgba(255, 255, 255, 0.18);
            }

            .eb-canvas-tool-btn:disabled {
                opacity: 0.35;
                cursor: not-allowed;
            }

            .eb-canvas-tool-btn:disabled:hover {
                background: transparent;
            }

            .eb-canvas-tool-critical:hover {
                background: var(--eb-critical);
            }

            /* ---- Inspector (panel derecho) ---- */
            .eb-inspector {
                padding: 14px;
            }

            .eb-inspector-empty {
                text-align: center;
                padding: 32px 12px;
                color: var(--eb-ink-muted);
            }

            .eb-inspector-empty i {
                font-size: 20px;
                margin-bottom: 8px;
                display: block;
                color: var(--eb-line);
            }

            .eb-inspector-empty p {
                margin: 0;
                font-size: 12.5px;
                line-height: 1.5;
            }

            .eb-inspector-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--eb-line-soft);
            }

            .eb-inspector-icon {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: var(--eb-accent);
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                flex: 0 0 auto;
            }

            .eb-inspector-title {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--eb-ink-strong);
            }

            .eb-inspector-sub {
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.06em;
                font-size: 10px;
                color: var(--eb-ink-muted);
            }

            .eb-tabs {
                display: flex;
                gap: 4px;
                background: var(--eb-bg);
                border-radius: 8px;
                padding: 3px;
                margin-bottom: 14px;
            }

            .eb-tab-btn {
                flex: 1 1 0;
                border: none;
                background: transparent;
                color: var(--eb-ink-soft);
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.06em;
                font-size: 10.5px;
                font-weight: 700;
                padding: 7px 4px;
                border-radius: 6px;
                cursor: pointer;
                transition: background 0.12s ease, color 0.12s ease;
            }

            .eb-tab-btn:hover {
                color: var(--eb-accent);
            }

            .eb-tab-btn.is-active {
                background: var(--eb-surface);
                color: var(--eb-accent);
                box-shadow: 0 1px 2px rgba(15, 33, 56, 0.1);
            }

            .eb-tab-panel {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .eb-inspector-note {
                margin: 0;
                font-size: 12px;
                line-height: 1.5;
                color: var(--eb-ink-soft);
                background: var(--eb-bg);
                border-radius: 7px;
                padding: 10px;
            }

            .eb-logo-preview {
                background: #0B4C87;
                border-radius: 6px;
                padding: 10px;
                display: inline-flex;
                align-self: flex-start;
                margin-bottom: 10px;
            }

            /* ---- Campos reutilizables dentro del inspector ---- */
            .eb-fields-row {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: flex-end;
            }

            .eb-field {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .eb-field label {
                font-family: var(--eb-font-mono);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-size: 10.5px;
                font-weight: 600;
                color: var(--eb-ink-muted);
            }

            .eb-field-wide {
                flex: 1 1 100%;
            }

            .eb-field-wide input,
            .eb-field-wide .au-input {
                width: 100%;
                font-family: var(--eb-font-sans);
            }

            .eb-color-input {
                width: 40px;
                height: 30px;
                padding: 2px;
                border: 1px solid var(--eb-line);
                border-radius: 6px;
                background: var(--eb-surface);
                cursor: pointer;
            }

            .eb-number-input {
                width: 100%;
                font-family: var(--eb-font-sans);
            }

            .eb-tab-panel .au-input,
            .eb-tab-panel .au-select {
                font-family: var(--eb-font-sans);
                font-size: 13px;
                border: 1px solid var(--eb-line);
                border-radius: 7px;
            }

            .eb-quill-mount {
                background: #fff;
                min-height: 140px;
                border: 1px solid var(--eb-line);
                border-radius: 7px;
                overflow: hidden;
            }

            .eb-quill-mount .ql-editor {
                min-height: 100px;
                font-family: var(--eb-font-sans);
                font-size: 13.5px;
            }

            .eb-quill-mount .ql-toolbar.ql-snow {
                border: none;
                border-bottom: 1px solid var(--eb-line);
                font-family: var(--eb-font-sans);
            }

            .eb-quill-mount .ql-container.ql-snow {
                border: none;
            }

            /* ---- Responsivo: apilar columnas en pantallas angostas ---- */
            @media (max-width: 900px) {
                .eb-workspace {
                    grid-template-columns: 1fr;
                    min-width: 0;
                }

                .eb-col-left,
                .eb-col-right {
                    max-height: 320px;
                    border-right: none;
                    border-left: none;
                    border-bottom: 1px solid var(--eb-line-soft);
                }

                .eb-col-center {
                    max-height: 480px;
                }
            }

            /* ===== Modal de vista previa ===== */
            .eb-modal {
                position: fixed;
                inset: 0;
                z-index: 1050;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: var(--eb-font-sans);
            }

            .eb-modal-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 33, 56, 0.55);
            }

            .eb-modal-dialog {
                position: relative;
                width: min(720px, 92vw);
                max-height: 88vh;
                background: var(--eb-surface);
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(15, 33, 56, 0.35);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .eb-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 18px;
                border-bottom: 1px solid var(--eb-line);
                color: var(--eb-ink-strong);
            }

            .eb-modal-body {
                padding: 18px;
                overflow: auto;
            }

            #eb-preview-frame {
                width: 100%;
                height: 60vh;
                border: 1px solid var(--eb-line);
                border-radius: 8px;
                background: #fff;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <script>
            (function () {
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
                var advancedToggleLabel = document.getElementById('eb-advanced-toggle');
                var advancedModeValue = document.getElementById('eb-advanced-mode-value');
                var ebWorkspace = document.querySelector('#eb-editor .eb-workspace');

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
                var initialData = @json($emailTemplate->blocks_json ?? null);
                var hasSavedBlocksJson = !!(initialData && Array.isArray(initialData.blocks));
                var isEditTemplate = @json($isEdit);

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
                            return '<div style="text-align:' + (s.align || 'center') + ';padding:12px"><img src="{{ $ebLogoUrl }}" width="' + (s.width || 160) + '" alt="Logo"></div>';
                        case 'heading':
                            return '<h2 style="margin:0;padding:14px;color:' + (s.color || '#0B4C87') + ';background:' + (s.backgroundColor || '#FFFFFF') + ';text-align:' + (s.align || 'left') + ';font-size:' + (s.fontSize || 22) + 'px">' + (block.content || '') + '</h2>';
                        case 'text':
                            return '<div style="padding:14px;color:' + (s.color || '#333333') + ';background:' + (s.backgroundColor || '#FFFFFF') + '">' + (block.content || '') + '</div>';
                        case 'products':
                            return '<div style="padding:14px;background:' + (s.backgroundColor || '#F5F7F9') + '">@{{productos}}</div>';
                        case 'coupon':
                            return '<div style="padding:14px;background:' + (s.backgroundColor || '#FFF7E6') + '">@{{cupon_bloque}}</div>';
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
                                content: '<div class="eb-logo-preview"><img src="{{ $ebLogoUrl }}" alt="Logo" style="max-width:160px;display:block"></div>' +
                                    '<p class="eb-inspector-note">Este bloque siempre usa el logo del sistema — ajusta su alineación y ancho en la pestaña Estilo.</p>',
                                style: '<div class="eb-fields-row">' + alignField('settings.align', s.align) + numberField('Ancho (px)', 'settings.width', s.width, 40, 400) + '</div>'
                            };
                        case 'heading':
                            return {
                                content: textField('Contenido', 'content', block.content, 'Ej. Ofertas para ti @{{nombre_cliente}}'),
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
                document.querySelectorAll('.eb-device-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        document.querySelectorAll('.eb-device-btn').forEach(function (b) { b.classList.remove('is-active'); });
                        btn.classList.add('is-active');
                        canvasPaper.classList.toggle('eb-canvas-paper--mobile', btn.getAttribute('data-device') === 'mobile');
                    });
                });

                // ---- Toggle "Modo avanzado" ----
                // Refleja el estado visual del switch (usado también para activarlo
                // programáticamente al cargar, sin simular un click real).
                function applyAdvancedModeUI(isOn) {
                    advancedToggleLabel.classList.toggle('is-on', isOn);
                    var checkbox = advancedToggleLabel.querySelector('.au-toggle-input');
                    if (checkbox) checkbox.checked = isOn;
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
                        ebWorkspace.style.display = 'none';
                        bodyHiddenField.classList.add('eb-html-textarea');
                    } else {
                        ebWorkspace.style.display = '';
                        bodyHiddenField.classList.remove('eb-html-textarea');
                    }
                    // syncJson() (dentro de renderAll) manda blocks_json vacío y
                    // recalcula builder_mode a 'code' cuando no hay bloques.
                    renderAll();
                }

                advancedModeValue.addEventListener('change', function () {
                    setAdvancedMode(advancedModeValue.value === '1');
                });

                // ---- Fallback HTML simple para el campo "body" heredado ----
                // Mientras el backend no consuma blocks_json de forma exclusiva, esto
                // mantiene el campo "body" (todavía required en el backend) con un HTML
                // equivalente a los bloques armados, reutilizando los mismos placeholders
                // @{{productos}} / @{{cupon_bloque}} que ya sabe reemplazar el renderer
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

                // ---- Vista previa (POST /admin/email-templates/preview-blocks) ----
                var previewBtn = document.getElementById('eb-preview-btn');
                var previewModal = document.getElementById('eb-preview-modal');
                var previewFrame = document.getElementById('eb-preview-frame');
                var previewLoading = document.getElementById('eb-preview-loading');
                var previewError = document.getElementById('eb-preview-error');
                var previewCloseBtn = document.getElementById('eb-preview-close');

                function closePreview() {
                    previewModal.style.display = 'none';
                }

                previewBtn.addEventListener('click', function () {
                    syncJson();

                    previewModal.style.display = 'flex';
                    previewFrame.style.display = 'none';
                    previewError.style.display = 'none';
                    previewLoading.style.display = 'block';
                    previewLoading.textContent = 'Generando vista previa…';

                    // En modo avanzado no hay bloques (state.blocks queda vacío al
                    // activarlo), así que blocks_json no tiene nada que armar — hay que
                    // mandar el HTML crudo del textarea directo, o la vista previa sale
                    // en blanco aunque el HTML escrito/pegado sea válido.
                    var isAdvanced = advancedModeValue.value === '1';
                    var payload = isAdvanced
                        ? { html: bodyHiddenField.value }
                        : { blocks_json: jsonField.value };

                    fetch('{{ url('admin/email-templates/preview-blocks') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(function (res) {
                            if (!res.ok) {
                                throw new Error('HTTP ' + res.status);
                            }
                            return res.json();
                        })
                        .then(function (data) {
                            previewLoading.style.display = 'none';
                            if (!data || typeof data.html !== 'string') {
                                throw new Error('Respuesta inválida del servidor');
                            }
                            previewFrame.srcdoc = data.html;
                            previewFrame.style.display = 'block';
                        })
                        .catch(function (err) {
                            previewLoading.style.display = 'none';
                            previewError.style.display = 'block';
                            previewError.textContent = 'No se pudo generar la vista previa' + (err && err.message ? ' (' + err.message + ')' : '') + '. Es posible que esta función todavía no esté disponible — tus bloques no se pierden, solo no se pudo generar la vista previa.';
                        });
                });

                previewCloseBtn.addEventListener('click', closePreview);
                previewModal.querySelector('.eb-modal-backdrop').addEventListener('click', closePreview);

                // ---- Render inicial ----
                // Plantilla existente sin bloques guardados (el caso que antes solo
                // mostraba un aviso pasivo): se activa el modo avanzado de una vez, con
                // su "body" actual ya cargado en el textarea, en vez de mostrar un
                // editor de bloques vacío. Una plantilla nueva arranca en modo bloques
                // (comportamiento actual, sin cambios).
                if (isEditTemplate && !hasSavedBlocksJson) {
                    applyAdvancedModeUI(true);
                    setAdvancedMode(true);
                } else {
                    state.selectedId = state.blocks.length ? state.blocks[0].id : null;
                    renderAll();
                }
            })();
        </script>
    @endpush
@endsection
