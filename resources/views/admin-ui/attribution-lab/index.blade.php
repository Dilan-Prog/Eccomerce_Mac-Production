@extends('admin-ui.layouts.master')

@section('title', 'Laboratorio de atribución')

@push('styles')
    <style>
        /* Laboratorio de atribución: estilos propios de esta página (los CSS globales de admin-ui no se tocan). */
        .admin-ui-v2 .lab-section { margin-bottom: var(--au-space-5); }
        .admin-ui-v2 .lab-intro { color: var(--au-text-subdued); font-size: var(--au-fs-sm); margin: 0 0 var(--au-space-4); }
        .admin-ui-v2 .lab-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 0 var(--au-space-4); }
        .admin-ui-v2 .lab-actions { display: flex; flex-wrap: wrap; align-items: center; gap: var(--au-space-2); }
        .admin-ui-v2 .lab-inline { display: flex; gap: var(--au-space-2); }
        .admin-ui-v2 .lab-inline .au-input { flex: 1 1 auto; min-width: 0; }
        .admin-ui-v2 .lab-inline .au-btn { flex: none; }
        .admin-ui-v2 .lab-actions + .lab-actions { margin-top: var(--au-space-3); }
        .admin-ui-v2 .lab-subtitle { font-size: var(--au-fs-lg); font-weight: 700; margin: var(--au-space-5) 0 var(--au-space-2); }
        .admin-ui-v2 .lab-error { color: var(--au-critical); }
        .admin-ui-v2 .lab-hidden { display: none; }

        /* Tarjetas de estado */
        .admin-ui-v2 .lab-stat-body { min-width: 0; overflow-wrap: anywhere; }
        .admin-ui-v2 .lab-stat-line { font-size: 11.5px; color: var(--au-text-subdued); margin-top: 2px; }
        .admin-ui-v2 .lab-warnings { display: flex; flex-direction: column; gap: var(--au-space-2); margin-top: var(--au-space-4); }
        .admin-ui-v2 .lab-warning { display: flex; align-items: flex-start; gap: var(--au-space-3); font-size: var(--au-fs-sm); }
        .admin-ui-v2 .lab-warning .au-badge { flex: none; }

        /* Verificación del sitio */
        .admin-ui-v2 .lab-checks { display: flex; flex-direction: column; gap: var(--au-space-2); margin-top: var(--au-space-3); }
        .admin-ui-v2 .lab-check { display: flex; align-items: center; gap: var(--au-space-2); font-size: var(--au-fs-sm); }
        .admin-ui-v2 .lab-check-mark { font-weight: 700; }
        .admin-ui-v2 .lab-check-mark.is-ok { color: var(--au-success); }
        .admin-ui-v2 .lab-check-mark.is-bad { color: var(--au-critical); }

        /* Avisos */
        .admin-ui-v2 .lab-note,
        .admin-ui-v2 .lab-alert {
            border: 1px solid var(--au-info-border);
            background: var(--au-info-bg);
            color: var(--au-info);
            border-radius: var(--au-radius-md);
            padding: var(--au-space-3) var(--au-space-4);
            font-size: var(--au-fs-sm);
            margin-top: var(--au-space-3);
        }
        .admin-ui-v2 .lab-note-warning,
        .admin-ui-v2 .lab-alert-warning { border-color: var(--au-warning-border); background: var(--au-warning-bg); color: var(--au-warning); }
        .admin-ui-v2 .lab-alert-critical { border-color: var(--au-critical-border); background: var(--au-critical-bg); color: var(--au-critical); }
        .admin-ui-v2 .lab-alert-success { border-color: var(--au-success-border); background: var(--au-success-bg); color: var(--au-success); }
        .admin-ui-v2 .lab-errors { margin-top: var(--au-space-3); padding: var(--au-space-3) var(--au-space-4); border: 1px solid var(--au-critical-border); background: var(--au-critical-bg); color: var(--au-critical); border-radius: var(--au-radius-md); font-size: var(--au-fs-sm); }
        .admin-ui-v2 .lab-errors li { list-style: disc; margin-left: var(--au-space-4); }

        /* Generador */
        .admin-ui-v2 .lab-product-wrap { margin-bottom: var(--au-space-4); }
        .admin-ui-v2 .lab-product-selected { margin-top: var(--au-space-2); display: flex; align-items: center; flex-wrap: wrap; gap: var(--au-space-1); font-size: var(--au-fs-sm); }
        .admin-ui-v2 .lab-product-name { overflow-wrap: anywhere; }

        /* Eventos */
        .admin-ui-v2 table.au-table tbody tr.lab-row { cursor: pointer; }
        .admin-ui-v2 table.au-table tbody tr.lab-row:hover { background: var(--au-surface-subdued); }
        .admin-ui-v2 table.au-table tbody tr.lab-row.is-selected { background: var(--au-blue-050); }
        .admin-ui-v2 table.au-table tbody td { padding: 10px 14px; border-bottom: 1px solid var(--au-border-row); white-space: nowrap; }
        .admin-ui-v2 .lab-empty { padding: var(--au-space-5); text-align: center; color: var(--au-text-subdued); font-size: var(--au-fs-sm); }

        /* Visor JSON */
        .admin-ui-v2 .au-json {
            margin: var(--au-space-3) 0 0;
            padding: var(--au-space-3) var(--au-space-4);
            max-height: 360px;
            overflow: auto;
            background: var(--au-surface-subdued);
            color: var(--au-text);
            border: 1px solid var(--au-border);
            border-radius: var(--au-radius-md);
            font-family: var(--au-font-mono);
            font-size: 12px;
            line-height: 1.55;
            white-space: pre;
            tab-size: 2;
        }

        /* Probador y simulador */
        .admin-ui-v2 .lab-tabs { padding: 0; margin-bottom: var(--au-space-4); }
        .admin-ui-v2 button.au-tab { background: none; border: 0; border-bottom: 2px solid transparent; font-family: inherit; }
        .admin-ui-v2 button.au-tab.is-active { border-bottom-color: var(--au-blue-700); }
        .admin-ui-v2 .lab-result-head { display: flex; flex-wrap: wrap; align-items: center; gap: var(--au-space-3); margin-top: var(--au-space-4); }
        .admin-ui-v2 .lab-result-meta { font-size: var(--au-fs-sm); color: var(--au-text-subdued); }
        .admin-ui-v2 .lab-sim-line { display: flex; flex-wrap: wrap; align-items: baseline; gap: var(--au-space-2); font-size: var(--au-fs-sm); margin-top: var(--au-space-2); }
        .admin-ui-v2 .lab-sim-label { font-weight: 600; }
        .admin-ui-v2 .lab-sim-value { overflow-wrap: anywhere; }
        .admin-ui-v2 .lab-sim-none { color: var(--au-text-subdued); }
        .admin-ui-v2 .lab-sim-subtitle { font-weight: 700; margin-top: var(--au-space-4); }
        .admin-ui-v2 .lab-n8n-sent { font-size: var(--au-fs-sm); margin-top: var(--au-space-3); display: flex; flex-direction: column; gap: var(--au-space-1); }
        .admin-ui-v2 .lab-n8n-sent code { overflow-wrap: anywhere; white-space: pre-wrap; }
        .admin-ui-v2 .au-input.lab-input-invalid { border-color: var(--au-critical); }
        .admin-ui-v2 .lab-fixed-tz { height: 38px; display: flex; align-items: center; font-size: var(--au-fs-sm); color: var(--au-text-subdued); }

        @media (max-width: 640px) {
            .admin-ui-v2 .lab-actions .au-btn { flex: 1 1 100%; }
        }
    </style>
