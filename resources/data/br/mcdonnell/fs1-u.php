<?php

/*
| Landing pt-BR · McDonnell & Miller FS1/U
| URL: /br/mcdonnell-miller/fs1-u
|
| Todo el texto visible en portugués de Brasil. Sin precios. Sin datos técnicos
| que no consten en el catálogo del fabricante (presiones, temperaturas,
| materiales, certificaciones): la ficha lleva solo hechos seguros.
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => 'switch-de-flujo-fs1u-12-npt',

    'sku' => 'FS1/U',
    'categoria' => 'Interruptores de fluxo',

    // Frase corta para la tabla de relacionados de OTRAS landings.
    'relacao' => 'Interruptor de fluxo de 1/2 polegada NPT, para tubulações menores',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller FS1/U | Interruptor de Fluxo 1/2 pol.',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Interruptor de fluxo FS1/U McDonnell & Miller, conexão de 1/2 pol. NPT, para proteção de bombas e chillers. Distribuidor autorizado. Orçamento no WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller FS1/U — Interruptor de Fluxo de Paleta, 1/2 pol. NPT',
    // Párrafo bajo el H1.
    'resumo' => 'Interruptor de fluxo original McDonnell & Miller com conexão de 1/2 polegada NPT: detecta a presença ou a ausência de fluxo de líquido em tubulações de menor diâmetro e aciona um contato para proteger bombas, chillers e sistemas hidrônicos.',

    'bullets' => [
        'Detecção de fluxo por paleta: comuta o contato quando o líquido para de circular',
        'Conexão de processo de 1/2 polegada com rosca NPT, para linhas de menor diâmetro',
        'Intertravamento de bombas e chillers contra funcionamento sem fluxo',
        'Peça original McDonnell & Miller (Xylem), fornecida por canal autorizado',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Interruptor de fluxo de paleta para líquidos'],
        ['Modelo', 'McDonnell & Miller FS1/U'],
        ['Conexão de processo', '1/2 polegada, rosca NPT'],
        ['Princípio', 'Paleta acionada pelo fluxo, comutando um contato elétrico'],
        ['Função', 'Detecção de presença ou ausência de fluxo para intertravamento'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Proteção de bombas', 'Desliga a bomba quando não há fluxo, evitando o funcionamento a seco e danos ao selo mecânico.'],
        ['Intertravamento de chillers', 'Confirma a circulação de água gelada ou de condensação antes de liberar a partida do compressor.'],
        ['Sistemas hidrônicos', 'Supervisão de fluxo em circuitos de aquecimento e resfriamento com tubulações de pequeno diâmetro.'],
        ['Torres de resfriamento', 'Prova de fluxo em linhas auxiliares de água de condensação e de reposição.'],
        ['Linhas de processo', 'Sinalização de falta de fluxo em ramais de água de processo, refrigeração de equipamentos e circuitos de amostragem.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['fs8-w', 'fs4-3j', '150s-hd'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça FS1/U, a quantidade e, se possível, uma foto da plaqueta do interruptor instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['Como escolho o tamanho de conexão correto?', 'O FS1/U tem conexão de 1/2 polegada NPT, indicada para tubulações de menor diâmetro. Confira a rosca da tomada de processo onde o interruptor será instalado; para linhas de 1 polegada, veja os modelos FS8-W (NPT) e FS4-3J (BSPT).'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
