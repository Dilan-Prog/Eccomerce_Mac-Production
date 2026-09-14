@extends('frontend.layouts.br')

{{-- ============================================================================
     Landing pt-BR · Honeywell DC1040
     Plantilla de producto para el mercado de Brasil. Nace del diseño de Claude
     Design (mac-del-norte-dc1040) adaptado al layout propio de la sección
     Brasil (frontend.layouts.br): cabecera y pie en portugués, sin catálogo.

     TODA la conversión va por WhatsApp. No hay formularios ni botones de
     correo: el correo y el teléfono aparecen solo como texto.

     Para replicarla a otro producto: copiar este archivo, su ruta en
     routes/web.php y el método de HomeController; cambiar SKU, textos y tabla.
     ============================================================================ --}}

@php
    $br = config('brasil');
    $urlCanonica = route('br.honeywell-dc1040');
    $imagenPrincipal = $imagenes->first() ?? 'https://www.macdelnorte.com/uploads/media_670f02b4af765.Dc1040.webp';
@endphp

@section('title', 'DC1040 Honeywell | Controlador de Temperatura 1/4 DIN')

@section('meta_description', 'Honeywell DC1040, controlador de temperatura 1/4 DIN com entrada universal e PID. Distribuidor autorizado. Solicite orçamento e prazo de entrega pelo WhatsApp.')

