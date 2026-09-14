@extends('frontend.layouts.br')

{{-- ============================================================================
     Landing pt-BR · McDonnell & Miller (150S como produto estrela)
     Landing de MARCA para o mercado do Brasil, derivada da plantilla
     honeywell-dc1040. Diferença principal: `$codigos` traz produtos distintos
     da linha (150S, 157S, boias, foles, snap switches, interruptores de
     fluxo), não variantes de um só modelo, então os nomes se mostram tal
     como vêm do catálogo.

     TODA a conversão vai por WhatsApp. Sem formulários nem botões de e-mail.
     ============================================================================ --}}

@php
    $br = config('brasil');
    $urlCanonica = route('br.mcdonnell-miller');
    $imagenPrincipal = $imagenes->first() ?? 'https://www.macdelnorte.com/uploads/media_691154d65049d.150S-HD-1.webp';
@endphp

@section('title', 'McDonnell & Miller 150S | Corte por Baixa Água')

@section('meta_description', 'McDonnell & Miller 150S, controle de nível e corte por baixa água para caldeiras a vapor. Distribuidor autorizado. Orçamento e prazo pelo WhatsApp.')

@section('social_meta')
    <meta property="og:type" content="product">
    <meta property="og:title" content="McDonnell & Miller 150S | Corte por Baixa Água">
    <meta property="og:description" content="Controle de nível de água e corte por baixa água para caldeiras a vapor. Distribuidor autorizado McDonnell & Miller. Orçamento e prazo de entrega para o Brasil.">
    <meta property="og:image" content="{{ $imagenPrincipal }}">
    <meta property="og:url" content="{{ $urlCanonica }}">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="McDonnell & Miller 150S | Corte por Baixa Água">
    <meta name="twitter:description" content="Controle de nível de água e corte por baixa água para caldeiras a vapor. Distribuidor autorizado McDonnell & Miller.">
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
          "name": "McDonnell & Miller 150S Controle de Nível e Corte por Baixa Água",
          "sku": "150S",
          "mpn": "150S",
          "brand": { "@type": "Brand", "name": "McDonnell & Miller" },
          "category": "Controles de nível para caldeiras",
          "image": {!! json_encode($imagenes->values()->all(), JSON_UNESCAPED_SLASHES) !!},
          "description": "Controle combinado de nível de água tipo boia para caldeiras a vapor, com corte por baixo nível de água e controle de bomba de alimentação.",
          "additionalProperty": [
            { "@type": "PropertyValue", "name": "Tipo", "value": "Controle combinado de nível de água e corte por baixa água" },
            { "@type": "PropertyValue", "name": "Aplicação", "value": "Caldeiras a vapor" },
            { "@type": "PropertyValue", "name": "Princípio", "value": "Boia (flutuador)" }
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
            { "@type": "ListItem", "position": 2, "name": "Controles de nível para caldeiras" },
            { "@type": "ListItem", "position": 3, "name": "McDonnell & Miller 150S", "item": "{{ $urlCanonica }}" }
          ]
        },
        {
          "@type": "FAQPage",
          "mainEntity": [
            { "@type": "Question", "name": "Como faço para receber um orçamento?", "acceptedAnswer": { "@type": "Answer", "text": "Envie o modelo (150S, 157S ou a peça de reposição), a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Um engenheiro de aplicação confirma o item correto e devolve preço, prazo de entrega e condições de pagamento em até 1 dia útil." } },
            { "@type": "Question", "name": "Qual é o prazo de entrega para o Brasil?", "acceptedAnswer": { "@type": "Answer", "text": "Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira." } },
            { "@type": "Question", "name": "A Mac del Norte emite documentação de importação?", "acceptedAnswer": { "@type": "Answer", "text": "Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM) do produto." } },
            { "@type": "Question", "name": "Qual é a garantia do produto?", "acceptedAnswer": { "@type": "Answer", "text": "Garantia de fábrica McDonnell & Miller contra defeitos de fabricação. Como canal autorizado, encaminhamos o processo de RMA diretamente ao fabricante." } },
            { "@type": "Question", "name": "Vocês têm peças de reposição para o 150S?", "acceptedAnswer": { "@type": "Answer", "text": "Sim. Fornecemos boia (SA150-11), fole (SA150-106R), conjunto de snap switches (SWA150S) e flanges de 2 polegadas, além do mecanismo completo 150S-HD." } }
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
            <span>Controles de nível para caldeiras</span><span class="sep">/</span>
            <span class="cur">McDonnell &amp; Miller 150S</span>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    <div class="br-wrap">
        <div class="br-hero">
            <div>
                <div class="br-tags">
                    <span class="br-tag br-tag--auth">Distribuidor autorizado McDonnell &amp; Miller</span>
                    <span class="br-tag br-tag--sku">SKU: 150S · 157S · SA150 · FS8-W</span>
                </div>
                <h1>McDonnell &amp; Miller 150S — Controle de Nível e Corte por Baixa Água para Caldeiras</h1>
                <p class="br-lead">Controle combinado tipo boia para caldeiras a vapor: desliga o queimador quando o nível de água cai abaixo do seguro e comanda a bomba de alimentação, em um único equipamento. Linha completa McDonnell &amp; Miller (Xylem) com peças de reposição originais.</p>
                <ul class="br-bullets">
                    <li>Corte por baixo nível de água: proteção contra queima da caldeira</li>
                    <li>Controle de bomba de alimentação integrado no mesmo mecanismo</li>
                    <li>Peças de reposição originais: boia, fole, snap switches e flanges</li>
                    <li>Também 157S, controles 21/U e 64, e interruptores de fluxo FS</li>
                </ul>
                <div class="br-ctas">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm150s_hero">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Solicitar orçamento pelo WhatsApp
                    </a>
                    <small>Resposta técnica<br>em até 1 dia útil</small>
                </div>
            </div>

            {{-- Galeria: fotos reais do catálogo (uma por produto da linha).
                 Limitada a 8 miniaturas; as miniaturas trocam a principal. --}}
            <div class="br-gallery">
                <div class="br-gallery-main">
                    <img id="br-img-principal" src="{{ $imagenPrincipal }}"
                         alt="McDonnell & Miller 150S, controle de nível e corte por baixa água para caldeiras" width="520" height="340">
                </div>
                @if($imagenes->count() > 1)
                <div class="br-thumbs" role="list">
                    @foreach($imagenes->take(8) as $i => $img)
                        <button type="button" class="br-thumb {{ $i === 0 ? 'is-active' : '' }}" data-src="{{ $img }}" role="listitem" aria-label="Foto {{ $i + 1 }} da linha McDonnell & Miller">
                            <img src="{{ $img }}" alt="" loading="lazy" width="120" height="60">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Confiança ── --}}
    <div class="br-trust">
        <div class="br-wrap">
            <div class="br-trust-grid">
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">01</span>
                    <div><h3>Produto original com garantia de fábrica</h3><p>Fornecimento direto de canal autorizado, com rastreabilidade de lote.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">02</span>
                    <div><h3>Ficha técnica em PDF</h3><p>Manual de instalação, lista de peças e diagramas de ligação enviados pelo WhatsApp.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">03</span>
                    <div><h3>Atendimento técnico em português</h3><p>Engenheiros de aplicação para identificar o modelo instalado e a peça correta.</p></div>
                </div>
                <div class="br-trust-item">
                    <span class="br-trust-ico" aria-hidden="true">04</span>
                    <div><h3>Envio internacional com rastreio</h3><p>Despacho aéreo ou terrestre com código de rastreamento e suporte à liberação.</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="br-wrap">

        {{-- ── O que é o 150S ── --}}
        <section class="br-section" id="especificacoes">
            <h2>O que é o 150S</h2>
            <p class="br-sub">Resumo do produto. Consulte a ficha técnica do fabricante para dados de pressão, materiais e dimensões.</p>
            <div class="br-specs">
                <table>
                    <tbody>
                        <tr><th scope="row">Tipo</th><td>Controle combinado de nível de água e corte por baixa água</td></tr>
                        <tr><th scope="row">Aplicação</th><td>Caldeiras a vapor</td></tr>
                        <tr><th scope="row">Princípio</th><td>Boia (flutuador)</td></tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr><th scope="row">Funções</th><td>Corte por baixo nível de água + controle de bomba de alimentação</td></tr>
                        <tr><th scope="row">Marca</th><td>McDonnell &amp; Miller — Xylem</td></tr>
                        <tr><th scope="row">Família</th><td>150S / 157S e peças de reposição</td></tr>
                    </tbody>
                </table>
            </div>

            @php
                // Cada producto de la marca tiene su propia landing en
                // resources/data/br/mcdonnell/{slug}.php. Se enlaza por el
                // slug del catalogo, y el nombre sale en pt-BR desde ahi.
                $landingsMM = collect(glob(resource_path('data/br/mcdonnell/*.php')))
                    ->mapWithKeys(fn ($f) => [basename($f, '.php') => require $f]);
            @endphp
            @if($landingsMM->isNotEmpty())
            <p class="br-sub" style="margin:26px 0 0">Linha McDonnell &amp; Miller disponível — cada item tem a sua página:</p>
            <div class="br-codes">
                @foreach($landingsMM->sortBy('sku') as $slugMM => $l)
                    <a href="{{ route('br.mcdonnell-miller.produto', $slugMM) }}" class="br-code" title="{{ $l['nombre'] }}">{{ $l['sku'] }}</a>
                @endforeach
            </div>
            @endif

        </section>

        {{-- ── Aplicações ── --}}
        <section class="br-section" id="aplicacoes">
            <h2>Aplicações típicas</h2>
            <p class="br-sub">Onde os controles de nível McDonnell &amp; Miller costumam ser especificados.</p>
            <div class="br-apps">
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">A</span><h3>Caldeiras a vapor industriais</h3><p>Corte por baixa água e comando da bomba de alimentação em caldeiras flamotubulares e aquatubulares.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">B</span><h3>Caldeiras de água quente</h3><p>Proteção contra operação a seco em sistemas de aquecimento central e processo.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">C</span><h3>Geradores de vapor de alimentos e bebidas</h3><p>Cozimento, pasteurização e limpeza CIP com vapor de processo.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">D</span><h3>Hospitais e lavanderias</h3><p>Vapor para esterilização, calandras e secadoras industriais.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">E</span><h3>Indústria química e têxtil</h3><p>Vapor para reatores, tinturarias e ramas de acabamento.</p></div>
                <div class="br-app"><span class="br-app-ico" aria-hidden="true">F</span><h3>Manutenção e reposição</h3><p>Peças SA150, boias, foles e snap switches para recuperar controles instalados.</p></div>
            </div>
        </section>

        {{-- ── Peças e modelos relacionados ── --}}
        <section class="br-section" id="equivalencias">
            <h2>Peças e modelos relacionados</h2>
            <p class="br-sub">Itens da linha McDonnell &amp; Miller que costumam ser cotados junto com o 150S. A compatibilidade deve ser confirmada com o modelo instalado.</p>
            <div class="br-xref-wrap">
                <table class="br-xref">
                    <thead>
                        <tr>
                            <th scope="col">Número de peça</th>
                            <th scope="col">Fabricante</th>
                            <th scope="col">Relação com o 150S</th>
                            <th scope="col">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th scope="row"><a href="{{ route('br.mcdonnell-miller.produto', '157s') }}">157S</a></th><td class="fab">McDonnell &amp; Miller</td><td>Controle de nível, alternativa ao 150S</td><td class="obs">Confirmar compatibilidade com o modelo instalado</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.mcdonnell-miller.produto', 'sa150-11') }}">SA150-11</a></th><td class="fab">McDonnell &amp; Miller</td><td>Boia de reposição</td><td class="obs">Confirmar compatibilidade com o modelo instalado</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.mcdonnell-miller.produto', 'sa150-106r') }}">SA150-106R</a></th><td class="fab">McDonnell &amp; Miller</td><td>Fole de reposição</td><td class="obs">Confirmar compatibilidade com o modelo instalado</td></tr>
                        <tr><th scope="row"><a href="{{ route('br.mcdonnell-miller.produto', 'swa150s') }}">SWA150S</a></th><td class="fab">McDonnell &amp; Miller</td><td>Conjunto de snap switches</td><td class="obs">Confirmar compatibilidade com o modelo instalado</td></tr>
                        <tr><th scope="row">FS8-W / FS4-3J / FS1/U</th><td class="fab">McDonnell &amp; Miller</td><td>Interruptores de fluxo</td><td class="obs">Confirmar a conexão e a aplicação de cada modelo</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ── Ficha técnica ── --}}
        <section class="br-section" id="ficha">
            <div class="br-sheet">
                <div>
                    <h2>Ficha técnica do 150S</h2>
                    <p>Manual de instalação, lista de peças de reposição e diagramas de ligação. PDF em inglês, com resumo em português.</p>
                </div>
                <div class="br-sheet-cta">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa br-wa--sm track-conversion" data-type="whatsapp_br_mm150s_ficha">
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
                        <p>Envie o modelo (150S, 157S ou a peça de reposição), a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Um engenheiro de aplicação confirma o item correto e devolve preço, prazo de entrega e condições de pagamento em até 1 dia útil.</p>
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
                        <p>Garantia de fábrica McDonnell &amp; Miller contra defeitos de fabricação. Como canal autorizado, encaminhamos o processo de RMA diretamente ao fabricante.</p>
                    </details>
                    <details>
                        <summary>Posso receber suporte técnico em português?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. O atendimento comercial e técnico para o Brasil é feito em português pelo WhatsApp, {{ \Illuminate\Support\Str::lower($br['hours']) }}, incluindo apoio na identificação do modelo instalado, na escolha da peça de reposição e na interpretação dos diagramas de ligação.</p>
                    </details>
                    <details>
                        <summary>Vocês têm peças de reposição para o 150S?<span class="sig" aria-hidden="true">+</span></summary>
                        <p>Sim. Fornecemos boia (SA150-11), fole (SA150-106R), conjunto de snap switches (SWA150S) e flanges de 2 polegadas, além do mecanismo completo 150S-HD. Envie uma foto do controle instalado para confirmarmos a peça correta.</p>
                    </details>
                </div>
            </div>
        </section>

        @include('frontend.layouts.br.relacionados', ['actual' => 'mcdonnell-miller'])
        @include('frontend.layouts.br.relacionados-mcdonnell')

    </div>

    {{-- ── Orçamento: um só caminho, WhatsApp ── --}}
    <section class="br-quote" id="orcamento">
        <div class="br-wrap">
            <div class="br-quote-grid">
                <div>
                    <h2>Solicitar orçamento do 150S</h2>
                    <p class="br-lead">Envie o modelo, a quantidade e, se possível, uma foto da plaqueta do controle instalado. Um engenheiro de aplicação responde pelo WhatsApp com preço, prazo de entrega e documentação de importação.</p>
                    <ul>
                        <li>Sem preço publicado: todo fornecimento é sob orçamento.</li>
                        <li>Informe o número de peça completo quando já tiver o item especificado em projeto ou na lista de peças.</li>
                        <li>Orçamentos de kits de reposição ou de manutenção recorrente recebem condições específicas.</li>
                    </ul>
                </div>
                <div class="br-quote-side">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm150s_orcamento">
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

    {{-- Barra fixa só no celular --}}
    <div class="br-sticky">
        <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm150s_sticky">
            @include('frontend.layouts.br.icon-whatsapp')
            Solicitar orçamento pelo WhatsApp
        </a>
    </div>

</div>
@endsection

@push('scripts')
    @include('frontend.layouts.br.landing-scripts')
@endpush
