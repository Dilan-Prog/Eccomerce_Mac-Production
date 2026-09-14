@extends('frontend.layouts.br')

{{-- ============================================================================
     Landing pt-BR · producto individual McDonnell & Miller.

     UNA vista para toda la línea. Lo que cambia por producto llega en $p
     desde resources/data/br/mcdonnell/{slug}.php (ver 150s-hd.php como
     modelo). $imagenes son las fotos del producto del catálogo; $todos es la
     lista completa de landings de la marca, para "Outros produtos".

     TODA la conversión va por WhatsApp.
     ============================================================================ --}}

@php
    $br = config('brasil');
    $urlCanonica = route('br.mcdonnell-miller.produto', $slug);
    $imagenPrincipal = $imagenes->first() ?? config('brasil.landings.mcdonnell-miller.imagen');
    $relacionados = collect($p['relacionados'] ?? [])->filter(fn ($s) => isset($todos[$s]));
@endphp

@section('title', $p['titulo'])
@section('meta_description', $p['descricao'])

@section('social_meta')
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $p['titulo'] }}">
    <meta property="og:description" content="{{ $p['descricao'] }}">
    <meta property="og:image" content="{{ $imagenPrincipal }}">
    <meta property="og:url" content="{{ $urlCanonica }}">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $p['titulo'] }}">
    <meta name="twitter:description" content="{{ $p['descricao'] }}">
    <meta name="twitter:image" content="{{ $imagenPrincipal }}">
@endsection

@section('meta_robots', 'index, follow, max-image-preview:large')

