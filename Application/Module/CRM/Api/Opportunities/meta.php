<?php

return [
    'description' => 'List or retrieve CRM opportunities. Single-record responses include line items.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single opportunity by ID (includes line items)'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by opportunity name, stage, forecast category, or account name'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
