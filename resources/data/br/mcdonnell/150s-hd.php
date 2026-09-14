<?php

/*
| Landing pt-BR · McDonnell & Miller 150S-HD
| URL: /br/mcdonnell-miller/150s-hd
|
| Este archivo es la PLANTILLA de datos para las landings de la marca. Todo
| el texto visible en portugués de Brasil. Sin precios. Sin datos técnicos
| que no consten en el catálogo del fabricante (presiones, temperaturas,
| materiales, certificaciones): la ficha lleva solo hechos seguros.
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => '150s-hd-mecanismo-para-control-de-nivel-mcdonnell-miller',

    'sku' => '150S-HD',
    'categoria' => 'Controles de nível para caldeiras',
    // Como se describe este producto en la tabla de relacionados de OTRAS landings.
    'relacao' => 'Mecanismo de reposição da série 150S',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller 150S-HD | Mecanismo de Controle de Nível',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Mecanismo 150S-HD McDonnell & Miller para controle de nível e corte por baixa água em caldeiras a vapor. Distribuidor autorizado. Orçamento pelo WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller 150S-HD — Mecanismo de Controle de Nível para Caldeiras',
    // Párrafo bajo el H1.
    'resumo' => 'Mecanismo de reposição da série 150S: mantém a proteção por baixo nível de água e o comando da bomba de alimentação da sua caldeira a vapor, com peça original McDonnell & Miller.',

    'bullets' => [
        'Mecanismo original da série 150S, para reposição sem trocar o corpo instalado',
        'Corte por baixo nível de água: protege a caldeira contra queima a seco',
        'Comando de bomba de alimentação integrado no mesmo mecanismo',
        'Fornecimento de canal autorizado, com rastreabilidade',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Mecanismo (cabeça) de controle de nível para caldeiras'],
        ['Família', 'McDonnell & Miller série 150S'],
        ['Aplicação', 'Caldeiras a vapor'],
        ['Princípio', 'Boia (flutuador) acionando contatos'],
        ['Funções', 'Corte por baixo nível de água e controle de bomba de alimentação'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Caldeiras a vapor industriais', 'Reposição do mecanismo em controles 150S já instalados na planta.'],
        ['Geradores de vapor', 'Alimentos e bebidas, lavanderias e hospitais que dependem de vapor contínuo.'],
        ['Manutenção preventiva', 'Troca programada do mecanismo em paradas de manutenção.'],
        ['Indústria química e têxtil', 'Proteção de caldeiras em processos com alta demanda de vapor.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['150s-alta-pressao', '157s', 'sa150-11', 'sa150-106r', 'swa150s', 'flanges-2-polegadas'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça, a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['O 150S-HD serve para o meu controle 150S instalado?', 'O 150S-HD é o mecanismo de reposição da série 150S. Informe o modelo completo e o ano aproximado do controle instalado para confirmarmos a compatibilidade antes do envio.'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
