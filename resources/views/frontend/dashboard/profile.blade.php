@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} | Mi cuenta
@endsection

@push('styles')
{{-- DataTables (solo se usa en el tab de pedidos) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
/* ── VARIABLES ADICIONALES ─────────────────────────────── */
:root {
    --negro-texto:      #1A202C;
    --verde-disponible: #2F855A;
    --verde-claro:      #F0FDF4;
    --amarillo-claro:   #FFFBEB;
    --radius-sm:   4px;
    --radius-md:   6px;
    --radius-lg:   8px;
    --radius-xl:  12px;
    --radius-full: 999px;
    --shadow-sm:  0 2px 4px rgba(0,0,0,0.04);
    --shadow-md:  0 4px 12px rgba(0,0,0,0.08);
    --shadow-cta: 0 4px 14px rgba(247,148,29,0.4);
}

/* ── BREADCRUMB ─────────────────────────────────────────── */
.mdn-breadcrumb { background:var(--blanco); border-bottom:1px solid var(--gris-borde); padding:12px 0; }
.mdn-breadcrumb-list { display:flex; align-items:center; gap:8px; font-size:13px; flex-wrap:wrap; }
.mdn-breadcrumb-list a { color:var(--gris-claro-texto); text-decoration:none; font-weight:600; }
.mdn-breadcrumb-list a:hover { color:var(--azul-principal); }
.mdn-breadcrumb-sep { color:var(--gris-borde); font-size:11px; }
.mdn-breadcrumb-cur { color:var(--azul-principal); font-weight:700; }

/* ── PAGE HERO ──────────────────────────────────────────── */
.mdn-page-hero {
    background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,#0057A8 100%);
    color:var(--blanco); padding:36px 0 30px; position:relative; overflow:hidden;
}
.mdn-page-hero::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);
    background-size:50px 50px;
}
.mdn-page-hero-inner { position:relative; z-index:2; }
.mdn-page-eyebrow {
    display:inline-block; background:rgba(246,173,28,0.18);
    border:1px solid rgba(246,173,28,0.45); color:var(--amarillo-destacado);
    font-size:11px; font-weight:800; padding:5px 12px; border-radius:var(--radius-sm);
    text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px;
}
.mdn-page-hero h1 { font-size:clamp(20px,2.5vw,28px); font-weight:800; color:var(--blanco); margin:0 0 6px; }
.mdn-page-hero p  { font-size:14px; opacity:0.88; margin:0; }

/* ── PROFILE LAYOUT ─────────────────────────────────────── */
.profile-page  { padding:32px 0 64px; background:var(--gris-fondo); }
.profile-grid  { display:grid; grid-template-columns:240px 1fr; gap:24px; align-items:start; }

/* ── SIDEBAR ────────────────────────────────────────────── */
.profile-sidebar {
    background:var(--blanco); border-radius:var(--radius-xl);
    border:1px solid var(--gris-borde); box-shadow:var(--shadow-sm);
    padding:8px 0; position:sticky; top:24px; overflow:hidden;
}
.profile-sidebar-link {
    display:flex; align-items:center; gap:10px; padding:11px 20px;
    font-size:13px; font-weight:600; color:var(--gris-texto);
    text-decoration:none; background:none; border:none;
    border-left:3px solid transparent; width:100%; text-align:left;
    cursor:pointer; font-family:inherit; transition:all 0.15s; line-height:1.4;
}
.profile-sidebar-link svg { width:16px; height:16px; flex-shrink:0; color:var(--gris-claro-texto); }
.profile-sidebar-link:hover { background:var(--gris-fondo); color:var(--azul-principal); }
.profile-sidebar-link:hover svg { color:var(--azul-principal); }
.profile-sidebar-link.active {
    background:var(--azul-claro); color:var(--azul-principal);
    font-weight:700; border-left-color:var(--accent-cta);
}
.profile-sidebar-link.active svg { color:var(--azul-principal); }
.profile-sidebar-link.danger { color:var(--rojo-error); }
.profile-sidebar-link.danger svg { color:var(--rojo-error); }
.profile-sidebar-link.danger:hover { background:#FEF2F2; }
.profile-sidebar-divider { height:1px; background:var(--gris-borde); margin:6px 0; }

/* ── MAIN CARD ──────────────────────────────────────────── */
.profile-main {
    background:var(--blanco); border-radius:var(--radius-xl);
    border:1px solid var(--gris-borde); box-shadow:var(--shadow-sm);
    padding:32px; min-height:480px;
}
.profile-section-header { margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--gris-borde); }
.profile-section-header h2 { font-size:19px; font-weight:800; color:var(--azul-principal); margin:0 0 4px; }
.profile-section-header p  { font-size:13px; color:var(--gris-claro-texto); margin:0; }
.profile-section-header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }

