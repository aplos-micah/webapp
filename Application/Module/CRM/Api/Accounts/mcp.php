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
    [
        'name'        => 'create_account',
        'description' => 'Create a new CRM account. Returns the new account ID on success.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'name'           => ['type' => 'string',  'description' => 'Account name (required)'],
                'account_number' => ['type' => 'string',  'description' => 'Account number'],
                'type'           => ['type' => 'string',  'description' => 'Account type (e.g. Customer, Prospect, Partner)'],
                'industry'       => ['type' => 'string',  'description' => 'Industry'],
                'status'         => ['type' => 'string',  'description' => 'Status (e.g. Active, Onboarding, Churned)'],
                'website'        => ['type' => 'string',  'description' => 'Website URL'],
                'annual_revenue' => ['type' => 'number',  'description' => 'Annual revenue'],
                'employee_count' => ['type' => 'integer', 'description' => 'Number of employees'],
                'ownership'      => ['type' => 'string',  'description' => 'Ownership type (e.g. Public, Private)'],
                'description'    => ['type' => 'string',  'description' => 'Account description or notes'],
            ],
            'required' => ['name'],
        ],
        'handler' => 'create',
        'service' => 'account',
    ],
];
