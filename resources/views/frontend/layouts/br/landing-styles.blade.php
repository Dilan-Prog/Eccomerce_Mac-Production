{{-- Estilos compartidos por todas las landings de producto de la sección
     Brasil. Cada landing hace @include de este parcial dentro de
     @push('styles'); así el CSS vive en un solo sitio. --}}
<style>
    /* Paleta del sitio (variables definidas en master), tipografia Poppins. */
    .br-landing { color: #1B2430; }
    .br-landing h1, .br-landing h2, .br-landing h3 { color: var(--azul-oscuro, #002856); font-weight: 700; margin: 0; }
    .br-landing p { text-wrap: pretty; }
    .br-landing a { text-decoration: none; }
    .br-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }

    .br-crumbs { font-size: 12.5px; color: var(--gris-claro-texto, #718096); padding: 14px 0; border-bottom: 1px solid var(--gris-borde, #DDE3EA); background: #F7F9FC; }
    .br-crumbs a { color: var(--azul-medio, #0057A8); }
    .br-crumbs .sep { color: #B6C0CC; margin: 0 7px; }
    .br-crumbs .cur { color: #1B2430; }

    /* ── Boton unico: WhatsApp ── */
    .br-wa { display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: #25D366; color: #fff; font-weight: 700; font-size: 16px; padding: 15px 28px; border-radius: 999px; box-shadow: 0 6px 18px rgba(37,211,102,.32); transition: transform .15s, box-shadow .15s; }
    .br-wa:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 8px 22px rgba(37,211,102,.4); }
    .br-wa svg { width: 20px; height: 20px; fill: currentColor; flex: none; }
    .br-wa--sm { font-size: 15px; padding: 13px 22px; }

    /* ── Hero ── */
    .br-hero { display: grid; grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr); gap: 56px; align-items: start; padding: 44px 0 48px; }
    .br-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .br-tag { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 600; padding: 6px 11px; letter-spacing: .02em; border-radius: 4px; }
    .br-tag--auth { border: 1px solid var(--azul-principal, #003E7E); color: var(--azul-principal, #003E7E); background: var(--azul-claro, #E6EFF8); }
    .br-tag--sku { border: 1px solid var(--gris-borde, #DDE3EA); color: var(--gris-claro-texto, #718096); font-family: ui-monospace, Menlo, Consolas, monospace; font-weight: 500; }
    .br-hero h1 { font-size: clamp(26px, 3.2vw, 40px); line-height: 1.12; margin-bottom: 16px; }
    .br-lead { font-size: 18px; line-height: 1.6; color: #405063; margin: 0 0 26px; max-width: 54ch; }
    .br-bullets { list-style: none; margin: 0 0 30px; padding: 0; display: grid; gap: 9px; }
    .br-bullets li { display: grid; grid-template-columns: 18px minmax(0,1fr); gap: 11px; font-size: 15px; line-height: 1.55; color: #2A3A4D; }
    .br-bullets li::before { content: '›'; color: var(--azul-medio, #0057A8); font-weight: 700; }
    .br-ctas { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
    .br-ctas small { font-size: 13px; color: var(--gris-claro-texto, #718096); line-height: 1.4; }

    /* ── Galeria ── */
    .br-gallery { border: 1px solid var(--gris-borde, #DDE3EA); background: #F7F9FC; padding: 22px; border-radius: 8px; }
    .br-gallery-main { width: 100%; height: 340px; display: grid; place-items: center; background: #fff; border-radius: 6px; overflow: hidden; }
    .br-gallery-main img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .br-thumbs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 14px; }
    .br-thumb { height: 72px; display: grid; place-items: center; background: #fff; border: 1.5px solid var(--gris-borde, #DDE3EA); border-radius: 6px; overflow: hidden; cursor: pointer; padding: 6px; transition: border-color .15s; }
    .br-thumb img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .br-thumb:hover, .br-thumb.is-active { border-color: var(--azul-medio, #0057A8); }

    /* ── Confianza ── */
    .br-trust { border-top: 1px solid var(--gris-borde, #DDE3EA); border-bottom: 1px solid var(--gris-borde, #DDE3EA); background: var(--azul-claro, #E6EFF8); }
    .br-trust-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 28px; padding: 26px 0; }
    .br-trust-item { display: grid; grid-template-columns: 40px minmax(0,1fr); gap: 14px; align-items: start; }
    .br-trust-ico { width: 40px; height: 40px; background: var(--azul-principal, #003E7E); color: #fff; display: grid; place-items: center; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 15px; font-weight: 600; border-radius: 6px; }
    .br-trust-item h3 { font-size: 14.5px; margin-bottom: 4px; }
    .br-trust-item p { margin: 0; font-size: 13px; line-height: 1.5; color: #405063; }

    /* ── Secciones ── */
    .br-section { padding: 52px 0 0; scroll-margin-top: 90px; }
    .br-section h2 { font-size: clamp(22px, 2.4vw, 28px); margin-bottom: 8px; }
    .br-section .br-sub { font-size: 15px; color: var(--gris-claro-texto, #718096); margin: 0 0 26px; max-width: 78ch; }

    .br-specs { display: grid; grid-template-columns: 1fr 1fr; gap: 0 48px; }
    .br-specs table { width: 100%; border-collapse: collapse; font-size: 14.5px; }
    .br-specs tr { border-bottom: 1px solid var(--gris-borde, #DDE3EA); }
    .br-specs th { text-align: left; font-weight: 500; color: var(--gris-claro-texto, #718096); padding: 13px 16px 13px 0; width: 42%; vertical-align: top; }
    .br-specs td { padding: 13px 0; color: #1B2430; font-weight: 500; }

    .br-codes { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
    .br-code { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12.5px; border: 1px solid var(--gris-borde, #DDE3EA); background: #fff; padding: 6px 10px; border-radius: 4px; color: var(--azul-principal, #003E7E); }

    .br-apps { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
    .br-app { border: 1px solid var(--gris-borde, #DDE3EA); padding: 22px; border-radius: 8px; background: #fff; }
    .br-app-ico { display: grid; place-items: center; width: 36px; height: 36px; background: var(--azul-claro, #E6EFF8); color: var(--azul-principal, #003E7E); font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 14px; font-weight: 600; margin-bottom: 14px; border-radius: 6px; }
    .br-app h3 { font-size: 16.5px; margin-bottom: 7px; }
    .br-app p { margin: 0; font-size: 14px; line-height: 1.55; color: #405063; }

    .br-xref-wrap { overflow-x: auto; }
    .br-xref { width: 100%; border-collapse: collapse; font-size: 14.5px; border: 1px solid var(--gris-borde, #DDE3EA); }
    .br-xref thead tr { background: var(--azul-oscuro, #002856); color: #fff; }
    .br-xref th, .br-xref td { text-align: left; padding: 13px 18px; }
    .br-xref tbody tr { border-top: 1px solid var(--gris-borde, #DDE3EA); }
    .br-xref tbody th { font-family: ui-monospace, Menlo, Consolas, monospace; font-weight: 600; color: var(--azul-principal, #003E7E); }
    .br-xref td.fab { color: #405063; }
    .br-xref td.obs { color: var(--gris-claro-texto, #718096); }

    .br-sheet { border: 1px solid var(--gris-borde, #DDE3EA); background: #F7F9FC; padding: 34px 38px; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 40px; align-items: center; border-radius: 8px; }
    .br-sheet h2 { font-size: 24px; margin-bottom: 8px; }
    .br-sheet p { margin: 0; font-size: 15px; line-height: 1.6; color: #405063; max-width: 62ch; }
    .br-sheet-cta { display: flex; flex-direction: column; gap: 9px; }
    .br-sheet-cta small { font-size: 12.5px; color: var(--gris-claro-texto, #718096); text-align: center; }

    .br-faq { display: grid; grid-template-columns: minmax(0, 320px) minmax(0, 1fr); gap: 48px; align-items: start; }
    .br-faq-intro p { margin: 0; font-size: 15px; line-height: 1.6; color: var(--gris-claro-texto, #718096); }
    .br-faq-list { border-top: 1px solid var(--gris-borde, #DDE3EA); }
    .br-faq details { border-bottom: 1px solid var(--gris-borde, #DDE3EA); }
    .br-faq summary { cursor: pointer; list-style: none; display: flex; justify-content: space-between; gap: 20px; align-items: center; padding: 18px 0; font-size: 16px; font-weight: 600; color: var(--azul-oscuro, #002856); }
    .br-faq summary::-webkit-details-marker { display: none; }
    .br-faq .sig { flex: none; width: 24px; height: 24px; border: 1px solid var(--gris-borde, #DDE3EA); display: grid; place-items: center; color: var(--azul-medio, #0057A8); font-size: 15px; transition: transform .2s ease; border-radius: 4px; }
    .br-faq details[open] .sig { transform: rotate(45deg); }
    .br-faq details p { margin: 0 0 20px; font-size: 15px; line-height: 1.65; color: #405063; max-width: 72ch; }

    .br-quote { background: var(--azul-principal, #003E7E); color: #fff; padding: 52px 0; margin-top: 52px; }
    .br-quote-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 420px); gap: 56px; align-items: center; }
    .br-quote h2 { color: #fff; font-size: 28px; margin-bottom: 12px; }
    .br-quote .br-lead { color: #C8D9EC; font-size: 16px; }
    .br-quote ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; font-size: 14.5px; color: var(--azul-claro, #E6EFF8); }
    .br-quote li { display: grid; grid-template-columns: 16px minmax(0,1fr); gap: 11px; }
    .br-quote li::before { content: '›'; color: #FF8A3D; font-weight: 700; }
    .br-quote-side { display: grid; gap: 14px; }
    .br-quote-side .br-wa { width: 100%; font-size: 17px; padding: 17px 24px; }
    .br-quote-box { border: 1px solid #4A709C; padding: 18px 20px; font-size: 14px; line-height: 1.7; color: #C8D9EC; border-radius: 6px; }
    .br-quote-box strong { display: block; color: #fff; margin-bottom: 6px; }
    .br-quote-box a { color: #fff; }

    /* Barra fija en movil: el 57 % del trafico de LATAM es movil. */
    .br-sticky { display: none; }

    @media (max-width: 991px) {
        .br-hero, .br-specs, .br-faq, .br-quote-grid, .br-sheet { grid-template-columns: 1fr; }
        .br-hero { gap: 28px; padding: 26px 0 30px; }
        .br-trust-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
        .br-apps { grid-template-columns: 1fr 1fr; }
        .br-gallery-main { height: 240px; }
        .br-section { padding-top: 34px; }
        .br-quote { padding: 34px 0; margin-top: 34px; }
        .br-quote h2 { font-size: 22px; }
    }
    @media (max-width: 575px) {
        .br-trust-grid, .br-apps { grid-template-columns: 1fr; }
        .br-ctas .br-wa { width: 100%; }
        .br-sheet { padding: 22px; }
        .br-xref th, .br-xref td { padding: 11px 12px; font-size: 13.5px; }
        .br-sticky { display: block; position: sticky; bottom: 0; z-index: 50; background: #fff; border-top: 1px solid var(--gris-borde, #DDE3EA); padding: 10px 14px; box-shadow: 0 -2px 10px rgba(0,40,86,.12); }
        .br-sticky .br-wa { width: 100%; padding: 14px; font-size: 15px; }
    }

    /* ── Carrusel "Você também pode se interessar" ── */
    .br-rel { padding: 52px 0 0; }
    .br-rel-head { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .br-rel-nav { display: flex; gap: 8px; }
    .br-rel-nav button { width: 38px; height: 38px; border: 1.5px solid var(--gris-borde, #DDE3EA); background: #fff; border-radius: 50%; color: var(--azul-principal, #003E7E); font-size: 18px; cursor: pointer; display: grid; place-items: center; }
    .br-rel-nav button:hover { border-color: var(--azul-medio, #0057A8); background: var(--azul-claro, #E6EFF8); }
    .br-rel-track { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; padding-bottom: 6px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .br-rel-track::-webkit-scrollbar { display: none; }
    .br-rel-card { flex: 0 0 calc((100% - 32px) / 3); scroll-snap-align: start; border: 1px solid var(--gris-borde, #DDE3EA); border-radius: 8px; background: #fff; overflow: hidden; display: flex; flex-direction: column; transition: box-shadow .15s, border-color .15s; }
    .br-rel-card:hover { border-color: var(--azul-medio, #0057A8); box-shadow: 0 6px 18px rgba(0,40,86,.10); text-decoration: none; }
    .br-rel-img { height: 170px; display: grid; place-items: center; background: #F7F9FC; padding: 14px; }
    .br-rel-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .br-rel-body { padding: 16px 18px 18px; display: grid; gap: 6px; }
    .br-rel-marca { font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gris-claro-texto, #718096); }
    .br-rel-card h3 { font-size: 16px; line-height: 1.3; }
    .br-rel-card p { margin: 0; font-size: 13.5px; line-height: 1.5; color: #405063; }
    .br-rel-link { margin-top: 6px; font-size: 13.5px; font-weight: 700; color: var(--azul-medio, #0057A8); }
    @media (max-width: 991px) { .br-rel-card { flex-basis: calc((100% - 16px) / 2); } .br-rel { padding-top: 34px; } }
    @media (max-width: 575px) { .br-rel-card { flex-basis: 84%; } }
</style>
