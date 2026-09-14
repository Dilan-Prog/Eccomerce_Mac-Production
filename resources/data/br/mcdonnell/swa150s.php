<?php

/*
| Landing pt-BR · McDonnell & Miller SWA150S
| URL: /br/mcdonnell-miller/swa150s
|
| Todo el texto visible en portugués de Brasil. Sin precios. Sin datos técnicos
| que no consten en el catálogo del fabricante (presiones, temperaturas,
| materiales, certificaciones): la ficha lleva solo hechos seguros.
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => 'ensamble-de-switches-snap-switch-swa150s-mcdonnell-miller',

    'sku' => 'SWA150S',
    'categoria' => 'Peças de reposição para controles de nível',

    // Frase corta usada en la tabla de relacionados de OTRAS páginas.
    'relacao' => 'Conjunto de snap switches de reposição para a série 150S',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller SWA150S | Conjunto de Snap Switches',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Conjunto de snap switches SWA150S McDonnell & Miller, reposição para o mecanismo dos controles de nível série 150S. Distribuidor autorizado. WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller SWA150S — Conjunto de Snap Switches para Controles Série 150S',
    // Párrafo bajo el H1.
    'resumo' => 'Conjunto (assembly) de snap switches de reposição para o mecanismo dos controles de nível McDonnell & Miller série 150S: recupera o corte por baixa água e o comando de bomba sem trocar o mecanismo completo.',

    'bullets' => [
        'Conjunto de snap switches original para o mecanismo da série 150S',
        'Reposição pontual dos contatos, sem trocar o mecanismo nem o corpo do controle',
        'Restabelece o corte por baixo nível de água e o comando de bomba',
        'Fornecimento de canal autorizado, com rastreabilidade',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Conjunto (assembly) de snap switches de reposição'],
        ['Família', 'McDonnell & Miller série 150S'],
        ['Aplicação', 'Mecanismo dos controles de nível 150S em caldeiras a vapor'],
        ['Função', 'Contatos elétricos acionados pela boia para corte por baixa água e comando de bomba'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Reparo de controles 150S', 'Substituição dos switches quando os contatos apresentam falha ou desgaste.'],
        ['Manutenção preventiva', 'Troca programada dos contatos em paradas de manutenção da caldeira.'],
        ['Estoque de peças críticas', 'Item de reposição para plantas com várias caldeiras equipadas com controles 150S.'],
        ['Caldeiras a vapor industriais', 'Alimentos e bebidas, lavanderias, hospitais e indústria de processo.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['150s-hd', '157s', '150s-alta-pressao', 'sa150-11', 'sa150-106r'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça, a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['O SWA150S serve para qualquer controle da série 150S?', 'O SWA150S é o conjunto de snap switches do mecanismo da série 150S. Como existem variações de modelo e de ano de fabricação, informe o modelo completo da plaqueta para confirmarmos a compatibilidade antes do envio.'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
