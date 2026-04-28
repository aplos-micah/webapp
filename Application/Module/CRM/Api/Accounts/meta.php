<?php

return [
    'description' => 'List or retrieve CRM accounts.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single account by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by name, account number, type, industry, status, or website'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
