<?php

return [
    'description' => 'List or retrieve Knowledge Base articles.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',            'type' => 'integer', 'description' => 'Return a single article by ID'],
        ['name' => 'search',        'type' => 'string',  'description' => 'Filter by title, content, or tags'],
        ['name' => 'status',        'type' => 'string',  'description' => 'Filter by status: Draft, Published, Archived (default: Published)'],
        ['name' => 'category',      'type' => 'string',  'description' => 'Filter by category'],
        ['name' => 'include_drafts','type' => 'boolean', 'description' => 'Include Draft articles (default: false)'],
        ['name' => 'limit',         'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset',        'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
