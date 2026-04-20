<nav id="steps-nav" style="
    background: var(--navy-light);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    position: sticky;
    top: 68px;
    z-index: 99;
    overflow-x: auto;
    scrollbar-width: none;
">
    <div style="
        max-width: 900px;
        margin: 0 auto;
        padding: 0 16px;
        display: flex;
        min-width: max-content;
    ">
        @php
            $navSteps = [
                ['id' => 'general',       'icon' => '📋', 'label' => 'Generales'],
                ['id' => 'cliente',       'icon' => '🏢', 'label' => 'Cliente'],
                ['id' => 'equipo',        'icon' => '🔧', 'label' => 'Equipo'],
                ['id' => 'mediciones',    'icon' => '📊', 'label' => 'Mediciones'],
                ['id' => 'observaciones', 'icon' => '📝', 'label' => 'Observaciones'],
                ['id' => 'fotos',         'icon' => '📷', 'label' => 'Fotografías'],
                ['id' => 'firma',         'icon' => '✍️',  'label' => 'Firma y PDF'],
            ];
        @endphp

        @foreach($navSteps as $i => $step)
        <button
            class="step-tab"
            data-step="{{ $step['id'] }}"
            data-step-index="{{ $i }}"
            style="
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                color: var(--g400);
                padding: 11px 15px;
                font-size: 12px;
                font-weight: 600;
                cursor: pointer;
                font-family: inherit;
                white-space: nowrap;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: color 0.2s, border-color 0.2s, background 0.2s;
            "
        >
            <span>{{ $step['icon'] }}</span>
            <span>{{ $step['label'] }}</span>
        </button>
        @endforeach
    </div>
</nav>
