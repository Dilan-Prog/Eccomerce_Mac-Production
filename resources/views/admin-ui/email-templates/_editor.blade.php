{{--
    Editor de plantillas de correo — layout de 2 columnas (editor | vista
    previa en vivo) con barra superior de acciones.

    Este parcial es SOLO el formulario: no trae layout, ni <style>, ni
    <script>. Eso es a propósito, porque se usa de dos maneras:
      - dentro de la página completa admin/email-templates/create|edit
        (resources/views/admin-ui/email-templates/form.blade.php), y
      - inyectado por AJAX en la pantalla de pestañas de Email Marketing
        (resources/views/admin-ui/email-marketing/index.blade.php).
    Los estilos viven en admin-ui/css/components/email-builder.css y la
    lógica en admin-ui/js/email-builder/email-builder.js (AU.EmailBuilder);
    cada anfitrión los carga una sola vez y llama a AU.EmailBuilder.mount().

    Quién manda el submit también lo decide el anfitrión: el botón "Guardar
    plantilla" es siempre type="submit" sobre este mismo <form>. La página
    completa lo deja enviarse normal (redirect de toda la vida); la pantalla
    de pestañas intercepta el submit y lo manda por fetch. "Cancelar" no
    hace nada por sí solo: lleva data-eb-cancel para que cada anfitrión lo
    conecte a lo que corresponda (volver al listado o cerrar el panel).
--}}
@php
    $isEdit = isset($emailTemplate) && $emailTemplate;
    $ebLogoUrl = asset('uploads/logo/2k-blanco-azul.png');
    $ebType = old('type', $emailTemplate->type ?? 'individual');
    $ebTypes = [
        'individual' => 'Individual',
        'campaign' => 'Campaña',
        'sequence' => 'Secuencia',
    ];

    // Tokens ofrecidos como chips en el editor de HTML crudo. A propósito
    // NO se ofrecen {{deal.*}} ni {{unsubscribe_url}}: este proyecto no
    // tiene concepto de "deal", y el enlace de baja depende del token de
    // rastreo por envío que genera la plataforma de correo, no la
    // plantilla — ofrecerlos insertaría marcadores que en el correo real
    // saldrían en blanco. Ver el encabezado de App\Support\EmailTemplateRenderer.
    $ebTokens = [
        '{{contact.name}}' => 'Nombre del destinatario',
        '{{contact.email}}' => 'Correo del destinatario',
        '{{contact.company}}' => 'Empresa del destinatario',
        '{{quote.quote_number}}' => 'Folio de la cotización',
        '{{quote.total}}' => 'Total de la cotización',
        '{{quote.currency}}' => 'Moneda de la cotización',
        '{{quote.valid_until}}' => 'Vigencia de la cotización',
        '{{nombre_cliente}}' => 'Nombre del cliente',
        '{{categoria}}' => 'Categoría dominante del cliente',
        '{{productos}}' => 'Tarjetas de productos recomendados',
        '{{cupon_codigo}}' => 'Código del cupón',
        '{{cupon_descuento}}' => 'Descuento del cupón',
        '{{cupon_bloque}}' => 'Sección completa del cupón',
        '{{cart.total}}' => 'Total del carrito',
        '{{cart.items_table}}' => 'Tabla de líneas del carrito',
    ];
@endphp

