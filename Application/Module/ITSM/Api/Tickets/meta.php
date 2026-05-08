<?php

return [
    'description' => 'List or retrieve ITSM tickets (incidents, service requests, problems, changes).',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',       'type' => 'integer', 'description' => 'Return a single ticket by ID'],
        ['name' => 'search',   'type' => 'string',  'description' => 'Filter by title or ticket number'],
        ['name' => 'status',   'type' => 'string',  'description' => 'Filter by status: New, In Progress, Pending, Resolved, Closed'],
        ['name' => 'priority', 'type' => 'string',  'description' => 'Filter by priority: Low, Medium, High, Critical'],
        ['name' => 'type',     'type' => 'string',  'description' => 'Filter by type: Incident, Service Request, Problem, Change'],
        ['name' => 'limit',    'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset',   'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
