<?php

return [
    'description' => 'List or retrieve product definitions.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single product by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by product name, SKU, family, type, or lifecycle status'],
        ['name' => 'active', 'type' => 'boolean', 'description' => 'Restrict to active products only (pass 1)'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