@section('social_meta')
    <meta property="og:type" content="product">
    <meta property="og:title" content="DC1040 Honeywell | Controlador de Temperatura 1/4 DIN">
    <meta property="og:description" content="Controlador 1/4 DIN com entrada universal e PID auto-sintonizado. Distribuidor autorizado Honeywell. Orçamento e prazo de entrega para o Brasil.">
    <meta property="og:image" content="{{ $imagenPrincipal }}">
    <meta property="og:url" content="{{ $urlCanonica }}">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DC1040 Honeywell | Controlador de Temperatura 1/4 DIN">
    <meta name="twitter:description" content="Controlador 1/4 DIN com entrada universal e PID auto-sintonizado. Distribuidor autorizado Honeywell.">
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
          "name": "Honeywell DC1040 Controlador de Temperatura 1/4 DIN",
          "sku": "DC1040",
          "mpn": "DC1040",
          "brand": { "@type": "Brand", "name": "Honeywell" },
          "category": "Controladores de temperatura",
          "image": {!! json_encode($imagenes->values()->all(), JSON_UNESCAPED_SLASHES) !!},
          "description": "Controlador 1/4 DIN com entrada universal (termopar, RTD, mV, mA) e controle PID com auto-sintonia.",
          "additionalProperty": [
            { "@type": "PropertyValue", "name": "Entrada", "value": "Universal (termopar, RTD, mV, mA)" },
            { "@type": "PropertyValue", "name": "Alimentação", "value": "90-264 VAC" },
            { "@type": "PropertyValue", "name": "Formato", "value": "1/4 DIN, 96 x 96 mm" }
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
            { "@type": "ListItem", "position": 3, "name": "Honeywell DC1040", "item": "{{ $urlCanonica }}" }
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
            <span class="cur">Honeywell DC1040</span>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    <div class="br-wrap">
        <div class="br-hero">
            <div>
                <div class="br-tags">
                    <span class="br-tag br-tag--auth">Distribuidor autorizado Honeywell</span>
                    <span class="br-tag br-tag--sku">SKU: DC1040 · série DC1000</span>
                </div>
                <h1>Honeywell DC1040 — Controlador de Temperatura 1/4 DIN</h1>
                <p class="br-lead">Entrada universal e controle PID com auto-sintonia em um painel 96 × 96 mm: um único modelo atende termopar, RTD, mV e mA, reduzindo o estoque de peças de reposição da sua planta.</p>
                <ul class="br-bullets">
                    <li>Entrada universal: um só modelo cobre termopar, RTD, mV e mA</li>
                    <li>Saída configurável por relé, SSR ou 4–20 mA</li>
                    <li>Auto-sintonia PID reduz o tempo de comissionamento em campo</li>
                    <li>Alimentação ampla 90–264 VAC, sem transformador adicional</li>
                </ul>
                <div class="br-ctas">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1040_hero">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Solicitar orçamento pelo WhatsApp
                    </a>
                    <small>Resposta técnica<br>em até 1 dia útil</small>
                </div>
            </div>

            {{-- Galeria: fotos reales del catalogo (portada + galeria de cada
                 codigo de pedido DC1040). Las miniaturas cambian la principal. --}}
            <div class="br-gallery">
                <div class="br-gallery-main">
                    <img id="br-img-principal" src="{{ $imagenPrincipal }}"
                         alt="Honeywell DC1040, controlador de temperatura 1/4 DIN" width="520" height="340">
                </div>
                @if($imagenes->count() > 1)
                <div class="br-thumbs" role="list">
                    @foreach($imagenes as $i => $img)
                        <button type="button" class="br-thumb {{ $i === 0 ? 'is-active' : '' }}" data-src="{{ $img }}" role="listitem" aria-label="Foto {{ $i + 1 }} do Honeywell DC1040">
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
                        <tr><th scope="row">Tipo de produto</th><td>Controlador de temperatura/processo 1/4 DIN</td></tr>
                        <tr><th scope="row">Entrada</th><td>Universal — termopar, RTD, mV, mA</td></tr>
                        <tr><th scope="row">Saída de controle</th><td>Relé, SSR (pulso de tensão) ou 4–20 mA</td></tr>
                        <tr><th scope="row">Algoritmo de controle</th><td>PID com auto-sintonia (auto-tune)</td></tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr><th scope="row">Display</th><td>Duplo, 4 dígitos (PV e SV)</td></tr>
                        <tr><th scope="row">Alimentação</th><td>90–264 VAC</td></tr>
                        <tr><th scope="row">Dimensão do painel</th><td>96 × 96 mm (1/4 DIN)</td></tr>
                    </tbody>
                </table>
            </div>

            @if($codigos->isNotEmpty())
            {{-- Codigos de pedido reales del catalogo: son los terminos exactos que
                 busca un comprador tecnico. --}}
            <p class="br-sub" style="margin:26px 0 0">Códigos de pedido disponíveis:</p>
            <div class="br-codes">
                @foreach($codigos as $nombre)
                    <span class="br-code">{{ \Illuminate\Support\Str::of($nombre)->replace('Control de temperatura ', '') }}</span>
                @endforeach
            </div>
            @endif

        </section>

        {{-- ── Aplicaciones ── --}}
        <section class="br-section" id="aplicacoes">
            <h2>Aplicações típicas</h2>
            <p class="br-sub">Onde o DC1040 costuma ser especificado.</p>
            <div class="br-apps">
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">A</span><h3>Fornos industriais</h3><p>Controle de setpoint em fornos de tratamento térmico e queima cerâmica.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">B</span><h3>Extrusoras</h3><p>Controle por zona de aquecimento em linhas de extrusão de plásticos.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">C</span><h3>Autoclaves</h3><p>Ciclos de esterilização com rampa e patamar de temperatura.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">D</span><h3>Câmaras climáticas</h3><p>Ensaios de temperatura controlada em laboratórios e QA.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">E</span><h3>Processos químicos</h3><p>Reatores e tanques encamisados com aquecimento indireto.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">F</span><h3>Secadores e estufas</h3><p>Secagem de alimentos, grãos e pintura industrial.</p></div>
            </div>
        </section>

        {{-- ── Equivalencias: la puerta de entrada SEO por numero de parte ── --}}
        <section class="br-section" id="equivalencias">
            <h2>Substitui / Equivalente a</h2>
            <p class="br-sub">Referências cruzadas dentro da série DC1000 e modelos correlatos. A compatibilidade de recorte de painel e de fiação deve ser confirmada para cada aplicação.</p>
            <div class="br-xref-wrap">
                <table class="br-xref">
                    <thead>
                        <tr>
                            <th scope="col">Número de peça</th>
                            <th scope="col">Fabricante</th>
                            <th scope="col">Relação com o DC1040</th>
                            <th scope="col">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Cuando existan las landings hermanas, cada PN debe enlazar a la suya. --}}
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc1010') }}">DC1010</a></th><td class="fab">Honeywell</td><td>Mesma série, formato 1/16 DIN</td><td class="obs">Alternativa compacta; recorte de painel diferente</td></tr>
                        <tr><th scope="row">DC1020</th><td class="fab">Honeywell</td><td>Mesma série, formato 1/8 DIN vertical</td><td class="obs">Confirmar recorte de painel para a aplicação</td></tr>
                        <tr><th scope="row">DC1030</th><td class="fab">Honeywell</td><td>Mesma série, formato 1/8 DIN horizontal</td><td class="obs">Confirmar recorte de painel para a aplicação</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc1200') }}">DC1200</a></th><td class="fab">Honeywell</td><td>Série superior com recursos adicionais</td><td class="obs">Confirmar paridade de funções para a aplicação</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.honeywell-dc2800') }}">DC2800</a></th><td class="fab">Honeywell</td><td>Controlador 1/4 DIN de linha superior</td><td class="obs">Indicado quando o projeto exige mais saídas ou comunicação</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ── Ficha tecnica ── --}}
        <section class="br-section" id="ficha">
            <div class="br-sheet">
                <div>
                    <h2>Ficha técnica do DC1040</h2>
                    <p>Manual de instalação, tabela de códigos de pedido, diagramas de ligação e dimensional do recorte de painel. PDF em inglês, com resumo em português.</p>
                </div>
                <div class="br-sheet-cta">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa br-wa--sm track-conversion" data-type="whatsapp_br_dc1040_ficha">
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
                        <p>Garantia de fábrica Honeywell contra defeitos de fabricação, com prazo definido pelo fabricante para a linha DC1000. Como canal autorizado, encaminhamos o processo de RMA diretamente ao fabricante.</p>
                    </details>
                    <details>
                        <summary>Posso receber suporte técnico em português?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. O atendimento comercial e técnico para o Brasil é feito em português pelo WhatsApp, {{ \Illuminate\Support\Str::lower($br['hours']) }}, incluindo apoio na seleção do código de pedido, parametrização inicial e interpretação dos diagramas de ligação.</p>
                    </details>
                    <details>
                        <summary>Vocês ajudam a escolher entre os modelos da série DC1000?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. Informe a grandeza medida, o tipo de sensor, o número de zonas e o espaço disponível no painel; indicamos o formato (1/16, 1/8 ou 1/4 DIN) e o tipo de saída mais adequado, com as alternativas equivalentes.</p>
                    </details>
                </div>
            </div>
        </section>

        @include('frontend.layouts.br.relacionados', ['actual' => 'honeywell-dc1040'])
        @include('frontend.layouts.br.relacionados-mcdonnell')

    </div>

    {{-- ── Orcamento: un solo camino, WhatsApp ── --}}
    <section class="br-quote" id="orcamento">
        <div class="br-wrap">
            <div class="br-quote-grid">
                <div>
                    <h2>Solicitar orçamento do DC1040</h2>
                    <p class="br-lead">Envie o número de peça, a quantidade e a tensão de alimentação da sua aplicação. Um engenheiro de aplicação responde pelo WhatsApp com preço, prazo de entrega e documentação de importação.</p>
                    <ul>
                        <li>Sem preço publicado: todo fornecimento é sob orçamento.</li>
                        <li>Informe o código de pedido completo quando já tiver o item especificado em projeto.</li>
                        <li>Orçamentos para múltiplas zonas ou projetos recorrentes recebem condições específicas.</li>
                    </ul>
                </div>
                <div class="br-quote-side">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1040_orcamento">
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
        <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_dc1040_sticky">
            @include('frontend.layouts.br.icon-whatsapp')
            Solicitar orçamento pelo WhatsApp
        </a>
    </div>

</div>
@endsection

@push('scripts')
    @include('frontend.layouts.br.landing-scripts')
@endpush