@endpush

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Laboratorio de atribución',
        'subtitle' => 'Prueba el flujo completo (visita con gclid, clic a WhatsApp, folio, consulta de n8n) sin tocar a visitantes reales ni contaminar métricas ni Google Ads.',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Laboratorio de atribución'],
        ],
    ])

    {{-- 1. Estado --}}
    <section class="au-card lab-section" id="lab-sec-status" aria-labelledby="lab-h-status">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-status">1. Estado</h2>
            <button type="button" class="au-btn au-btn-sm" id="lab-status-refresh"><i class="fas fa-sync-alt"></i> Actualizar</button>
        </div>
        <div class="au-card-body">
            <div id="lab-status-error" class="au-help-text lab-error lab-hidden"></div>
            <div class="au-stat-grid" id="lab-status-grid"></div>
            <ul class="lab-warnings" id="lab-warnings" aria-live="polite"></ul>
        </div>
    </section>

    {{-- 2. Modo prueba --}}
    <section class="au-card lab-section" id="lab-sec-mode" aria-labelledby="lab-h-mode">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-mode">2. Modo prueba</h2>
            <div id="lab-test-pill"></div>
        </div>
        <div class="au-card-body">
            <p class="lab-intro">
                Con el modo prueba activo, <strong>este navegador</strong> ve el sitio sin etiquetas de Google ni de Meta, marca sus eventos como de
                prueba y el botón de WhatsApp no usa la línea real. Los demás visitantes no notan nada. Se apaga solo al vencer la cookie.
            </p>
            <div class="lab-actions">
                <button type="button" class="au-btn au-btn-primary" id="lab-btn-activate"><i class="fas fa-flask"></i> Activar modo prueba</button>
                <button type="button" class="au-btn" id="lab-btn-deactivate"><i class="fas fa-power-off"></i> Desactivar modo prueba</button>
                <button type="button" class="au-btn" id="lab-btn-verify"><i class="fas fa-shield-alt"></i> Verificar sitio público</button>
            </div>
            <div id="lab-verify-result" aria-live="polite"></div>

            {{-- Ajustes de prueba: se guardan en la base de datos (no en el .env) --}}
            <h3 class="lab-subtitle" id="lab-h-settings">Ajustes de prueba</h3>
            <p class="lab-intro">
                Se guardan en el panel (base de datos), no en el <code class="au-mono">.env</code>. Solo se usan en modo prueba y en este laboratorio.
            </p>
            <div class="lab-form-grid lab-settings-grid">
                <div class="au-field">
                    <label class="au-label" for="lab-set-whatsapp">Número de WhatsApp de prueba (cuenta de prueba de Kommo)</label>
                    <input type="tel" class="au-input au-mono" id="lab-set-whatsapp" inputmode="tel" autocomplete="off" spellcheck="false" placeholder="5215512345678" aria-describedby="lab-set-whatsapp-help lab-set-whatsapp-source lab-set-whatsapp-error">
                    <div class="au-help-text" id="lab-set-whatsapp-help">Solo dígitos con lada de país, ej. 5215512345678. Es el número al que abren los botones de WhatsApp en modo prueba. Vacío = abre WhatsApp sin número y tú eliges el chat.</div>
                    <div class="au-help-text" id="lab-set-whatsapp-source" aria-live="polite"></div>
                    <div class="au-help-text lab-error lab-hidden" id="lab-set-whatsapp-error" role="alert"></div>
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-set-n8n">URL del webhook de n8n (para pruebas)</label>
                    <input type="url" class="au-input au-mono" id="lab-set-n8n" inputmode="url" autocomplete="off" spellcheck="false" placeholder="https://…" aria-describedby="lab-set-n8n-help lab-set-n8n-error">
                    <div class="au-help-text" id="lab-set-n8n-help">Webhook de n8n al que el botón de prueba manda un mensaje simulado con el folio de tu último evento de prueba. Debe ser https y pública. No se usa en el sitio ni con visitantes reales.</div>
                    <div class="au-help-text lab-error lab-hidden" id="lab-set-n8n-error" role="alert"></div>
                </div>
            </div>
            <div class="lab-actions">
                <button type="button" class="au-btn au-btn-primary" id="lab-settings-save"><i class="fas fa-save"></i> Guardar ajustes</button>
                <button type="button" class="au-btn" id="lab-n8n-test" disabled><i class="fas fa-paper-plane"></i> Enviar mensaje de prueba a n8n</button>
            </div>
            <div class="au-help-text lab-hidden" id="lab-n8n-test-hint" style="margin-top:var(--au-space-2)">Guarda primero una URL de n8n para poder enviar el mensaje de prueba.</div>
            <div id="lab-n8n-result" aria-live="polite"></div>
        </div>
    </section>

    {{-- 3. Generador de visita simulada --}}
    <section class="au-card lab-section" id="lab-sec-visit" aria-labelledby="lab-h-visit">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-visit">3. Generador de visita simulada</h2>
        </div>
        <div class="au-card-body">
            <p class="lab-intro">Arma la URL de un anuncio de Google con un gclid de prueba y ábrela en una pestaña nueva de este navegador.</p>

            <div id="lab-gen-warning" class="lab-note lab-note-warning lab-hidden" role="alert" style="margin-top:0;margin-bottom:var(--au-space-4)">
                El modo prueba no está activo en este navegador. Actívalo en la sección 2: sin él, la visita simulada se registraría como real y dispararía GA4 y Ads.
            </div>

            <div class="lab-form-grid">
                <div class="au-field">
                    <label class="au-label" for="lab-dest">Destino</label>
                    <select class="au-select" id="lab-dest">
                        <option value="home">Inicio</option>
                        <option value="product">Producto</option>
                        <option value="categorias">Categorías (/categorias)</option>
                    </select>
                </div>
            </div>

            <div class="lab-product-wrap" id="lab-product-wrap">
                <label class="au-label" for="lab-product-q">Producto</label>
                <input type="search" class="au-input" id="lab-product-q" placeholder="Busca por nombre o SKU (mínimo 2 caracteres)" autocomplete="off">
                <div class="au-sku-search-results" id="lab-product-results"></div>
                <div class="lab-product-selected" id="lab-product-selected"></div>
            </div>

            <div class="lab-form-grid">
                <div class="au-field" style="grid-column: 1 / -1">
                    <label class="au-label" for="lab-gclid">gclid</label>
                    <div class="lab-inline">
                        <input type="text" class="au-input au-mono" id="lab-gclid" autocomplete="off" spellcheck="false">
                        <button type="button" class="au-btn" id="lab-gclid-regen" title="Generar otro gclid de prueba"><i class="fas fa-redo"></i><span>Regenerar</span></button>
                    </div>
                    <div class="au-help-text">Mínimo 20 caracteres; solo letras, números, guion y guion bajo. Por defecto empieza con TEST_GCLID_.</div>
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-gbraid">gbraid (opcional)</label>
                    <input type="text" class="au-input au-mono" id="lab-gbraid" autocomplete="off" spellcheck="false">
                    <div class="au-help-text">Si lo llenas: mínimo 10 caracteres.</div>
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-wbraid">wbraid (opcional)</label>
                    <input type="text" class="au-input au-mono" id="lab-wbraid" autocomplete="off" spellcheck="false">
                    <div class="au-help-text">Si lo llenas: mínimo 10 caracteres.</div>
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-utm-source">utm_source</label>
                    <input type="text" class="au-input" id="lab-utm-source" value="google" autocomplete="off">
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-utm-medium">utm_medium</label>
                    <input type="text" class="au-input" id="lab-utm-medium" value="cpc" autocomplete="off">
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-utm-campaign">utm_campaign</label>
                    <input type="text" class="au-input" id="lab-utm-campaign" value="prueba_lab" autocomplete="off">
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-utm-term">utm_term</label>
                    <input type="text" class="au-input" id="lab-utm-term" autocomplete="off">
                </div>
                <div class="au-field">
                    <label class="au-label" for="lab-utm-content">utm_content</label>
                    <input type="text" class="au-input" id="lab-utm-content" autocomplete="off">
                </div>
            </div>

            <ul class="lab-errors lab-hidden" id="lab-gen-errors" role="alert"></ul>

            <div class="lab-actions" style="margin-top:var(--au-space-4)">
                <button type="button" class="au-btn au-btn-primary" id="lab-open"><i class="fas fa-external-link-alt"></i> Abrir en pestaña nueva</button>
            </div>
            <div class="lab-note lab-note-warning" role="note">
                Ábrela solo en este navegador con el modo prueba activo; en otro navegador sí dispararía GA4/Ads.
            </div>
        </div>
    </section>

    {{-- 4. Eventos de prueba recientes --}}
    <section class="au-card lab-section" id="lab-sec-events" aria-labelledby="lab-h-events">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-events">4. Eventos de prueba recientes</h2>
            <div class="lab-actions">
                <label class="au-flex au-gap-2 au-help-text" style="margin:0;cursor:pointer">
                    <input type="checkbox" class="au-checkbox" id="lab-events-auto" checked> Actualizar solo (cada 7 s)
                </label>
                <button type="button" class="au-btn au-btn-sm" id="lab-events-refresh"><i class="fas fa-sync-alt"></i> Actualizar ahora</button>
            </div>
        </div>
        <div class="au-card-body au-card-body-flush">
            <div id="lab-events-error" class="au-help-text lab-error lab-hidden" style="padding:var(--au-space-3) var(--au-space-5)"></div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>Hora (MX)</th>
                            <th>Tipo</th>
                            <th>Placement</th>
                            <th>Folio</th>
                            <th>gclid</th>
                            <th>Campaña</th>
                            <th>Producto</th>
                            <th>Sesión</th>
                            <th>Fuente de atribución</th>
                        </tr>
                    </thead>
                    <tbody id="lab-events-body"></tbody>
                </table>
            </div>
            <div class="lab-empty" id="lab-events-empty">Aún no hay eventos de prueba. Abre una visita simulada y haz clic en un botón de contacto.</div>
            <div class="au-help-text" style="padding:var(--au-space-3) var(--au-space-5)">Clic en una fila para ver el JSON que recibiría n8n. Se muestran los últimos 50.</div>
        </div>
    </section>

    <section class="au-card lab-section lab-hidden" id="lab-event-detail" aria-labelledby="lab-event-detail-title">
        <div class="au-card-header">
            <h3 class="au-card-title" id="lab-event-detail-title">Detalle del evento</h3>
            <button type="button" class="au-btn au-btn-sm" id="lab-event-close"><i class="fas fa-times"></i> Cerrar</button>
        </div>
        <div class="au-card-body">
            <pre class="au-json" id="lab-event-json"></pre>
        </div>
    </section>

    {{-- 5. Probador estilo n8n --}}
    <section class="au-card lab-section" id="lab-sec-lookup" aria-labelledby="lab-h-lookup">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-lookup">5. Probador estilo n8n</h2>
        </div>
        <div class="au-card-body">
            <p class="lab-intro">Consulta lo mismo que hará n8n: por folio o por ventana de tiempo (cruce de llamadas).</p>

            <div class="au-tabs lab-tabs" role="tablist">
                <button type="button" class="au-tab is-active" role="tab" data-lab-mode="folio">Por folio</button>
                <button type="button" class="au-tab" role="tab" data-lab-mode="window">Por ventana de tiempo</button>
            </div>

            <div id="lab-q-pane-folio">
                <div class="lab-form-grid">
                    <div class="au-field">
                        <label class="au-label" for="lab-q-folio">Folio</label>
                        <input type="text" class="au-input au-mono" id="lab-q-folio" placeholder="MN7K4Q2 o COT-…" autocomplete="off" spellcheck="false">
                    </div>
                    <div class="au-field">
                        <label class="au-label" for="lab-q-message-at">Hora del mensaje (opcional, hora de México)</label>
                        <input type="datetime-local" class="au-input" id="lab-q-message-at">
                    </div>
                </div>
            </div>

            <div id="lab-q-pane-window" class="lab-hidden">
                <div class="lab-form-grid">
                    <div class="au-field">
                        <label class="au-label" for="lab-q-type">Tipo de evento</label>
                        <select class="au-select" id="lab-q-type">
                            <option value="phone_click">phone_click (llamada)</option>
                            <option value="whatsapp_click">whatsapp_click</option>
                            <option value="email_click">email_click</option>
                            <option value="quote_submit">quote_submit</option>
                        </select>
                    </div>
                    <div class="au-field">
                        <label class="au-label" for="lab-q-from">Desde</label>
                        <input type="datetime-local" class="au-input" id="lab-q-from">
                    </div>
                    <div class="au-field">
                        <label class="au-label" for="lab-q-to">Hasta</label>
                        <input type="datetime-local" class="au-input" id="lab-q-to">
                    </div>
                    <div class="au-field">
                        <span class="au-label">Zona horaria</span>
                        <div class="lab-fixed-tz au-mono">America/Mexico_City</div>
                    </div>
                </div>
            </div>

            <div class="lab-form-grid">
                <div class="au-field">
                    <label class="au-label" for="lab-q-scope">Alcance</label>
                    <select class="au-select" id="lab-q-scope">
                        <option value="test">Solo eventos de prueba</option>
                        @if (!empty($lab['can_real']))
                            <option value="real">Eventos reales</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="lab-actions">
                <button type="button" class="au-btn au-btn-primary" id="lab-q-run"><i class="fas fa-search"></i> Consultar</button>
                <button type="button" class="au-btn" id="lab-q-curl"><i class="fas fa-terminal"></i> Copiar como curl</button>
            </div>
            <div class="lab-note" role="note">
                Este probador llama la lógica en el servidor: no ejercita el token ni el límite de peticiones; para eso usa el curl.
                El curl lleva el literal <code class="au-mono">$TOKEN</code>: define esa variable en tu terminal con el token real.
            </div>

            <div id="lab-q-result" aria-live="polite"></div>
        </div>
    </section>

    {{-- 6. Simulador de mensaje entrante --}}
    <section class="au-card lab-section" id="lab-sec-sim" aria-labelledby="lab-h-sim">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-sim">6. Simulador de mensaje entrante</h2>
        </div>
        <div class="au-card-body">
            <p class="lab-intro">Pega el texto tal como lo recibe Kommo y revisa si n8n extraería el folio con su regex actual.</p>
            <div class="au-field">
                <label class="au-label" for="lab-sim-text">Mensaje del cliente</label>
                <textarea class="au-textarea" id="lab-sim-text" rows="4" placeholder="Hola, me interesa: DC1010. Folio: MN7K4Q2" spellcheck="false"></textarea>
            </div>
            <div class="lab-actions">
                <button type="button" class="au-btn au-btn-primary" id="lab-sim-run"><i class="fas fa-vial"></i> Analizar mensaje</button>
            </div>
            <div id="lab-sim-out" aria-live="polite"></div>
        </div>
    </section>

    {{-- 7. Zona de limpieza --}}
    <section class="au-card lab-section" id="lab-sec-clean" aria-labelledby="lab-h-clean">
        <div class="au-card-header">
            <h2 class="au-card-title" id="lab-h-clean">7. Zona de limpieza</h2>
        </div>
        <div class="au-card-body">
            <p class="lab-intro">Elimina todas las filas marcadas como prueba. Los eventos reales no se tocan.</p>
            <div class="lab-actions">
                <button type="button" class="au-btn au-btn-critical" id="lab-destroy"><i class="fas fa-trash-alt"></i> Borrar mis eventos de prueba</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.AU_LAB = @json($lab, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    </script>
    <script src="{{ asset('admin-ui/js/attribution-lab/lab.js') }}?v={{ filemtime(public_path('admin-ui/js/attribution-lab/lab.js')) }}"></script>
@endpush