/* ── AVATAR ─────────────────────────────────────────────── */
.profile-avatar-row { display:flex; align-items:center; gap:16px; padding:0 0 24px; border-bottom:1px solid var(--gris-borde); margin-bottom:24px; }
.profile-avatar { width:56px; height:56px; background:linear-gradient(135deg,var(--azul-principal),#0057A8); color:var(--blanco); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:800; flex-shrink:0; }
.profile-avatar-info h3 { font-size:15px; font-weight:800; color:var(--azul-principal); margin:0 0 3px; }
.profile-avatar-info p  { font-size:12px; color:var(--gris-claro-texto); margin:0; }

/* ── FORMS ──────────────────────────────────────────────── */
.mdn-form-group { margin-bottom:18px; }
.mdn-form-group label { display:block; font-size:13px; font-weight:700; color:var(--azul-principal); margin-bottom:6px; }
.mdn-form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.mdn-form-input,.mdn-form-select,.mdn-form-textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--gris-borde);
    border-radius:var(--radius-md); font-size:14px; font-family:inherit;
    color:var(--negro-texto); background:var(--blanco); transition:all 0.2s;
}
.mdn-form-input:focus,.mdn-form-select:focus,.mdn-form-textarea:focus {
    outline:none; border-color:var(--azul-principal); box-shadow:0 0 0 3px rgba(0,62,126,0.1);
}
.mdn-form-input.is-invalid,.mdn-form-select.is-invalid { border-color:var(--rojo-error); }
.mdn-form-textarea { min-height:90px; resize:vertical; }
.mdn-form-select {
    cursor:pointer; appearance:none; padding-right:36px;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 12px center;
}
.mdn-form-help  { font-size:12px; color:var(--gris-claro-texto); margin-top:5px; }
.mdn-form-error { font-size:12px; color:var(--rojo-error); margin-top:4px; font-weight:600; }
.mdn-field-error { font-size:11px; color:var(--rojo-error); margin-top:3px; font-weight:600; display:none; }

