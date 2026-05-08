<?php

return [
    'description' => 'List or retrieve assets from the CMDB.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single asset by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by name, asset tag, or serial number'],
        ['name' => 'status', 'type' => 'string',  'description' => 'Filter by status: Active, In Stock, In Repair, Retired, Lost/Stolen'],
        ['name' => 'type',   'type' => 'string',  'description' => 'Filter by type: Hardware, Software, Network, Mobile, License, Other'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
