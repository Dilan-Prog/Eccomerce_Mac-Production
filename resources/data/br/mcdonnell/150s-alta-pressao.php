<?php

/*
| Landing pt-BR · McDonnell & Miller 150S (alta pressão)
| URL: /br/mcdonnell-miller/150s-alta-pressao
|
| Todo el texto visible en portugués de Brasil. Sin precios. Sin datos técnicos
| que no consten en el catálogo del fabricante (presiones, temperaturas,
| materiales, certificaciones): la ficha lleva solo hechos seguros.
*/

return [
    // Slug del producto en el catálogo (tabla products). De ahí salen las fotos.
    'catalogo_slug' => 'hi-pressure-control-150s-mcdonell-miller',

    'sku' => '150S (alta pressão)',
    'categoria' => 'Controles de nível para caldeiras',

    // Frase corta usada en la tabla de relacionados de OTRAS páginas.
    'relacao' => 'Versão do 150S para caldeiras de maior pressão',

    // <title>, ≤ 60 caracteres. Patrón: [Marca] [PN] | [Tipo de produto]
    'titulo' => 'McDonnell & Miller 150S | Controle de Nível Alta Pressão',
    // meta description, ≤ 155 caracteres.
    'descricao' => 'Controle de nível 150S alta pressão McDonnell & Miller para caldeiras a vapor de maior pressão de trabalho. Distribuidor autorizado. Orçamento no WhatsApp.',

    // H1
    'nombre' => 'McDonnell & Miller 150S Alta Pressão — Controle de Nível para Caldeiras a Vapor',
    // Párrafo bajo el H1.
    'resumo' => 'Versão do controle de nível 150S para caldeiras de maior pressão de trabalho — confirme a faixa no catálogo do fabricante. Corte por baixo nível de água e comando de bomba de alimentação, com peça original McDonnell & Miller.',

    'bullets' => [
        'Controle de nível tipo boia da série 150S para caldeiras de maior pressão de trabalho',
        'Corte por baixo nível de água: protege a caldeira contra queima a seco',
        'Comando de bomba de alimentação integrado no mesmo controle',
        'Fornecimento de canal autorizado, com rastreabilidade',
    ],

    // Tabla "O que é": pares [rótulo, valor]. Solo hechos seguros.
    'ficha' => [
        ['Tipo de produto', 'Controle de nível de água para caldeiras'],
        ['Família', 'McDonnell & Miller série 150S'],
        ['Aplicação', 'Caldeiras a vapor de maior pressão de trabalho — confirme a faixa no catálogo do fabricante'],
        ['Princípio', 'Boia (flutuador) acionando contatos'],
        ['Funções', 'Corte por baixo nível de água e controle de bomba de alimentação'],
        ['Fabricante', 'McDonnell & Miller (Xylem)'],
    ],

    // 4 a 6 aplicaciones: [título, texto]
    'aplicacoes' => [
        ['Caldeiras a vapor de maior pressão', 'Plantas cuja pressão de trabalho supera a faixa do 150S padrão.'],
        ['Indústria química e petroquímica', 'Processos que exigem vapor em pressões mais elevadas e operação contínua.'],
        ['Geração de energia e utilidades', 'Casas de caldeiras que fornecem vapor para turbinas e processos auxiliares.'],
        ['Papel e celulose', 'Caldeiras de processo com alta demanda de vapor.'],
        ['Manutenção preventiva', 'Troca programada do controle em paradas de manutenção da caldeira.'],
    ],

    // Slugs de OTRAS landings de la marca (archivos de esta carpeta), en orden.
    'relacionados' => ['150s-hd', '157s', 'sa150-11', 'sa150-106r', 'swa150s', 'flanges-2-polegadas'],

    // 5 FAQ: [pergunta, resposta]. Garantía SIN plazo numérico.
    'faq' => [
        ['Como faço para receber um orçamento?', 'Envie o número de peça, a quantidade e, se possível, uma foto da plaqueta do controle instalado pelo WhatsApp. Confirmamos a compatibilidade e devolvemos preço, prazo de entrega e condições de pagamento em até 1 dia útil.'],
        ['Qual é a pressão máxima de trabalho desta versão do 150S?', 'A faixa de pressão consta no catálogo do fabricante e varia conforme a configuração. Informe a pressão de trabalho da sua caldeira e o modelo da plaqueta para confirmarmos se esta versão é a adequada antes do envio.'],
        ['Qual é o prazo de entrega para o Brasil?', 'Para itens em estoque, o despacho ocorre em 1 a 2 dias úteis e o trânsito aéreo até os principais aeroportos brasileiros leva, em média, 5 a 10 dias úteis, mais o tempo de liberação aduaneira.'],
        ['Vocês emitem a documentação de importação?', 'Sim. O fornecimento é acompanhado de invoice comercial, packing list, certificado de origem quando aplicável e a classificação fiscal (NCM), tudo o que o seu despachante precisa para a nacionalização.'],
        ['Qual é a garantia?', 'Garantia de fábrica McDonnell & Miller contra defeitos de fabricação, com prazo definido pelo fabricante. Como canal autorizado, encaminhamos o processo diretamente ao fabricante.'],
    ],
];