/* ── BUTTONS ────────────────────────────────────────────── */
.mdn-btn { padding:11px 22px; border-radius:var(--radius-md); font-size:13px; font-weight:700; cursor:pointer; border:none; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:7px; transition:all 0.2s; font-family:inherit; white-space:nowrap; }
.mdn-btn svg { width:15px; height:15px; }
.mdn-btn-primary   { background:var(--accent-cta); color:var(--blanco); box-shadow:var(--shadow-cta); }
.mdn-btn-primary:hover { background:var(--accent-cta-hover); color:var(--blanco); transform:translateY(-1px); }
.mdn-btn-secondary { background:var(--blanco); color:var(--azul-principal); border:2px solid var(--azul-principal); }
.mdn-btn-secondary:hover { background:var(--azul-claro); color:var(--azul-principal); }
.mdn-btn-ghost  { background:var(--gris-fondo); color:var(--gris-texto); }
.mdn-btn-ghost:hover { background:var(--gris-borde); color:var(--azul-principal); }
.mdn-btn-danger { background:var(--blanco); color:var(--rojo-error); border:1.5px solid var(--gris-borde); }
.mdn-btn-danger:hover { border-color:var(--rojo-error); background:#FEF2F2; }
.mdn-btn-sm { padding:7px 13px; font-size:12px; }
.mdn-btn-xs { padding:5px 10px; font-size:11px; }

/* ── ALERTS ─────────────────────────────────────────────── */
.mdn-alert { display:flex; align-items:flex-start; gap:10px; padding:14px 16px; border-radius:var(--radius-lg); font-size:13px; font-weight:600; margin-bottom:20px; }
.mdn-alert svg { width:18px; height:18px; flex-shrink:0; margin-top:1px; }
.mdn-alert-success { background:var(--verde-claro); color:var(--verde-disponible); border:1px solid #BBF7D0; }
.mdn-alert-error   { background:#FEF2F2; color:var(--rojo-error); border:1px solid #FECACA; }

/* ── CONFIG ROWS (notificaciones) ───────────────────────── */
.mdn-config-row { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:16px 0; border-bottom:1px solid var(--gris-borde); }
.mdn-config-row:last-child { border-bottom:none; }
.mdn-config-info h4 { font-size:14px; font-weight:700; color:var(--negro-texto); margin:0 0 3px; }
.mdn-config-info p  { font-size:12px; color:var(--gris-claro-texto); line-height:1.5; margin:0; }
.mdn-toggle { width:46px; height:26px; background:var(--gris-borde); border-radius:var(--radius-full); border:none; cursor:pointer; position:relative; flex-shrink:0; transition:background 0.2s; }
.mdn-toggle::after { content:''; position:absolute; width:20px; height:20px; background:var(--blanco); border-radius:50%; top:3px; left:3px; transition:transform 0.2s; box-shadow:0 1px 4px rgba(0,0,0,0.15); }
.mdn-toggle.on { background:var(--verde-disponible); }
.mdn-toggle.on::after { transform:translateX(20px); }

/* ── INFO GRID (b2b) ────────────────────────────────────── */
.profile-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px; }
.profile-info-field { background:var(--gris-fondo); border-radius:var(--radius-lg); padding:14px 16px; }
.profile-info-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:var(--gris-claro-texto); display:block; margin-bottom:4px; }
.profile-info-value { font-size:15px; font-weight:700; color:var(--azul-principal); }

/* ── FISCAL BANNER ──────────────────────────────────────── */
.mdn-fiscal-verified { background:var(--verde-claro); border:1px solid #BBF7D0; border-radius:var(--radius-lg); padding:14px 16px; display:flex; gap:12px; align-items:flex-start; margin-bottom:22px; }
.mdn-fiscal-verified-icon { width:34px; height:34px; background:var(--verde-disponible); color:var(--blanco); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.mdn-fiscal-verified-icon svg { width:18px; height:18px; }
.mdn-fiscal-verified h4 { font-size:13px; font-weight:800; color:var(--verde-disponible); margin:0 0 3px; }
.mdn-fiscal-verified p  { font-size:12px; color:var(--gris-texto); margin:0; line-height:1.5; }

/* ── ADDRESS GRID ───────────────────────────────────────── */
.addr-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
.addr-card {
    background:var(--blanco); border:1.5px solid var(--gris-borde);
    border-radius:var(--radius-xl); padding:20px; position:relative;
    transition:border-color 0.2s;
}
.addr-card:hover { border-color:var(--azul-principal); }
.addr-card-title { font-size:15px; font-weight:800; color:var(--azul-principal); margin:0 0 10px; }
.addr-card-text  { font-size:13px; color:var(--gris-texto); line-height:1.65; margin:0 0 6px; }
.addr-card-contact { font-size:12px; color:var(--gris-claro-texto); margin:0 0 16px; }
.addr-card-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.addr-card-actions .addr-btn-edit {
    display:inline-flex; align-items:center; gap:5px;
    padding:7px 13px; font-size:12px; font-weight:700; cursor:pointer;
    border-radius:var(--radius-md); border:2px solid var(--azul-principal);
    color:var(--azul-principal); background:var(--blanco); transition:all 0.15s; font-family:inherit;
}
.addr-btn-edit:hover { background:var(--azul-claro); }
.addr-btn-edit svg { width:13px; height:13px; }
.addr-card-actions .addr-btn-del {
    background:none; border:none; color:var(--rojo-error); font-size:12px;
    font-weight:700; cursor:pointer; font-family:inherit; padding:7px 4px; transition:opacity 0.15s;
}
.addr-btn-del:hover { opacity:0.7; }
.addr-card-add {
    border:2px dashed var(--gris-borde); background:var(--gris-fondo);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:32px 20px; cursor:pointer; transition:all 0.2s; text-align:center;
    min-height:160px; border-radius:var(--radius-xl);
}
.addr-card-add:hover { border-color:var(--azul-principal); background:var(--azul-claro); }
.addr-card-add-icon { width:44px; height:44px; background:var(--azul-claro); color:var(--azul-principal); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:10px; }
.addr-card-add-icon svg { width:22px; height:22px; }
.addr-card-add-text { font-size:14px; font-weight:700; color:var(--azul-principal); }

/* ── MODAL ──────────────────────────────────────────────── */
.mdn-modal-backdrop {
    position:fixed; inset:0; background:rgba(0,0,0,0.45);
    z-index:1050; display:none; align-items:center; justify-content:center;
    padding:20px;
}
.mdn-modal-backdrop.open { display:flex; }
.mdn-modal {
    background:var(--blanco); border-radius:16px; width:100%; max-width:580px;
    max-height:90vh; overflow-y:auto; box-shadow:0 24px 64px rgba(0,0,0,0.22);
    animation:mdn-modal-in 0.2s ease;
}
@keyframes mdn-modal-in { from { opacity:0; transform:translateY(-16px); } to { opacity:1; transform:translateY(0); } }
.mdn-modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:22px 28px 18px; border-bottom:1px solid var(--gris-borde);
}
.mdn-modal-title { font-size:17px; font-weight:800; color:var(--azul-principal); margin:0; }
.mdn-modal-close {
    width:32px; height:32px; border:none; background:var(--gris-fondo);
    border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;
    color:var(--gris-texto); transition:background 0.15s; flex-shrink:0;
}
.mdn-modal-close:hover { background:var(--gris-borde); }
.mdn-modal-close svg { width:16px; height:16px; }
.mdn-modal-body    { padding:24px 28px; }
.mdn-modal-footer  { padding:16px 28px 24px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--gris-borde); }

/* ── DATATABLES OVERRIDE ────────────────────────────────── */
.mdn-datatable-wrap { overflow-x:auto; }
table.dataTable thead th { background:var(--gris-fondo); color:var(--azul-principal); font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:2px solid var(--gris-borde) !important; }
table.dataTable tbody tr:hover td { background:var(--azul-claro) !important; }
table.dataTable tbody td { font-size:13px; color:var(--gris-texto); vertical-align:middle; border-top:1px solid var(--gris-borde); }
.dataTables_wrapper .dataTables_paginate .paginate_button.current { background:var(--azul-principal) !important; color:#fff !important; border-color:var(--azul-principal) !important; border-radius:var(--radius-md); }
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { background:var(--azul-claro) !important; color:var(--azul-principal) !important; border-radius:var(--radius-md); }
.dataTables_wrapper .dataTables_filter input { border:1.5px solid var(--gris-borde); border-radius:var(--radius-md); padding:8px 12px; font-size:13px; }
.dataTables_wrapper .dataTables_filter input:focus { outline:none; border-color:var(--azul-principal); }
.dataTables_wrapper .dataTables_length select { border:1.5px solid var(--gris-borde); border-radius:var(--radius-md); padding:6px 28px 6px 10px; }

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media (max-width:960px) {
    .profile-grid { grid-template-columns:1fr; }
    .profile-sidebar { position:relative; top:0; display:flex; flex-wrap:wrap; padding:8px; gap:4px; }
    .profile-sidebar-link { border-left:none; border-radius:var(--radius-md); border-bottom:2px solid transparent; padding:8px 12px; font-size:12px; flex:1; min-width:max-content; justify-content:center; }
    .profile-sidebar-link.active { border-left-color:transparent; border-bottom-color:var(--accent-cta); }
    .profile-sidebar-divider { display:none; }
    .profile-main { padding:20px 16px; }
    .addr-grid { grid-template-columns:1fr; }
    .mdn-form-row { grid-template-columns:1fr; }
    .profile-info-grid { grid-template-columns:1fr; }
}
@media (max-width:560px) {
    .profile-sidebar-link span { display:none; }
}
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<div class="mdn-breadcrumb">
    <div class="container">
        <div class="mdn-breadcrumb-list">
            <a href="{{ route('index') }}">Inicio</a>
            <span class="mdn-breadcrumb-sep">›</span>
            <span class="mdn-breadcrumb-cur">Mi cuenta</span>
        </div>
    </div>
</div>

{{-- Page Hero --}}
<section class="mdn-page-hero">
    <div class="container mdn-page-hero-inner">
        <div class="mdn-page-eyebrow">Tu cuenta</div>
        <h1>Configuración del perfil</h1>
        <p>Administra tus datos personales, pedidos, direcciones y preferencias</p>
    </div>
</section>

{{-- Profile Section --}}
<section class="profile-page">
    <div class="container">

        @if(session('success'))
        <div class="mdn-alert mdn-alert-success" style="margin-bottom:20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mdn-alert mdn-alert-error" style="margin-bottom:20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
        </div>
        @endif

        @php
            $activeTab = request()->get('tab', 'personal');
            $userName  = Auth::user()->name;
            $parts     = explode(' ', trim($userName));
            $initials  = mb_strtoupper(mb_substr($parts[0], 0, 1));
            if (count($parts) > 1) $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
        @endphp

        <div class="profile-grid">

            {{-- ╔══════════════════════╗
                 ║     SIDEBAR          ║
                 ╚══════════════════════╝ --}}
            <aside class="profile-sidebar">

                <a href="{{ route('user.profile') }}"
                   class="profile-sidebar-link {{ $activeTab === 'personal' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Datos personales</span>
                </a>

                <a href="{{ route('user.profile') }}?tab=fiscal"
                   class="profile-sidebar-link {{ $activeTab === 'fiscal' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Datos fiscales</span>
                </a>

                <a href="{{ route('user.profile') }}?tab=password"
                   class="profile-sidebar-link {{ $activeTab === 'password' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span>Contraseña</span>
                </a>

                <a href="{{ route('user.profile') }}?tab=notifications"
                   class="profile-sidebar-link {{ $activeTab === 'notifications' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span>Notificaciones</span>
                </a>

                <a href="{{ route('user.profile') }}?tab=b2b"
                   class="profile-sidebar-link {{ $activeTab === 'b2b' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L1 21h22L12 2z"/></svg>
                    <span>Plan B2B</span>
                </a>

                <div class="profile-sidebar-divider"></div>

                <a href="{{ route('user.profile') }}?tab=orders"
                   class="profile-sidebar-link {{ $activeTab === 'orders' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span>Mis pedidos</span>
                </a>

                <a href="{{ route('user.profile') }}?tab=addresses"
                   class="profile-sidebar-link {{ $activeTab === 'addresses' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>Direcciones</span>
                </a>

                <div class="profile-sidebar-divider"></div>

                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="profile-sidebar-link danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Cerrar sesión</span>
                    </button>
                </form>

            </aside>

            {{-- ╔══════════════════════╗
                 ║  CONTENIDO PRINCIPAL  ║
                 ╚══════════════════════╝ --}}
            <div class="profile-main">

                {{-- ═══════════ DATOS PERSONALES ═══════════ --}}
                @if($activeTab === 'personal')
                <div class="profile-section-header">
                    <h2>Datos personales</h2>
                    <p>Información básica de tu cuenta</p>
                </div>
                <div class="profile-avatar-row">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <div class="profile-avatar-info">
                        <h3>{{ Auth::user()->name }}</h3>
                        <p>{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mdn-form-row">
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label for="name">Nombre completo <span style="color:var(--rojo-error)">*</span></label>
                            <input type="text" id="name" name="name" class="mdn-form-input"
                                   value="{{ old('name', Auth::user()->name) }}" placeholder="Tu nombre completo">
                            @error('name')<div class="mdn-form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label for="email">Correo electrónico <span style="color:var(--rojo-error)">*</span></label>
                            <input type="email" id="email" name="email" class="mdn-form-input"
                                   value="{{ old('email', Auth::user()->email) }}" placeholder="tu@correo.com">
                            @error('email')<div class="mdn-form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;">
                        <button type="submit" class="mdn-btn mdn-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Guardar cambios
                        </button>
                        <a href="{{ route('user.profile') }}" class="mdn-btn mdn-btn-ghost">Cancelar</a>
                    </div>
                </form>
                @endif

                {{-- ═══════════ DATOS FISCALES ═══════════ --}}
                @if($activeTab === 'fiscal')
                <div class="profile-section-header">
                    <h2>Datos fiscales</h2>
                    <p>Información para emisión de CFDI</p>
                </div>
                <div class="mdn-fiscal-verified">
                    <div class="mdn-fiscal-verified-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h4>Constancia de Situación Fiscal verificada</h4>
                        <p>Tus datos fiscales están al día. Puedes actualizar la información a continuación.</p>
                    </div>
                </div>
                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mdn-form-group">
                        <label>Razón social <span style="color:var(--rojo-error)">*</span></label>
                        <input type="text" name="razon_social" class="mdn-form-input" value="{{ old('razon_social') }}" placeholder="Nombre o razón social">
                    </div>
                    <div class="mdn-form-row">
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label>RFC <span style="color:var(--rojo-error)">*</span></label>
                            <input type="text" name="rfc" class="mdn-form-input" value="{{ old('rfc') }}" placeholder="XAXX010101000" maxlength="13">
                        </div>
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label>Régimen fiscal <span style="color:var(--rojo-error)">*</span></label>
                            <select name="regimen_fiscal" class="mdn-form-select">
                                <option value="">Selecciona un régimen</option>
                                <option value="601">601 - General de Ley Personas Morales</option>
                                <option value="612">612 - Personas Físicas con Actividades Empresariales</option>
                                <option value="626">626 - Régimen Simplificado de Confianza</option>
                            </select>
                        </div>
                    </div>
                    <div class="mdn-form-row" style="margin-top:18px">
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label>Uso CFDI <span style="color:var(--rojo-error)">*</span></label>
                            <select name="uso_cfdi" class="mdn-form-select">
                                <option value="G03">G03 - Gastos en general</option>
                                <option value="G01">G01 - Adquisición de mercancías</option>
                                <option value="I08">I08 - Otra maquinaria y equipo</option>
                            </select>
                        </div>
                        <div class="mdn-form-group" style="margin-bottom:0">
                            <label>Código Postal Fiscal <span style="color:var(--rojo-error)">*</span></label>
                            <input type="text" name="cp_fiscal" class="mdn-form-input" value="{{ old('cp_fiscal') }}" placeholder="66000" maxlength="5">
                        </div>
                    </div>
                    <div class="mdn-form-group" style="margin-top:18px">
                        <label>Domicilio fiscal completo</label>
                        <textarea name="domicilio_fiscal" class="mdn-form-textarea" placeholder="Av. Ejemplo 123, Col. Centro, Ciudad, Estado C.P. 00000">{{ old('domicilio_fiscal') }}</textarea>
                    </div>
                    <div class="mdn-form-group">
                        <label>Email para envío de facturas <span style="color:var(--rojo-error)">*</span></label>
                        <input type="email" name="email_factura" class="mdn-form-input" value="{{ old('email_factura', Auth::user()->email) }}" placeholder="contabilidad@empresa.mx">
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="mdn-btn mdn-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Guardar cambios
                        </button>
                    </div>
                </form>
                @endif

                {{-- ═══════════ CONTRASEÑA ═══════════ --}}
                @if($activeTab === 'password')
                <div class="profile-section-header">
                    <h2>Cambiar contraseña</h2>
                    <p>Usa una contraseña segura de al menos 8 caracteres</p>
                </div>
                <form action="{{ route('user.profile.update.password') }}" method="POST" style="max-width:480px;">
                    @csrf
                    <div class="mdn-form-group">
                        <label>Contraseña actual <span style="color:var(--rojo-error)">*</span></label>
                        <input type="password" name="current_password" class="mdn-form-input" placeholder="Tu contraseña actual">
                        @error('current_password')<div class="mdn-form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="mdn-form-group">
                        <label>Nueva contraseña <span style="color:var(--rojo-error)">*</span></label>
                        <input type="password" name="password" class="mdn-form-input" placeholder="Mínimo 8 caracteres">
                        @error('password')<div class="mdn-form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="mdn-form-group">
                        <label>Confirmar nueva contraseña <span style="color:var(--rojo-error)">*</span></label>
                        <input type="password" name="password_confirmation" class="mdn-form-input" placeholder="Repite la nueva contraseña">
                    </div>
                    <div class="mdn-form-help" style="margin-bottom:20px;">Usa al menos 8 caracteres con números y símbolos</div>
                    <button type="submit" class="mdn-btn mdn-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        Actualizar contraseña
                    </button>
                </form>
                @endif

                {{-- ═══════════ NOTIFICACIONES ═══════════ --}}
                @if($activeTab === 'notifications')
                <div class="profile-section-header">
                    <h2>Notificaciones</h2>
                    <p>Configura cómo y cuándo quieres recibir avisos</p>
                </div>
                @foreach([
                    ['email-orders',  'Estado de pedidos por correo',      'Recibe avisos cuando tu pedido cambie de estado (procesando, enviado, entregado)', true],
                    ['whatsapp',      'Notificaciones por WhatsApp',        'Recibe el seguimiento de tu pedido directo en tu celular', true],
                    ['quotes',        'Cotizaciones generadas',             'Te avisamos cuando tu cotización solicitada esté lista', true],
                    ['promotions',    'Ofertas y novedades',                'Recibe primero ofertas exclusivas y nuevos productos Honeywell', true],
                    ['maintenance',   'Recordatorios de mantenimiento',     'Te avisamos cuándo reordenar refacciones recurrentes', false],
                    ['newsletter',    'Newsletter mensual',                 'Tips técnicos, casos de uso y novedades del sector industrial', false],
                ] as [$key, $title, $desc, $on])
                <div class="mdn-config-row">
                    <div class="mdn-config-info">
                        <h4>{{ $title }}</h4>
                        <p>{{ $desc }}</p>
                    </div>
                    <button class="mdn-toggle {{ $on ? 'on' : '' }}" data-setting="{{ $key }}"></button>
                </div>
                @endforeach
                <div style="margin-top:24px;">
                    <button class="mdn-btn mdn-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Guardar preferencias
                    </button>
                </div>
                @endif

                {{-- ═══════════ PLAN B2B ═══════════ --}}
                @if($activeTab === 'b2b')
                <div class="profile-section-header">
                    <h2>Plan B2B / Revendedor</h2>
                    <p>Accede a precios especiales para revendedores y distribuidores</p>
                </div>
                <div style="background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 100%);color:var(--blanco);border-radius:12px;padding:28px;margin-bottom:24px;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-30%;right:-5%;width:280px;height:280px;background:radial-gradient(circle,rgba(246,173,28,0.18) 0%,transparent 60%);pointer-events:none;"></div>
                    <div style="position:relative;z-index:2;">
                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--amarillo-destacado);margin-bottom:10px;">★ Programa de revendedores</div>
                        <h3 style="color:var(--blanco);font-size:20px;margin:0 0 10px;">Accede a precios B2B exclusivos</h3>
                        <p style="opacity:0.88;font-size:13px;margin:0 0 24px;line-height:1.65;max-width:480px;">Obtén descuentos de hasta 20% sobre precio público, línea de crédito empresarial, ingeniero dedicado y atención personalizada para tu empresa.</p>
                        <a href="{{ route('contact') }}" class="mdn-btn mdn-btn-primary" style="display:inline-flex;background:var(--accent-cta);">Solicitar acceso B2B</a>
                    </div>
                </div>
                <div class="profile-info-grid">
                    @foreach([['Descuento B2B','Hasta 20%'],['Crédito empresarial','30 días plazo'],['Ingeniero dedicado','Soporte prioritario'],['Cotizaciones','Con CFDI incluido']] as [$lbl,$val])
                    <div class="profile-info-field">
                        <span class="profile-info-label">{{ $lbl }}</span>
                        <span class="profile-info-value">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ═══════════ MIS PEDIDOS ═══════════ --}}
                @if($activeTab === 'orders')
                <div class="profile-section-header">
                    <h2>Mis pedidos</h2>
                    <p>Historial completo de tus órdenes de compra</p>
                </div>
                <div class="mdn-datatable-wrap">
                    <table id="orders-table" class="table table-hover w-100" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Estado pago</th>
                                <th>Estado orden</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                @endif

                {{-- ═══════════ DIRECCIONES ═══════════ --}}
                @if($activeTab === 'addresses')
                <div class="profile-section-header-row profile-section-header">
                    <div>
                        <h2>Mis direcciones</h2>
                        <p>Gestiona las direcciones de entrega para tus pedidos</p>
                    </div>
                    <button class="mdn-btn mdn-btn-primary mdn-btn-sm" id="btn-add-address">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Agregar dirección
                    </button>
                </div>

                <div class="addr-grid" id="addr-grid">
                    @forelse($addresses as $addr)
                    <div class="addr-card" data-id="{{ $addr->id }}">
                        <div class="addr-card-title">{{ $addr->name }}</div>
                        <div class="addr-card-text">
                            {{ $addr->street }}{{ $addr->street_number ? ' #'.$addr->street_number : '' }},
                            Col. {{ $addr->col }}<br>
                            {{ $addr->city }}, {{ $addr->state }}&nbsp;C.P. {{ $addr->zip }}
                        </div>
                        <div class="addr-card-contact">
                            {{ $addr->email }} &middot; {{ $addr->phone }}
                            @if($addr->street_1)<br><span style="color:var(--gris-claro-texto);">Ref: {{ $addr->street_1 }}</span>@endif
                        </div>
                        <div class="addr-card-actions">
                            <button class="addr-btn-edit btn-edit-addr"
                                    data-id="{{ $addr->id }}"
                                    data-name="{{ $addr->name }}"
                                    data-email="{{ $addr->email }}"
                                    data-phone="{{ $addr->phone }}"
                                    data-street="{{ $addr->street }}"
                                    data-street_number="{{ $addr->street_number }}"
                                    data-col="{{ $addr->col }}"
                                    data-zip="{{ $addr->zip }}"
                                    data-city="{{ $addr->city }}"
                                    data-state="{{ $addr->state }}"
                                    data-street_1="{{ $addr->street_1 }}"
                                    data-address="{{ $addr->address }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Editar
                            </button>
                            <button class="addr-btn-del btn-del-addr"
                                    data-id="{{ $addr->id }}"
                                    data-url="{{ route('user.address.destroy', $addr->id) }}">
                                Eliminar
                            </button>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gris-claro-texto);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="44" height="44" style="margin:0 auto 12px;display:block;opacity:0.4;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <p style="font-size:14px;font-weight:600;">Aún no tienes direcciones guardadas</p>
                    </div>
                    @endforelse

                    {{-- Tarjeta "Agregar" --}}
                    <button class="addr-card-add" id="btn-add-address-2" type="button">
                        <div class="addr-card-add-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div class="addr-card-add-text">Agregar nueva dirección</div>
                    </button>
                </div>
                @endif

            </div>{{-- .profile-main --}}
        </div>{{-- .profile-grid --}}
    </div>{{-- .container --}}
