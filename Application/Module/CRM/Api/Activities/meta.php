<?php

return [
    'description' => 'List, retrieve, create, or update CRM activities.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',             'type' => 'integer', 'description' => 'Return a single activity by ID'],
        ['name' => 'account_id',     'type' => 'integer', 'description' => 'Filter activities by account ID'],
        ['name' => 'contact_id',     'type' => 'integer', 'description' => 'Filter activities by contact ID'],
        ['name' => 'opportunity_id', 'type' => 'integer', 'description' => 'Filter activities by opportunity ID'],
        ['name' => 'search',         'type' => 'string',  'description' => 'Text search filter (type name, notes, outcome)'],
        ['name' => 'limit',          'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset',         'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
