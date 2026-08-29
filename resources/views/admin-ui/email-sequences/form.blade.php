@extends('admin-ui.layouts.master')

@php
    $isEdit = isset($emailSequence) && $emailSequence;
    $typeLabels = ['individual' => 'Individual', 'campaign' => 'Campaña', 'sequence' => 'Secuencia'];

    // Pasos con los que arranca el constructor: los guardados al editar, lo
    // que venía en old() si la validación falló, o un paso vacío de arranque
    // al crear (una secuencia necesita al menos uno).
    $initialSteps = old('steps');
    if (!is_array($initialSteps)) {
        $initialSteps = $isEdit
            ? $emailSequence->steps->map(fn ($step) => [
                'email_template_id' => $step->email_template_id,
                'wait_days' => $step->wait_days,
                'name' => $step->name,
            ])->values()->all()
            : [['email_template_id' => '', 'wait_days' => 0, 'name' => '']];
    }
    $initialSteps = array_values($initialSteps);
@endphp

@section('title', $isEdit ? 'Editar secuencia' : 'Nueva secuencia')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $isEdit ? 'Editar secuencia' : 'Nueva secuencia',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing', 'url' => route('admin.email-marketing.index', ['tab' => 'secuencias'])],
            ['label' => $isEdit ? $emailSequence->name : 'Nueva'],
        ],
        'actions' => '<a href="' . route('admin.email-marketing.index', ['tab' => 'secuencias']) . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver</a>',
    ])

    <form action="{{ $isEdit ? route('admin.email-sequences.update', $emailSequence->id) : route('admin.email-sequences.store') }}" method="POST">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start">
            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="au-card">
                    <div class="au-card-header">
                        <div class="au-card-title">{{ $isEdit ? 'Editar secuencia' : 'Nueva secuencia' }}</div>
                    </div>
                    <div class="au-card-body">
                        <div class="au-field">
                            <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
                            <input type="text" class="au-input" name="name" value="{{ old('name', $emailSequence->name ?? '') }}" placeholder="Ej. Seguimiento de cotización" required>
                            <span class="au-help-text">Solo para identificarla en la lista de secuencias.</span>
                        </div>

                        <div class="au-field">
                            <label class="au-label">Descripción</label>
                            <textarea class="au-input" name="description" rows="2" placeholder="Opcional — para qué sirve esta secuencia.">{{ old('description', $emailSequence->description ?? '') }}</textarea>
                        </div>

                        @include('admin-ui.partials._toggle-field', [
                            'name' => 'status',
                            'label' => 'Estado',
                            'description' => 'Solo las secuencias activas inscriben cotizaciones nuevas. Pausarla NO cancela los seguimientos que ya están en curso.',
                            'checked' => (int) old('status', $isEdit ? (int) $emailSequence->status : 1) === 1,
                        ])
                    </div>
                </div>

                <div class="au-card">
                    <div class="au-card-header">
                        <div class="au-card-title">Pasos de la secuencia</div>
                    </div>
                    <div class="au-card-body">
                        <span class="au-help-text" style="display:block;margin-bottom:12px">
                            Los días de espera se cuentan <strong>desde que se inscribe la cotización</strong>, no desde el paso anterior. Por ejemplo: 0, 3 y 7 días significa que el segundo correo sale 3 días después de la cotización y el tercero 7 días después de la cotización.
                        </span>

                        @error('steps')
                            <div class="au-help-text" style="display:block;color:var(--au-critical-text, #b42318);margin-bottom:12px">{{ $message }}</div>
                        @enderror

                        <div id="seq-steps"></div>

                        <button type="button" class="au-btn" id="seq-add-step" style="margin-top:12px">+ Agregar paso</button>
                    </div>
                </div>

                <div class="au-flex au-gap-2" style="justify-content:flex-end">
                    <a href="{{ route('admin.email-marketing.index', ['tab' => 'secuencias']) }}" class="au-btn">Cancelar</a>
                    <button type="submit" class="au-btn au-btn-primary">{{ $isEdit ? 'Guardar cambios' : 'Crear secuencia' }}</button>
                </div>
            </div>

            <div class="au-card">
                <div class="au-card-header">
                    <div class="au-card-title">Cómo funciona</div>
                </div>
                <div class="au-card-body">
                    <p class="au-help-text" style="display:block;margin-bottom:10px"><strong>Quién entra:</strong> todas las cotizaciones con cliente, incluidas las que están en borrador. Cada cotización se sigue por separado — un cliente con tres cotizaciones recibe tres seguimientos.</p>
                    <p class="au-help-text" style="display:block;margin-bottom:10px"><strong>Quién sale:</strong> si el cliente hace una compra pagada después de inscribirse, sale del seguimiento y sus pasos pendientes ya no se envían.</p>
                    <p class="au-help-text" style="display:block;margin-bottom:10px"><strong>Cuándo se manda:</strong> lo decide n8n. Este panel solo deja los pasos listos; el envío y los reintentos son de n8n.</p>
                    <p class="au-help-text" style="display:block"><strong>Placeholders útiles</strong> en las plantillas de los pasos: <code>@{{contact.name}}</code>, <code>@{{contact.email}}</code>, <code>@{{contact.company}}</code>, <code>@{{quote.quote_number}}</code>, <code>@{{quote.total}}</code>, <code>@{{quote.currency}}</code>, <code>@{{quote.valid_until}}</code>.</p>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Constructor de pasos: filas repetibles en JS vanilla. Cada fila
        // manda steps[N][...]; el índice N se reasigna al agregar/quitar, y
        // el ORDEN de las filas en pantalla es el orden de los pasos (el
        // backend usa la posición del arreglo, no un campo aparte).
        const seqTemplates = @json($templates->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'type' => $t->type])->values());
        const seqTypeLabels = @json($typeLabels);
        const seqInitial = @json($initialSteps);

        const seqContainer = document.getElementById('seq-steps');

        function seqOptions(selectedId) {
            const options = ['<option value="">Selecciona una plantilla…</option>'];
            seqTemplates.forEach((t) => {
                const selected = String(t.id) === String(selectedId) ? ' selected' : '';
                const label = `${t.name} (${seqTypeLabels[t.type] || 'Individual'})`;
                options.push(`<option value="${t.id}"${selected}>${AU.escapeHtml(label)}</option>`);
            });
            return options.join('');
        }

        function seqRender() {
            const rows = Array.from(seqContainer.querySelectorAll('[data-seq-step]'));
            rows.forEach((row, index) => {
                row.querySelector('[data-seq-order]').textContent = 'Paso ' + (index + 1);
                row.querySelectorAll('[data-seq-name]').forEach((input) => {
                    input.name = `steps[${index}][${input.getAttribute('data-seq-name')}]`;
                });
                // Nunca se deja quitar el último paso: una secuencia sin
                // pasos no es válida y el backend la rechazaría.
                row.querySelector('[data-seq-remove]').disabled = rows.length === 1;
            });
        }

        function seqAddRow(step) {
            const row = document.createElement('div');
            row.setAttribute('data-seq-step', '');
            row.style.cssText = 'display:grid;grid-template-columns:110px 2fr 110px 1.2fr auto;gap:10px;align-items:end;padding:12px 0;border-bottom:1px solid var(--au-border, #E5E7EB)';
            row.innerHTML = `
                <div class="au-label" data-seq-order style="padding-bottom:10px"></div>
                <div class="au-field" style="margin:0">
                    <label class="au-label">Plantilla</label>
                    <select class="au-select" data-seq-name="email_template_id" required>${seqOptions(step.email_template_id)}</select>
                </div>
                <div class="au-field" style="margin:0">
                    <label class="au-label">Días de espera</label>
                    <input type="number" class="au-input" data-seq-name="wait_days" min="0" max="365" value="${Number(step.wait_days) || 0}" required>
                </div>
                <div class="au-field" style="margin:0">
                    <label class="au-label">Nombre del paso</label>
                    <input type="text" class="au-input" data-seq-name="name" value="${AU.escapeHtml(step.name || '')}" placeholder="Opcional">
                </div>
                <button type="button" class="au-btn au-btn-sm" data-seq-remove style="margin-bottom:2px">Quitar</button>
            `;
            row.querySelector('[data-seq-remove]').addEventListener('click', () => {
                row.remove();
                seqRender();
            });
            seqContainer.appendChild(row);
            seqRender();
        }

        seqInitial.forEach((step) => seqAddRow(step || {}));
        document.getElementById('seq-add-step').addEventListener('click', () => seqAddRow({ email_template_id: '', wait_days: 0, name: '' }));
    </script>
@endpush
