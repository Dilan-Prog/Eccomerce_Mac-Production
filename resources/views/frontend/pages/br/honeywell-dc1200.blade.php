@extends('frontend.layouts.br')

{{-- ============================================================================
     Landing pt-BR · Honeywell DC1200 (DC1202 / DC1203 / DC120L)
     Plantilla de producto para el mercado de Brasil. Copia de la landing
     DC1040 (frontend.pages.br.honeywell-dc1040) adaptada a la serie DC1200:
     cabecera y pie en portugués, sin catálogo.

     TODA la conversión va por WhatsApp. No hay formularios ni botones de
     correo: el correo y el teléfono aparecen solo como texto.

     Para replicarla a otro producto: copiar este archivo, su ruta en
     routes/web.php y el método de HomeController; cambiar SKU, textos y tabla.
     ============================================================================ --}}

@php
    $br = config('brasil');
    $urlCanonica = route('br.honeywell-dc1200');
    $imagenPrincipal = $imagenes->first() ?? 'https://www.macdelnorte.com/uploads/media_67534c0c1ea0a.dc1202.webp';
@endphp

@section('title', 'DC1200 Honeywell | Controlador de Temperatura 1/8 DIN')

@section('meta_description', 'Honeywell DC1200 (DC1202, DC1203, DC120L), controlador de temperatura 1/8 DIN com PID. Distribuidor autorizado. Solicite orçamento e prazo pelo WhatsApp.')

@section('social_meta')
    <meta property="og:type" content="product">
    <meta property="og:title" content="DC1200 Honeywell | Controlador de Temperatura 1/8 DIN">
    <meta property="og:description" content="Controlador 1/8 DIN (48 × 96 mm) com entrada para termopar/RTD e PID com auto-sintonia. Distribuidor autorizado Honeywell. Orçamento e prazo de entrega para o Brasil.">
    <meta property="og:image" content="{{ $imagenPrincipal }}">
    <meta property="og:url" content="{{ $urlCanonica }}">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DC1200 Honeywell | Controlador de Temperatura 1/8 DIN">
    <meta name="twitter:description" content="Controlador 1/8 DIN com entrada para termopar/RTD e PID com auto-sintonia. Distribuidor autorizado Honeywell.">
    <meta name="twitter:image" content="{{ $imagenPrincipal }}">
@endsection

@section('meta_robots', 'index, follow, max-image-preview:large')

@section('meta_tags')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Product",
          "@id": "{{ $urlCanonica }}#product",
          "name": "Honeywell DC1200 Controlador de Temperatura 1/8 DIN",
          "sku": "DC1200",
          "mpn": "DC1200",
          "brand": { "@type": "Brand", "name": "Honeywell" },
          "category": "Controladores de temperatura",
          "image": {!! json_encode($imagenes->values()->all(), JSON_UNESCAPED_SLASHES) !!},
          "description": "Controlador de temperatura 1/8 DIN (48 x 96 mm) com entrada para termopar/RTD, saída por relé ou SSR e controle PID com auto-sintonia. Modelos DC1202, DC1203 e DC120L.",
          "additionalProperty": [
            { "@type": "PropertyValue", "name": "Entrada", "value": "Termopar / RTD" },
            { "@type": "PropertyValue", "name": "Alimentação", "value": "110/220 VAC ou 24/48 VAC/VDC, conforme código" },
            { "@type": "PropertyValue", "name": "Formato", "value": "1/8 DIN, 48 x 96 mm" }
          ],
          "offers": {
            "@type": "Offer",
            "availability": "https://schema.org/InStock",
            "seller": { "@type": "Organization", "name": "Mac del Norte", "url": "{{ route('index') }}" },
            "url": "{{ $urlCanonica }}"
          }
        },
        {
          "@type": "BreadcrumbList",
          "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "Brasil", "item": "{{ $urlCanonica }}" },
            { "@type": "ListItem", "position": 2, "name": "Controladores de temperatura" },
            { "@type": "ListItem", "position": 3, "name": "Honeywell DC1200", "item": "{{ $urlCanonica }}" }
          ]
        },
        {
          "@type": "FAQPage",
          "mainEntity": [
            { "@type": "Question", "name": "Como faço para receber um orçamento?", "acceptedAnswer": { "@type": "Answer", "text": "Envie o número de peça, a quantidade e a tensão de alimentação pelo WhatsApp. Um engenheiro de aplicação confirma o modelo correto e devolve preço, prazo de entrega e condições de pagamento em até 1 dia útil." } },
            { "@type": "Question", "name": "Qual é o prazo de entrega para o Brasil?", "acceptedAnswer": { "@type": "Answer", "text": "Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira." } },
            { "@type": "Question", "name": "A Mac del Norte emite documentação de importação?", "acceptedAnswer": { "@type": "Answer", "text": "Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM) do produto." } },
            { "@type": "Question", "name": "Qual é a garantia do produto?", "acceptedAnswer": { "@type": "Answer", "text": "Garantia de fábrica Honeywell contra defeitos de fabricação. Como canal autorizado, encaminhamos o processo de RMA diretamente ao fabricante." } },
            { "@type": "Question", "name": "Posso receber suporte técnico em português?", "acceptedAnswer": { "@type": "Answer", "text": "Sim. O atendimento comercial e técnico para o Brasil é feito em português, de segunda a sexta, pelo WhatsApp." } }
          ]
        }
      ]
    }
    </script>