<form id="eb-form"
      method="POST"
      action="{{ $isEdit ? route('admin.email-templates.update', $emailTemplate->id) : route('admin.email-templates.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div id="eb-editor" class="eb-editor eb-editor--v2">
        {{-- Barra superior: identidad de la plantilla + modo + acciones --}}
        <div class="eb-bar">
            <input type="text" class="au-input eb-bar-name" name="name" required
                   value="{{ old('name', $emailTemplate->name ?? '') }}"
                   placeholder="Nombre interno de la plantilla">

            <input type="text" class="au-input eb-bar-subject" name="subject" required
                   value="{{ old('subject', $emailTemplate->subject ?? '') }}"
                   placeholder="Asunto del correo">

            <select class="au-select eb-bar-type" name="type" title="Tipo de plantilla">
                @foreach ($ebTypes as $typeKey => $typeLabel)
                    <option value="{{ $typeKey }}" {{ $ebType === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
                @endforeach
            </select>

            <div class="eb-bar-spacer"></div>

            <div class="eb-mode-seg" role="group" aria-label="Modo del editor">
                <button type="button" class="eb-mode-btn is-active" data-mode="blocks">Vista sencilla</button>
                <button type="button" class="eb-mode-btn" data-mode="code">Vista avanzada</button>
            </div>

            <button type="button" class="au-btn" data-eb-cancel>Cancelar</button>
            <button type="submit" class="au-btn au-btn-primary">Guardar plantilla</button>
        </div>

        {{-- Segunda fila: ajustes que no son parte del contenido del correo --}}
        <div class="eb-subbar">
            <div class="eb-subbar-item">
                <label for="eb-category">Categoría</label>
                <select class="au-select" name="category_id" id="eb-category">
                    <option value="" {{ !old('category_id', $emailTemplate->category_id ?? null) ? 'selected' : '' }}>General / todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (int) old('category_id', $emailTemplate->category_id ?? 0) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="eb-subbar-item">
                <label for="eb-theme-bg">Fondo del correo</label>
                <input type="color" id="eb-theme-bg" class="eb-color-input" value="#F4F6F8">
            </div>

            <div class="eb-subbar-item">
                @include('admin-ui.partials._toggle-field', [
                    'name' => 'status',
                    'label' => 'Activa',
                    'description' => '',
                    'checked' => $isEdit ? (bool) $emailTemplate->status : true,
                ])
            </div>
        </div>

        <div class="eb-split">
            {{-- ============ Columna izquierda: el editor ============ --}}
            <div class="eb-split-left">
                {{-- Cara "Vista sencilla": paleta de bloques, capas, validación e inspector --}}
                <div class="eb-side-blocks">
                    <div class="eb-pane-head">
                        <span><i class="fas fa-layer-group"></i> Bloques del correo</span>
                    </div>
                    <div class="eb-side-scroll">
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

                        <div id="eb-inspector" class="eb-inspector"></div>
                    </div>
                </div>

                {{-- Cara "Vista avanzada": HTML crudo con chips de token --}}
                <div class="eb-side-html">
                    <div class="eb-pane-head">
                        <span><i class="fas fa-code"></i> Cuerpo HTML</span>
                        <span id="eb-html-count" class="au-mono"></span>
                    </div>

                    <div class="eb-token-bar">
                        <div class="eb-token-label">Insertar token dinámico</div>
                        <div class="eb-token-chips">
                            @foreach ($ebTokens as $token => $tokenHelp)
                                <button type="button" class="eb-token-chip" data-token="{{ $token }}" title="{{ $tokenHelp }}">{{ $token }}</button>
                            @endforeach
                        </div>
                        <div class="eb-token-help">
                            Se insertan donde tengas el cursor. Al enviar, cada token se reemplaza con el dato del destinatario; si ese dato no aplica al tipo de correo, el token se queda tal cual — nunca truena el envío.
                        </div>
                    </div>

                    <div class="eb-html-host">
                        {{-- El textarea real que se envía al backend. En vista sencilla
                        queda oculto (display:none por CSS) y aquí se mantiene una copia
                        HTML equivalente generada a partir de los bloques (ver
                        buildFallbackBodyHtml en email-builder.js), para no romper la
                        validación "required" del campo body ni el envío real de correos.
                        En vista avanzada este mismo textarea se muestra tal cual (clase
                        eb-html-textarea) y el admin edita su HTML directamente. --}}
                        <textarea name="body" id="body-hidden" required spellcheck="false">{{ old('body', $emailTemplate->body ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ============ Columna derecha: vista previa ============ --}}
            <div class="eb-split-right">
                <div class="eb-pane-head">
                    <span><i class="fas fa-eye"></i> Vista previa en vivo</span>
                    <div class="eb-device-toggle">
                        <button type="button" class="eb-device-btn is-active" data-device="desktop"><i class="fas fa-desktop"></i> Escritorio</button>
                        <button type="button" class="eb-device-btn" data-device="mobile"><i class="fas fa-mobile-screen"></i> Móvil</button>
                    </div>
                </div>

                {{-- Cara "Vista sencilla": el lienzo, que además de mostrar el
                correo es donde se selecciona cada bloque para editarlo. --}}
                <div class="eb-canvas-host">
                    <div id="eb-canvas-scroll" class="eb-canvas-scroll">
                        <div id="eb-canvas-paper" class="eb-canvas-paper">
                            <div id="eb-canvas-blocks" class="eb-canvas-blocks"></div>
                        </div>
                    </div>
                </div>

                {{-- Cara "Vista avanzada": el HTML ya renderizado por el
                servidor, con los marcadores sustituidos con datos de ejemplo. --}}
                <div id="eb-preview-pane" class="eb-preview-pane">
                    <div class="eb-preview-subject">Asunto: <strong id="eb-preview-subject"></strong></div>
                    <div id="eb-preview-loading">Generando vista previa…</div>
                    <div id="eb-preview-error" class="au-help-text" style="display:none;color:var(--au-critical, #b3261e)"></div>
                    <iframe id="eb-preview-frame" title="Vista previa del correo"></iframe>
                </div>

                <div class="eb-pane-note">
                    Los tokens se resuelven con datos de ejemplo, nunca con los de un cliente real.
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="blocks_json" id="blocks-json-field" value="">
    <input type="hidden" name="builder_mode" id="builder-mode-field" value="{{ old('builder_mode', $emailTemplate->builder_mode ?? 'code') }}">
    <input type="hidden" id="eb-advanced-mode-value" value="0">
</form>

{{-- Configuración del editor, como JSON inerte. Va aquí dentro (y no en el
     anfitrión) para que el fragmento sea autosuficiente: la pantalla de
     pestañas lo inyecta con innerHTML, que NO ejecuta <script> pero sí
     conserva su contenido, así que AU.EmailBuilder.readConfig() lo lee de
     aquí. La página completa usa exactamente el mismo bloque. --}}
@php
    $ebConfig = [
        'initialData' => $emailTemplate->blocks_json ?? null,
        'isEdit' => $isEdit,
        'logoUrl' => $ebLogoUrl,
        'previewUrl' => route('admin.email-templates.preview-blocks'),
    ];
@endphp
<script type="application/json" id="eb-config">@json($ebConfig)</script>