</section>

{{-- ═══════════════════════════════════════
     MODAL DE DIRECCIÓN (agregar / editar)
════════════════════════════════════════ --}}
<div class="mdn-modal-backdrop" id="addr-modal-backdrop">
    <div class="mdn-modal" role="dialog" aria-modal="true" aria-labelledby="addr-modal-title">
        <div class="mdn-modal-header">
            <h2 class="mdn-modal-title" id="addr-modal-title">Nueva dirección</h2>
            <button class="mdn-modal-close" id="addr-modal-close" aria-label="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="addr-form" novalidate>
            @csrf
            <input type="hidden" id="addr-id" name="_addr_id" value="">
            <input type="hidden" name="_method" id="addr-method" value="POST">
            <div class="mdn-modal-body">

                <div class="mdn-form-group">
                    <label for="addr-name">Nombre / Alias de esta dirección <span style="color:var(--rojo-error)">*</span></label>
                    <input type="text" id="addr-name" name="name" class="mdn-form-input"
                           placeholder="Ej. Planta Industrial, Oficinas, Casa">
                    <div class="mdn-form-help">Te ayudará a identificarla rápido al hacer una compra</div>
                    <div class="mdn-field-error" id="err-name"></div>
                </div>

                <div class="mdn-form-row">
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-email">Correo de contacto <span style="color:var(--rojo-error)">*</span></label>
                        <input type="email" id="addr-email" name="email" class="mdn-form-input" placeholder="correo@empresa.mx">
                        <div class="mdn-field-error" id="err-email"></div>
                    </div>
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-phone">Teléfono <span style="color:var(--rojo-error)">*</span></label>
                        <input type="tel" id="addr-phone" name="phone" class="mdn-form-input" placeholder="10 dígitos">
                        <div class="mdn-field-error" id="err-phone"></div>
                    </div>
                </div>

                <div class="mdn-form-row" style="margin-top:18px">
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-street">Calle <span style="color:var(--rojo-error)">*</span></label>
                        <input type="text" id="addr-street" name="street" class="mdn-form-input" placeholder="Ej. Av. Constitución">
                        <div class="mdn-field-error" id="err-street"></div>
                    </div>
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-number">Número exterior</label>
                        <input type="text" id="addr-number" name="street_number" class="mdn-form-input" placeholder="Ej. 2424">
                    </div>
                </div>

                <div class="mdn-form-row" style="margin-top:18px">
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-col">Colonia <span style="color:var(--rojo-error)">*</span></label>
                        <input type="text" id="addr-col" name="col" class="mdn-form-input" placeholder="Ej. Centro">
                        <div class="mdn-field-error" id="err-col"></div>
                    </div>
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-zip">Código postal <span style="color:var(--rojo-error)">*</span></label>
                        <input type="text" id="addr-zip" name="zip" class="mdn-form-input" placeholder="64000" maxlength="5">
                        <div class="mdn-field-error" id="err-zip"></div>
                    </div>
                </div>

                <div class="mdn-form-row" style="margin-top:18px">
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-city">Ciudad <span style="color:var(--rojo-error)">*</span></label>
                        <input type="text" id="addr-city" name="city" class="mdn-form-input" placeholder="Ej. Monterrey">
                        <div class="mdn-field-error" id="err-city"></div>
                    </div>
                    <div class="mdn-form-group" style="margin-bottom:0">
                        <label for="addr-state">Estado <span style="color:var(--rojo-error)">*</span></label>
                        <select id="addr-state" name="state" class="mdn-form-select">
                            <option value="">Selecciona estado</option>
                            @foreach(['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima','Durango','Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        <div class="mdn-field-error" id="err-state"></div>
                    </div>
                </div>

                <div class="mdn-form-group" style="margin-top:18px">
                    <label for="addr-ref">Referencias <span style="color:var(--gris-claro-texto);font-weight:500;font-size:12px;">(opcional)</span></label>
                    <textarea id="addr-ref" name="street_1" class="mdn-form-textarea" rows="2"
                              placeholder="Ej. Edificio azul, frente al parque, junto a OXXO"></textarea>
                    <div class="mdn-form-help">Indícale al repartidor cómo encontrar tu ubicación</div>
                </div>

            </div>
            <div class="mdn-modal-footer">
                <button type="button" class="mdn-btn mdn-btn-ghost" id="addr-modal-cancel">Cancelar</button>
                <button type="submit" class="mdn-btn mdn-btn-primary" id="addr-submit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" id="addr-submit-icon"><polyline points="20 6 9 17 4 12"/></svg>
                    <span id="addr-submit-label">Guardar dirección</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    'use strict';

    /* ─── TOGGLE SWITCHES ──────────────────────────────── */
    document.querySelectorAll('.mdn-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () { this.classList.toggle('on'); });
    });

    /* ─── DATATABLES (pedidos) ─────────────────────────── */
    @if($activeTab === 'orders')
    $(document).ready(function () {
        $('#orders-table').DataTable({
            processing : true,
            serverSide : true,
            ajax       : '{{ route("user.orders.index") }}',
            language   : {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-MX.json'
            },
            columns: [
                { data:'Num.Orden',       name:'Num.Orden',       defaultContent:'—' },
                { data:'Fecha',           name:'Fecha' },
                { data:'Cantidad/Producto', name:'Cantidad/Producto', defaultContent:'—' },
                { data:'Pago',            name:'Pago' },
                { data:'Estado de Pago',  name:'Estado de Pago' },
                { data:'Estado De Orden', name:'Estado De Orden' },
                { data:'Acciones',        name:'Acciones', orderable:false, searchable:false },
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
        });
    });
    @endif

    /* ─── MODAL DE DIRECCIONES ─────────────────────────── */
    var backdrop   = document.getElementById('addr-modal-backdrop');
    var modalTitle = document.getElementById('addr-modal-title');
    var form       = document.getElementById('addr-form');
    var addrId     = document.getElementById('addr-id');
    var methodFld  = document.getElementById('addr-method');
    var submitLabel = document.getElementById('addr-submit-label');

    var ROUTES = {
        store  : '{{ route("user.address.store") }}',
        update : '{{ url("user/address") }}/',   // + id
        destroy: '{{ url("user/address") }}/',   // + id
    };
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function openModal(title, data) {
        modalTitle.textContent = title;
        submitLabel.textContent = data ? 'Guardar cambios' : 'Guardar dirección';
        form.reset();
        clearErrors();

        if (data) {
            addrId.value   = data.id || '';
            methodFld.value = 'PUT';
            setField('addr-name',   data.name);
            setField('addr-email',  data.email);
            setField('addr-phone',  data.phone);
            setField('addr-street', data.street);
            setField('addr-number', data.street_number);
            setField('addr-col',    data.col);
            setField('addr-zip',    data.zip);
            setField('addr-city',   data.city);
            setField('addr-state',  data.state);
            setField('addr-ref',    data.street_1);
        } else {
            addrId.value   = '';
            methodFld.value = 'POST';
        }

        backdrop.classList.add('open');
        document.getElementById('addr-name').focus();
    }

    function closeModal() { backdrop.classList.remove('open'); }

    function setField(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val || '';
    }

    function clearErrors() {
        document.querySelectorAll('.mdn-field-error').forEach(function (el) {
            el.style.display = 'none'; el.textContent = '';
        });
        document.querySelectorAll('.mdn-form-input.is-invalid,.mdn-form-select.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
    }

    function showErrors(errors) {
        var map = { name:'err-name', email:'err-email', phone:'err-phone', street:'err-street', col:'err-col', zip:'err-zip', city:'err-city', state:'err-state' };
        Object.keys(errors).forEach(function (key) {
            if (map[key]) {
                var el = document.getElementById(map[key]);
                if (el) { el.textContent = errors[key][0]; el.style.display = 'block'; }
            }
        });
    }

    /* Abrir modal nuevo */
    ['btn-add-address','btn-add-address-2'].forEach(function (id) {
        var btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', function () { openModal('Nueva dirección', null); });
    });

    /* Abrir modal editar */
    document.querySelectorAll('.btn-edit-addr').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal('Editar dirección', this.dataset);
        });
    });

    /* Cerrar modal */
    document.getElementById('addr-modal-close').addEventListener('click', closeModal);
    document.getElementById('addr-modal-cancel').addEventListener('click', closeModal);
    backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    /* Submit del formulario */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        var id      = addrId.value;
        var method  = id ? 'PUT' : 'POST';
        var url     = id ? ROUTES.update + id : ROUTES.store;
        var data    = new FormData(form);
        var submitBtn = document.getElementById('addr-submit-btn');

        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';

        fetch(url, {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-HTTP-Method-Override': method, 'Accept': 'application/json' },
            body   : data,
        })
        .then(function (res) {
            if (res.status === 422) return res.json().then(function (j) { throw { validation: j.errors }; });
            if (!res.ok) throw new Error('Error del servidor');
            return res.json();
        })
        .then(function (json) {
            if (json.status === 'success') {
                closeModal();
                window.location.href = '{{ route("user.profile") }}?tab=addresses';
            }
        })
        .catch(function (err) {
            if (err.validation) { showErrors(err.validation); }
            else { alert('Ocurrió un error. Por favor intenta de nuevo.'); }
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '';
        });
    });

    /* Eliminar dirección */
    document.querySelectorAll('.btn-del-addr').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id  = this.dataset.id;
            var url = this.dataset.url;
            if (!confirm('¿Eliminar esta dirección? Esta acción no se puede deshacer.')) return;

            fetch(url, {
                method : 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.status === 'success') {
                    var card = document.querySelector('.addr-card[data-id="' + id + '"]');
                    if (card) card.remove();
                }
            })
            .catch(function () { alert('No se pudo eliminar. Por favor intenta de nuevo.'); });
        });
    });

})();
</script>
@endpush

@endsection
