<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Text search filter (name, account number, type, industry, status, website)'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_accounts',
        'description' => 'Search and list CRM accounts. Filterable by name, account number, type, industry, status, or website.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'account',
    ],
    [
        'name'        => 'get_account',
        'description' => 'Get a single CRM account by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Account ID']],
            'required'   => ['id'],
        ],
        'handler'     => 'get',
        'service'     => 'account',
        'label'       => 'Account',
    ],
];
