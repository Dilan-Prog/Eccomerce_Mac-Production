<?php

/*
| Landing pt-BR · McDonnell & Miller 21/U
| URL: /br/mcdonnell-miller/21u
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => '21u-control-de-nivel-mym',

    'sku' => '21/U',
    'categoria' => 'Controles de nível para caldeiras',

    // Frase curta usada na tabela de relacionados de outras páginas.
    'relacao' => 'Controle de nível tipo boia da série 21',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller 21/U | Controle de Nível de Água',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Controle de nível 21/U McDonnell & Miller tipo boia para caldeiras: corte por baixa água e bomba. Distribuidor autorizado. Orçamento pelo WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller 21/U — Controle de Nível de Água para Caldeiras',
    // Párrafo bajo el H1.
    'resumo' => 'Controle de nível tipo boia da série 21, original McDonnell & Miller: conforme a configuração, atua como corte por baixo nível de água e/ou comando da bomba de alimentação da caldeira — confirme a aplicação no catálogo do fabricante.',

    'bullets' => [
        'Controle de nível tipo boia da série 21, peça original McDonnell & Miller',
        'Corte por baixo nível de água: protege a caldeira contra queima a seco',
        'Comando de bomba de alimentação conforme a configuração do modelo',
        'Fornecimento de canal autorizado, com rastreabilidade',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Controle de nível de água para caldeiras'],
        ['Família', 'McDonnell & Miller série 21'],
        ['Aplicação', 'Caldeiras — confirme a aplicação no catálogo do fabricante'],
        ['Princípio', 'Boia (flutuador) acionando contatos'],
        ['Funções', 'Corte por baixo nível de água e/ou controle de bomba, conforme configuração'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Caldeiras industriais', 'Proteção por baixo nível de água em caldeiras de plantas de processo.'],
        ['Geradores de vapor', 'Alimentos e bebidas, lavanderias e hospitais que dependem de operação contínua.'],
        ['Reposição de controle instalado', 'Substituição de um 21/U em fim de vida por peça original, sem adaptar a instalação.'],
        ['Manutenção preventiva', 'Troca programada do controle em paradas de manutenção.'],
        ['Indústria química e têxtil', 'Proteção de caldeiras em processos com alta demanda de vapor ou água quente.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['boia-21-30u', '64', 'sa91-60', '150s-hd'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça, a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['O 21/U faz corte por baixa água ou controle de bomba?', 'Depende da configuração do modelo: a série 21 é oferecida em versões para corte por baixo nível de água e para comando de bomba. Informe o modelo completo do controle instalado e o tipo de caldeira para confirmarmos a versão correta no catálogo do fabricante.'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