@endsection

@section('canonical_URL')
    <link rel="canonical" href="{{ $urlCanonica }}">
    <link rel="alternate" hreflang="pt-BR" href="{{ $urlCanonica }}">
    <link rel="alternate" hreflang="x-default" href="{{ $urlCanonica }}">
@endsection

@push('styles')
    @include('frontend.layouts.br.landing-styles')
@endpush

@section('content')
<div class="br-landing">

    <nav class="br-crumbs" aria-label="Trilha">
        <div class="br-wrap">
            <span>Brasil</span><span class="sep">/</span>
            <span>Controladores de temperatura</span><span class="sep">/</span>
            <span class="cur">Honeywell DC1200</span>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    <div class="br-wrap">
        <div class="br-hero">
            <div>
                <div class="br-tags">
                    <span class="br-tag br-tag--auth">Distribuidor autorizado Honeywell</span>
                    <span class="br-tag br-tag--sku">SKU: DC1202 · DC1203 · DC120L</span>
                </div>
                <h1>Honeywell DC1200 — Controlador de Temperatura 1/8 DIN</h1>
                <p class="br-lead">Formato 1/8 DIN vertical (48 × 96 mm): o equilíbrio entre espaço de painel e legibilidade do display. Três variantes — DC1202, DC1203 e DC120L — e duas opções de alimentação para cobrir a maioria dos painéis de aquecimento.</p>
                <ul class="br-bullets">
                    <li>Entrada para termopar ou RTD, conforme o código de pedido</li>
                    <li>Saída de controle por relé ou SSR, definida no código</li>
                    <li>Controle PID com auto-sintonia e alarmes configuráveis</li>
                    <li>Alimentação 110/220 VAC ou 24/48 VAC/VDC, conforme o código</li>
                </ul>
                <div class="br-ctas">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1200_hero">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Solicitar orçamento pelo WhatsApp
                    </a>
                    <small>Resposta técnica<br>em até 1 dia útil</small>
                </div>
            </div>

            {{-- Galeria: fotos reales del catalogo (portada + galeria de cada
                 codigo de pedido DC1202 / DC1203 / DC120L). Las miniaturas cambian la principal. --}}
            <div class="br-gallery">
                <div class="br-gallery-main">
                    <img id="br-img-principal" src="{{ $imagenPrincipal }}"
                         alt="Honeywell DC1200, controlador de temperatura 1/8 DIN" width="520" height="340">
                </div>
                @if($imagenes->count() > 1)
                <div class="br-thumbs" role="list">
                    @foreach($imagenes as $i => $img)
                        <button type="button" class="br-thumb {{ $i === 0 ? 'is-active' : '' }}" data-src="{{ $img }}" role="listitem" aria-label="Foto {{ $i + 1 }} do Honeywell DC1200">
                            <img src="{{ $img }}" alt="" loading="lazy" width="120" height="60">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Confianza ── --}}
    <div class="br-trust">
        <div class="br-wrap">
            <div class="br-trust-grid">
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">01</span>
                    <div><h3>Produto original com garantia de fábrica</h3><p>Fornecimento direto de canal autorizado, com rastreabilidade de lote.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">02</span>
                    <div><h3>Ficha técnica em PDF</h3><p>Manual, códigos de pedido e diagramas de ligação enviados pelo WhatsApp.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">03</span>
                    <div><h3>Atendimento técnico em português</h3><p>Engenheiros de aplicação para dimensionamento e seleção de código.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">04</span>
                    <div><h3>Envio internacional com rastreio</h3><p>Despacho aéreo ou terrestre com código de rastreamento e suporte à liberação.</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="br-wrap">

        {{-- ── Especificaciones ── --}}
        <section class="br-section" id="especificacoes">
            <h2>Especificações técnicas</h2>
            <p class="br-sub">Dados conforme manual do fabricante. Consulte a ficha técnica para a tabela completa de códigos de pedido.</p>
            <div class="br-specs">
                <table>
                    <tbody>
                        <tr><th scope="row">Tipo de produto</th><td>Controlador de temperatura 1/8 DIN</td></tr>
                        <tr><th scope="row">Modelos</th><td>DC1202, DC1203 e DC120L</td></tr>
                        <tr><th scope="row">Entrada</th><td>Termopar ou RTD, conforme código de pedido</td></tr>
                        <tr><th scope="row">Saída de controle</th><td>Relé ou SSR, conforme código de pedido</td></tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr><th scope="row">Algoritmo de controle</th><td>PID com auto-sintonia (auto-tune)</td></tr>
                        <tr><th scope="row">Alarmes</th><td>Alarmes configuráveis</td></tr>
                        <tr><th scope="row">Alimentação</th><td>110/220 VAC ou 24/48 VAC/VDC, conforme código</td></tr>
                        <tr><th scope="row">Dimensão do painel</th><td>48 × 96 mm (1/8 DIN vertical)</td></tr>
                    </tbody>
                </table>
            </div>

            @if($codigos->isNotEmpty())
            {{-- Codigos de pedido reales del catalogo: son los terminos exactos que
                 busca un comprador tecnico. --}}
            <p class="br-sub" style="margin:26px 0 0">Códigos de pedido disponíveis:</p>
            <div class="br-codes">
                @foreach($codigos as $nombre)
                    <span class="br-code">{{ \Illuminate\Support\Str::of($nombre)->replace('Control de temperatura ', '')->replace('Control De Temperatura ', '') }}</span>
                @endforeach
            </div>
            @endif

        </section>

        {{-- ── Aplicaciones ── --}}
        <section class="br-section" id="aplicacoes">
            <h2>Aplicações típicas</h2>
            <p class="br-sub">Onde a série DC1200 costuma ser especificada.</p>
            <div class="br-apps">
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">A</span><h3>Fornos industriais</h3><p>Controle de setpoint em fornos de tratamento térmico e queima cerâmica.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">B</span><h3>Estufas e secadores</h3><p>Secagem de alimentos, grãos e cura de pintura industrial.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">C</span><h3>Extrusoras</h3><p>Controle por zona de aquecimento em linhas de extrusão de plásticos.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">D</span><h3>Seladoras e embalagem</h3><p>Temperatura de barras de selagem em máquinas de embalagem.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">E</span><h3>Injetoras</h3><p>Zonas de aquecimento do canhão e do molde em injeção de plásticos.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">F</span><h3>Tratamento térmico</h3><p>Têmpera, revenimento e recozimento com controle de patamar.</p></div>
            </div>
        </section>

        {{-- ── Equivalencias: la puerta de entrada SEO por numero de parte ── --}}
        <section class="br-section" id="equivalencias">
            <h2>Substitui / Equivalente a</h2>
            <p class="br-sub">Referências cruzadas dentro da linha Honeywell de controladores de temperatura. A compatibilidade de recorte de painel e de fiação deve ser confirmada para cada aplicação.</p>
            <div class="br-xref-wrap">
                <table class="br-xref">
                    <thead>
                        <tr>
                            <th scope="col">Número de peça</th>
                            <th scope="col">Fabricante</th>
                            <th scope="col">Relação com o DC1200</th>
                            <th scope="col">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc1010') }}">DC1010</a></th><td class="fab">Honeywell</td><td>Série DC1000, formato 1/16 DIN</td><td class="obs">Alternativa compacta; recorte de painel diferente</td></tr>
                        <tr><th scope="row">DC1020</th><td class="fab">Honeywell</td><td>Série DC1000, formato 1/8 DIN vertical</td><td class="obs">Confirmar recorte de painel para a aplicação</td></tr>
                        <tr><th scope="row">DC1030</th><td class="fab">Honeywell</td><td>Série DC1000, formato 1/8 DIN horizontal</td><td class="obs">Confirmar recorte de painel para a aplicação</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc1040') }}">DC1040</a></th><td class="fab">Honeywell</td><td>Série DC1000, formato 1/4 DIN</td><td class="obs">Indicado quando o painel comporta 96 × 96 mm</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc2800') }}">DC2800</a></th><td class="fab">Honeywell</td><td>Controlador 1/4 DIN de linha superior</td><td class="obs">Confirmar paridade de funções para a aplicação</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ── Ficha tecnica ── --}}
        <section class="br-section" id="ficha">
            <div class="br-sheet">
                <div>
                    <h2>Ficha técnica da série DC1200</h2>
                    <p>Manual de instalação, tabela de códigos de pedido, diagramas de ligação e dimensional do recorte de painel. PDF em inglês, com resumo em português.</p>
                </div>
                <div class="br-sheet-cta">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa br-wa--sm track-conversion" data-type="whatsapp_br_dc1200_ficha">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Receber ficha técnica
                    </a>
                    <small>Enviamos o PDF pelo WhatsApp</small>
                </div>
            </div>
        </section>

        {{-- ── FAQ ── --}}
        <section class="br-section" id="faq">
            <div class="br-faq">
                <div class="br-faq-intro">
                    <h2>Perguntas frequentes</h2>
                    <p>Dúvidas mais comuns de compradores e engenheiros no Brasil. Não encontrou a sua? Fale com o nosso time técnico pelo WhatsApp.</p>
                </div>
                <div class="br-faq-list">
                    <details>
                        <summary>Como faço para receber um orçamento?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Envie o número de peça (ou o código de pedido completo), a quantidade e a tensão de alimentação pelo WhatsApp. Um engenheiro de aplicação confirma o modelo correto e devolve preço, prazo de entrega e condições de pagamento em até 1 dia útil.</p>
                    </details>
                    <details>
                        <summary>Qual é o prazo de entrega para o Brasil?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira. Itens sob encomenda seguem o lead time do fabricante, informado no orçamento.</p>
                    </details>
                    <details>
                        <summary>A Mac del Norte emite nota fiscal e documentação de importação?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM) do produto, tudo o que o seu despachante precisa para a nacionalização. A nota fiscal brasileira é emitida quando a operação é faturada por parceiro local — confirme a modalidade no orçamento.</p>
                    </details>
                    <details>
                        <summary>Qual é a garantia do produto?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Garantia de fábrica Honeywell contra defeitos de fabricação, com prazo definido pelo fabricante para a série DC1200. Como canal autorizado, encaminhamos o processo de RMA diretamente ao fabricante.</p>
                    </details>
                    <details>
                        <summary>Posso receber suporte técnico em português?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. O atendimento comercial e técnico para o Brasil é feito em português pelo WhatsApp, {{ \Illuminate\Support\Str::lower($br['hours']) }}, incluindo apoio na seleção do código de pedido, parametrização inicial e interpretação dos diagramas de ligação.</p>
                    </details>
                    <details>
                        <summary>Vocês ajudam a escolher entre DC1202, DC1203 e DC120L?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. Informe o tipo de sensor (termopar ou RTD), o tipo de saída desejada (relé ou SSR), a tensão disponível no painel (110/220 VAC ou 24/48 VAC/VDC) e os alarmes necessários; indicamos a variante e o código de pedido completo mais adequados, com as alternativas equivalentes.</p>
                    </details>
                </div>
            </div>
        </section>

        @include('frontend.layouts.br.relacionados', ['actual' => 'honeywell-dc1200'])
        @include('frontend.layouts.br.relacionados-mcdonnell')

    </div>

    {{-- ── Orcamento: un solo camino, WhatsApp ── --}}
    <section class="br-quote" id="orcamento">
        <div class="br-wrap">
            <div class="br-quote-grid">
                <div>
                    <h2>Solicitar orçamento do DC1200</h2>
                    <p class="br-lead">Envie o número de peça, a quantidade e a tensão de alimentação da sua aplicação. Um engenheiro de aplicação responde pelo WhatsApp com preço, prazo de entrega e documentação de importação.</p>
                    <ul>
                        <li>Sem preço publicado: todo fornecimento é sob orçamento.</li>
                        <li>Informe o código de pedido completo quando já tiver o item especificado em projeto.</li>
                        <li>Orçamentos para múltiplas zonas ou projetos recorrentes recebem condições específicas.</li>
                    </ul>
                </div>
                <div class="br-quote-side">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1200_orcamento">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Falar no WhatsApp
                    </a>
                    <div class="br-quote-box">
                        <strong>Atendimento técnico</strong>
                        {{ $br['hours'] }}<br>
                        <a href="mailto:{{ $br['email'] }}">{{ $br['email'] }}</a> · <a href="{{ $br['phone_href'] }}">{{ $br['phone'] }}</a><br>
                        Suporte em português, espanhol e inglês.
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Barra fija solo en movil --}}
    <div class="br-sticky">
        <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1200_sticky">
            @include('frontend.layouts.br.icon-whatsapp')
            Solicitar orçamento pelo WhatsApp
        </a>
    </div>

</div>
@endsection

@push('scripts')
    @include('frontend.layouts.br.landing-scripts')
@endpush
