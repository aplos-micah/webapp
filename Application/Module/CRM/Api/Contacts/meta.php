<?php

return [
    'description' => 'List or retrieve CRM contacts.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single contact by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by first name, last name, email, job title, or account name'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
