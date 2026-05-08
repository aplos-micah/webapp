<?php

return [
    'description' => 'List or retrieve projects.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',       'type' => 'integer', 'description' => 'Return a single project by ID'],
        ['name' => 'search',   'type' => 'string',  'description' => 'Filter by name or description'],
        ['name' => 'status',   'type' => 'string',  'description' => 'Filter by status: Draft, Active, On Hold, Completed, Cancelled'],
        ['name' => 'phase',    'type' => 'string',  'description' => 'Filter by phase: Initiation, Planning, Execution, Monitoring, Closure'],
        ['name' => 'priority', 'type' => 'string',  'description' => 'Filter by priority: Low, Medium, High, Critical'],
        ['name' => 'limit',    'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset',   'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
