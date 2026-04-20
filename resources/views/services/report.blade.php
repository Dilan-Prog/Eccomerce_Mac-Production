<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Servicio Técnico — MAC DEL NORTE</title>
    <style>
        :root {
            --navy:         #0A1628;
            --navy-light:   #162240;
            --navy-mid:     #1E3355;
            --orange:       #F47920;
            --orange-hover: #E06810;
            --orange-glow:  rgba(244,121,32,0.15);
            --green:        #10B981;
            --green-bg:     #ECFDF5;
            --red:          #EF4444;
            --red-bg:       #FEF2F2;
            --amber:        #F59E0B;
            --amber-bg:     #FFFBEB;
            --g50:  #F8FAFC;
            --g100: #F1F5F9;
            --g200: #E2E8F0;
            --g300: #CBD5E1;
            --g400: #94A3B8;
            --g500: #64748B;
            --g600: #475569;
            --g700: #334155;
            --white: #FFFFFF;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--g50);
            color: var(--g700);
            min-height: 100vh;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--g300); border-radius: 3px; }

        input, select, textarea {
            width: 100%;
            border: 1.5px solid var(--g200);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            font-family: inherit;
            color: var(--g700);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394A3B8' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 3px var(--orange-glow);
        }
        textarea { resize: vertical; min-height: 100px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--g500);
            margin-bottom: 5px;
        }

        .field-group { margin-bottom: 18px; }
        .field-row { display: grid; gap: 16px; margin-bottom: 18px; }
        .field-row.col-2 { grid-template-columns: 1fr 1fr; }
        .field-row.col-3 { grid-template-columns: 1fr 1fr 1fr; }

        .card {
            background: var(--white);
            border-radius: 14px;
            padding: 28px;
            border: 1px solid var(--g100);
            box-shadow: 0 1px 4px rgba(10,22,40,0.05);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 26px;
            background: var(--orange);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            font-family: inherit;
            white-space: nowrap;
        }
        .btn-primary:hover { background: var(--orange-hover); }
        .btn-primary:active { transform: scale(0.97); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border: 1.5px solid var(--g300);
            color: var(--g600);
            border-radius: 7px;
            font-weight: 600;
            font-size: 13px;
            background: transparent;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            font-family: inherit;
            white-space: nowrap;
        }
        .btn-secondary:hover { background: var(--g100); }
        .btn-secondary:active { transform: scale(0.97); }
        .btn-secondary:disabled { opacity: 0.4; cursor: not-allowed; }

        .step-panel { display: none; }

        #app {
            max-width: 900px;
            margin: 0 auto;
            padding: 16px 16px 120px;
        }

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
            min-width: 280px;
        }
        .toast {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            animation: fadeIn 0.3s ease;
            pointer-events: auto;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toast.success { background: var(--green); }
        .toast.error   { background: var(--red); }
        .toast.warning { background: var(--amber); color: var(--g700); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--orange);
            display: inline-block;
            margin-bottom: 22px;
        }

        /* Mediciones table */
        #med-table { border-collapse: collapse; width: 100%; }
        #med-table th {
            background: var(--navy);
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        #med-table th:last-child { text-align: center; width: 44px; }
        #med-table td { padding: 6px 4px; border-bottom: 1px solid var(--g100); }
        #med-table tbody tr:hover { background: var(--g50); }
        #med-table input, #med-table select {
            font-size: 12px;
            padding: 6px 8px;
            min-width: 60px;
        }

        /* Firma canvas */
        #sig-canvas-wrap {
            border: 1.5px solid var(--g200);
            border-radius: 10px;
            overflow: hidden;
            background: var(--white);
            position: relative;
            max-width: 520px;
        }
        #sigCanvas {
            width: 100%;
            height: 130px;
            display: block;
            cursor: crosshair;
        }
        #sig-baseline {
            position: absolute;
            left: 0; right: 0;
            top: 105px;
            border-top: 1px dashed var(--g300);
            pointer-events: none;
        }

        /* Foto grid */
        #foto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
        }
        .foto-card {
            position: relative;
            border-radius: 10px;
            border: 1px solid var(--g200);
            background: var(--white);
            overflow: visible;
        }
        .foto-card img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
            display: block;
        }
        .foto-card .foto-remove {
            position: absolute;
            top: -8px; right: -8px;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: var(--red);
            color: white;
            border: 2px solid white;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .foto-card .foto-caption { padding: 8px; }
        .foto-card .foto-caption input { font-size: 12px; padding: 6px 8px; }

        /* Summary */
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .summary-item .s-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--g500); display: block; margin-bottom: 3px;
        }
        .summary-item .s-value { font-size: 14px; font-weight: 600; color: var(--g700); }

        @media (max-width: 640px) {
            .field-row.col-2, .field-row.col-3 { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            #app { padding: 12px 12px 110px; }
            .card { padding: 18px; }
        }
    </style>
</head>
<body>

<div id="toast-container"></div>

@include('services.partials.header')
@include('services.partials.steps-nav')

<div id="app">
    <div class="card">
        @include('services.partials.steps.general')
        @include('services.partials.steps.cliente')
        @include('services.partials.steps.equipo')
        @include('services.partials.steps.mediciones')
        @include('services.partials.steps.observaciones')
        @include('services.partials.steps.fotos')
        @include('services.partials.steps.firma')
    </div>
</div>

@include('services.partials.bottom-bar')

<script src="{{ asset('js/services/form-wizard.js') }}"></script>
<script src="{{ asset('js/services/signature-pad.js') }}"></script>
<script src="{{ asset('js/services/photo-upload.js') }}"></script>
<script src="{{ asset('js/services/pdf-generator.js') }}"></script>

</body>
</html>
