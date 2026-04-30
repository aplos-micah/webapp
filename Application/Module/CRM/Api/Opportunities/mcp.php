<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Text search filter (opportunity name, stage, forecast category, account name)'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_opportunities',
        'description' => 'Search and list CRM opportunities. Filterable by opportunity name, stage, forecast category, or account name.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'opportunity',
    ],
    [
        'name'        => 'get_opportunity',
        'description' => 'Get a single CRM opportunity by ID, including its product line items.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Opportunity ID']],
            'required'   => ['id'],
        ],
        'handler'     => 'get_with_items',
        'service'     => 'opportunity',
        'rel_service' => 'line_item',
        'rel_method'  => 'findByOpportunity',
        'rel_key'     => 'line_items',
        'label'       => 'Opportunity',
    ],
];