@section('meta_tags')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Product',
                '@id' => $urlCanonica . '#product',
                'name' => $p['nombre'],
                'sku' => $p['sku'],
                'mpn' => $p['sku'],
                'brand' => ['@type' => 'Brand', 'name' => 'McDonnell & Miller'],
                'category' => $p['categoria'],
                'image' => $imagenes->values()->all(),
                'description' => $p['resumo'],
                'additionalProperty' => collect($p['ficha'])->map(fn ($f) => [
                    '@type' => 'PropertyValue', 'name' => $f[0], 'value' => $f[1],
                ])->values()->all(),
                'offers' => [
                    '@type' => 'Offer',
                    'availability' => 'https://schema.org/InStock',
                    'seller' => ['@type' => 'Organization', 'name' => 'Mac del Norte', 'url' => route('index')],
                    'url' => $urlCanonica,
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Brasil', 'item' => route('br.honeywell-dc1040')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'McDonnell & Miller', 'item' => route('br.mcdonnell-miller')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $p['sku'], 'item' => $urlCanonica],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => collect($p['faq'])->map(fn ($f) => [
                    '@type' => 'Question', 'name' => $f[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                ])->values()->all(),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
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
            <a href="{{ route('br.mcdonnell-miller') }}">McDonnell &amp; Miller</a><span class="sep">/</span>
            <span class="cur">{{ $p['sku'] }}</span>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    <div class="br-wrap">
        <div class="br-hero">
            <div>
                <div class="br-tags">
                    <span class="br-tag br-tag--auth">Distribuidor autorizado McDonnell &amp; Miller</span>
                    <span class="br-tag br-tag--sku">SKU: {{ $p['sku'] }}</span>
                </div>
                <h1>{{ $p['nombre'] }}</h1>
                <p class="br-lead">{{ $p['resumo'] }}</p>
                <ul class="br-bullets">
                    @foreach($p['bullets'] as $b)
                        <li>{{ $b }}</li>
                    @endforeach
                </ul>
                <div class="br-ctas">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm_{{ Str::slug($p['sku'], '_') }}_hero">
                        @include('frontend.layouts.br.icon-whatsapp')
                        Solicitar orçamento pelo WhatsApp
                    </a>
                    <small>Resposta técnica<br>em até 1 dia útil</small>
                </div>
            </div>

            <div class="br-gallery">
                <div class="br-gallery-main">
                    <img id="br-img-principal" src="{{ $imagenPrincipal }}" alt="{{ $p['nombre'] }}" width="520" height="340">
                </div>
                @if($imagenes->count() > 1)
                <div class="br-thumbs" role="list">
                    @foreach($imagenes->take(8) as $i => $img)
                        <button type="button" class="br-thumb {{ $i === 0 ? 'is-active' : '' }}" data-src="{{ $img }}" role="listitem" aria-label="Foto {{ $i + 1 }}">
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
                <div class="br-trust-item"><span class="br-trust-ico" aria-hidden="true">01</span><div><h3>Peça original com garantia de fábrica</h3><p>Fornecimento direto de canal autorizado, com rastreabilidade.</p></div></div>
                <div class="br-trust-item"><span class="br-trust-ico" aria-hidden="true">02</span><div><h3>Ficha técnica em PDF</h3><p>Catálogo do fabricante enviado pelo WhatsApp.</p></div></div>
                <div class="br-trust-item"><span class="br-trust-ico" aria-hidden="true">03</span><div><h3>Atendimento técnico em português</h3><p>Apoio na identificação do modelo e das peças de reposição.</p></div></div>
                <div class="br-trust-item"><span class="br-trust-ico" aria-hidden="true">04</span><div><h3>Envio internacional com rastreio</h3><p>Despacho aéreo ou terrestre com código de rastreamento e suporte à liberação.</p></div></div>
            </div>
        </div>
    </div>

    <div class="br-wrap">

        {{-- ── Ficha ── --}}
        <section class="br-section" id="especificacoes">
            <h2>O que é o {{ $p['sku'] }}</h2>
            <p class="br-sub">Dados conforme catálogo do fabricante. Consulte a ficha técnica para a tabela completa.</p>
            @php $mitad = (int) ceil(count($p['ficha']) / 2); @endphp
            <div class="br-specs">
                @foreach(array_chunk($p['ficha'], $mitad) as $col)
                <table><tbody>
                    @foreach($col as $f)
                        <tr><th scope="row">{{ $f[0] }}</th><td>{{ $f[1] }}</td></tr>
                    @endforeach
                </tbody></table>
                @endforeach
            </div>
        </section>

        {{-- ── Aplicaciones ── --}}
        <section class="br-section" id="aplicacoes">
            <h2>Aplicações típicas</h2>
            <p class="br-sub">Onde o {{ $p['sku'] }} costuma ser especificado.</p>
            <div class="br-apps">
                @foreach($p['aplicacoes'] as $i => $a)
                    <div class="br-app"><span class="br-app-ico" aria-hidden="true">{{ chr(65 + $i) }}</span><h3>{{ $a[0] }}</h3><p>{{ $a[1] }}</p></div>
                @endforeach
            </div>
        </section>

        {{-- ── Relacionados de la marca ── --}}
        @if($relacionados->isNotEmpty())
        <section class="br-section" id="equivalencias">
            <h2>Peças e modelos relacionados</h2>
            <p class="br-sub">Itens da linha McDonnell &amp; Miller que costumam acompanhar ou substituir o {{ $p['sku'] }}. Confirme a compatibilidade com o modelo instalado.</p>
            <div class="br-xref-wrap">
                <table class="br-xref">
                    <thead><tr><th scope="col">Número de peça</th><th scope="col">Produto</th><th scope="col">Relação com o {{ $p['sku'] }}</th></tr></thead>
                    <tbody>
                        @foreach($relacionados as $rs)
                            @php $r = $todos[$rs]; @endphp
                            <tr>
                                <th scope="row"><a href="{{ route('br.mcdonnell-miller.produto', $rs) }}">{{ $r['sku'] }}</a></th>
                                <td class="fab">{{ $r['nombre'] }}</td>
                                <td class="obs">{{ $r['relacao'] ?? 'Mesma linha McDonnell & Miller' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- ── Ficha tecnica ── --}}
        <section class="br-section" id="ficha">
            <div class="br-sheet">
                <div>
                    <h2>Ficha técnica do {{ $p['sku'] }}</h2>
                    <p>Catálogo do fabricante com dimensões, instalação e peças de reposição. PDF em inglês, com resumo em português.</p>
                </div>
                <div class="br-sheet-cta">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa br-wa--sm track-conversion" data-type="whatsapp_br_mm_{{ Str::slug($p['sku'], '_') }}_ficha">
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
                    <p>Dúvidas mais comuns sobre o {{ $p['sku'] }} no Brasil. Não encontrou a sua? Fale com o nosso time técnico pelo WhatsApp.</p>
                </div>
                <div class="br-faq-list">
                    @foreach($p['faq'] as $f)
                        <details>
                            <summary>{{ $f[0] }}<span class="sig" aria-hidden="true">+</span></summary>
                            <p>{{ $f[1] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        @include('frontend.layouts.br.relacionados', ['actual' => 'mcdonnell-miller'])
        @include('frontend.layouts.br.relacionados-mcdonnell', ['actual' => $slug])

    </div>

    {{-- ── Orcamento ── --}}
    <section class="br-quote" id="orcamento">
        <div class="br-wrap">
            <div class="br-quote-grid">
                <div>
                    <h2>Solicitar orçamento do {{ $p['sku'] }}</h2>
                    <p class="br-lead">Envie o número de peça, a quantidade e, se possível, uma foto do equipamento instalado. Respondemos pelo WhatsApp com preço, prazo de entrega e documentação de importação.</p>
                    <ul>
                        <li>Sem preço publicado: todo fornecimento é sob orçamento.</li>
                        <li>Informe o modelo completo do controle instalado para confirmarmos a compatibilidade.</li>
                        <li>Orçamentos de manutenção recorrente recebem condições específicas.</li>
                    </ul>
                </div>
                <div class="br-quote-side">
                    <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm_{{ Str::slug($p['sku'], '_') }}_orcamento">
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

    <div class="br-sticky">
        <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="br-wa track-conversion" data-type="whatsapp_br_mm_{{ Str::slug($p['sku'], '_') }}_sticky">
            @include('frontend.layouts.br.icon-whatsapp')
            Solicitar orçamento pelo WhatsApp
        </a>
    </div>

</div>
@endsection

@push('scripts')
    @include('frontend.layouts.br.landing-scripts')
@endpush
