<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Text search filter (product name, SKU, family, type, lifecycle status)'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_products',
        'description' => 'Search and list product definitions. Filterable by product name, SKU, product family, type, or lifecycle status.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'product',
    ],
    [
        'name'        => 'get_product',
        'description' => 'Get a single product definition by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Product ID']],
            'required'   => ['id'],
        ],
        'handler'     => 'get',
        'service'     => 'product',
        'label'       => 'Product',
    ],
];
