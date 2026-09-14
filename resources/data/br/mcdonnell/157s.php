<?php

/*
| Landing pt-BR · McDonnell & Miller 157S
| URL: /br/mcdonnell-miller/157s
|
| Todo el texto visible en portugués de Brasil. Sin precios. Sin datos técnicos
| que no consten en el catálogo del fabricante (presiones, temperaturas,
| materiales, certificaciones): la ficha lleva solo hechos seguros.
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => '157s-control-de-nivel-mcdonnell-miller',

    'sku' => '157S',
    'categoria' => 'Controles de nível para caldeiras',

    // Frase corta usada en la tabla de relacionados de OTRAS páginas.
    'relacao' => 'Controle de nível alternativo ao 150S',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller 157S | Controle de Nível',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Controle de nível 157S McDonnell & Miller tipo boia para caldeiras a vapor: corte por baixa água e comando de bomba. Distribuidor autorizado. WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller 157S — Controle de Nível Tipo Boia para Caldeiras a Vapor',
    // Párrafo bajo el H1.
    'resumo' => 'Controle de nível de água por boia da série 150S: interrompe a queima por baixo nível e comanda a bomba de alimentação da caldeira a vapor, com peça original McDonnell & Miller e fornecimento por canal autorizado.',

    'bullets' => [
        'Controle de nível completo, tipo boia, para caldeiras a vapor',
        'Corte por baixo nível de água: protege a caldeira contra queima a seco',
        'Comando de bomba de alimentação integrado no mesmo controle',
        'Alternativa ao 150S dentro da mesma família, com peças de reposição comuns',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Controle de nível de água para caldeiras'],
        ['Família', 'McDonnell & Miller série 150S'],
        ['Aplicação', 'Caldeiras a vapor'],
        ['Princípio', 'Boia (flutuador) acionando contatos'],
        ['Funções', 'Corte por baixo nível de água e controle de bomba de alimentação'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Caldeiras a vapor industriais', 'Proteção por baixo nível e comando de bomba em caldeiras de processo.'],
        ['Geradores de vapor', 'Alimentos e bebidas, lavanderias e hospitais que dependem de vapor contínuo.'],
        ['Retrofit de controles antigos', 'Substituição de controles de nível fora de linha por um modelo da série 150S.'],
        ['Indústria química e têxtil', 'Caldeiras em processos com alta demanda de vapor e operação contínua.'],
        ['Manutenção preventiva', 'Troca programada do controle em paradas de manutenção da caldeira.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['150s-hd', '150s-alta-pressao', 'sa150-11', 'sa150-106r', 'swa150s', 'flanges-2-polegadas'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça, a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['Qual é a diferença entre o 157S e o 150S?', 'Os dois pertencem à mesma família de controles de nível por boia da McDonnell & Miller e cumprem as mesmas funções de corte por baixa água e comando de bomba. A escolha depende do modelo já instalado e das condições de trabalho da caldeira; envie os dados da plaqueta e indicamos o modelo correto.'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
