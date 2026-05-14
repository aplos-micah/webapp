<?php

return [
    'description' => 'List, retrieve, create, or update CRM activity types.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single activity type by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by name'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 100, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
