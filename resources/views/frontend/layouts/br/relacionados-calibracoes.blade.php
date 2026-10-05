{{-- Tercer carrusel: serviços de calibração. No son productos del catálogo,
     así que las tarjetas llevan un icono en vez de foto y todas llevan al
     WhatsApp, igual que el resto de la sección.

     OJO con lo que se promete: el laboratorio está acreditado ante la EMA
     (Entidad Mexicana de Acreditación) y usa patrones rastreables a CENAM.
     NO se afirma acreditação RBC/Inmetro ni reconocimiento automático en
     Brasil: se dice la trazabilidad real y se invita a confirmar. --}}
@php
    $br = config('brasil');
    $calibracoes = [
        [
            'ico' => '⚙',
            'titulo' => 'Calibração de controladores de temperatura',
            'texto' => 'Verificação de entrada e saída, ajuste de PID e relatório com desvios da malha.',
            'tag' => 'controladores',
        ],
        [
            'ico' => '📈',
            'titulo' => 'Calibração de videorregistradores',
            'texto' => 'Sinais elétricos e curva de calibração ponto a ponto, com relatório técnico.',
            'tag' => 'videorregistradores',
        ],
        [
            'ico' => '💧',
            'titulo' => 'Calibração de medidores de vazão',
            'texto' => 'Verificação de faixa e linearidade, com análise de desvio e incerteza.',
            'tag' => 'vazao',
        ],
        [
            'ico' => '🔧',
            'titulo' => 'Comissionamento e partida',
            'texto' => 'Instalação, parametrização e colocação em marcha do instrumento em campo.',
            'tag' => 'comissionamento',
        ],
        [
            'ico' => '📄',
            'titulo' => 'Relatório e certificado',
            'texto' => 'Documento com pontos medidos, desvios e incerteza, para auditorias e manutenção.',
            'tag' => 'certificado',
        ],
        [
            'ico' => '🗓',
            'titulo' => 'Plano de calibração periódica',
            'texto' => 'Programação por instrumento para manter a planta dentro da faixa de confiança.',
            'tag' => 'plano',
        ],
    ];
@endphp

<section class="br-rel" aria-labelledby="br-rel-cal-titulo">
    <div class="br-rel-head">
        <div>
            <h2 id="br-rel-cal-titulo">Calibrações e serviços técnicos</h2>
            <p class="br-sub" style="margin:6px 0 0">Laboratório próprio em Monterrey, com padrões rastreáveis a CENAM e acreditação EMA (México). Consulte-nos sobre a aceitação do relatório no seu processo.</p>
        </div>
        <div class="br-rel-nav" aria-hidden="true">
            <button type="button" data-dir="prev" aria-label="Anterior">‹</button>
            <button type="button" data-dir="next" aria-label="Próximo">›</button>
        </div>
    </div>
    <div class="br-rel-track">
        @foreach($calibracoes as $c)
            <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener"
               class="br-rel-card br-rel-card--serv track-conversion"
               data-type="whatsapp_br_calibracao_{{ $c['tag'] }}">
                <div class="br-rel-img br-rel-img--ico"><span aria-hidden="true">{{ $c['ico'] }}</span></div>
                <div class="br-rel-body">
                    <span class="br-rel-marca">Serviço técnico</span>
                    <h3>{{ $c['titulo'] }}</h3>
                    <p>{{ $c['texto'] }}</p>
                    <span class="br-rel-link">Consultar no WhatsApp ›</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
