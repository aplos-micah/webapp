<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Filter by name, asset tag, or serial number'],
    'status' => ['type' => 'string',  'description' => 'Filter by status: Active, In Stock, In Repair, Retired, Lost/Stolen'],
    'type'   => ['type' => 'string',  'description' => 'Filter by type: Hardware, Software, Network, Mobile, License, Other'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_assets',
        'description' => 'Search and list assets from the CMDB. Filterable by status or type.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'asset',
    ],
    [
        'name'        => 'get_asset',
        'description' => 'Get a single asset by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Asset ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'asset',
        'label'   => 'Asset',
    ],
    [
        'name'        => 'create_asset',
        'description' => 'Create a new asset record. An ASSET-XXXXXX tag is automatically assigned on creation.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'name'             => ['type' => 'string',  'description' => 'Asset name (required)'],
                'type'             => ['type' => 'string',  'description' => 'Hardware | Software | Network | Mobile | License | Other (default: Hardware)'],
                'category'         => ['type' => 'string',  'description' => 'Category within the type (e.g. Laptop, Server)'],
                'status'           => ['type' => 'string',  'description' => 'Active | In Stock | In Repair | Retired | Lost/Stolen (default: Active)'],
                'manufacturer'     => ['type' => 'string',  'description' => 'Manufacturer name'],
                'model'            => ['type' => 'string',  'description' => 'Model name or number'],
                'serial_number'    => ['type' => 'string',  'description' => 'Serial number'],
                'location'         => ['type' => 'string',  'description' => 'Physical location (e.g. Building A, Desk 12)'],
                'assigned_to'      => ['type' => 'integer', 'description' => 'User ID the asset is assigned to'],
                'purchase_date'    => ['type' => 'string',  'description' => 'Purchase date (YYYY-MM-DD)'],
                'warranty_expires' => ['type' => 'string',  'description' => 'Warranty expiry date (YYYY-MM-DD)'],
                'cost'             => ['type' => 'number',  'description' => 'Asset cost'],
                'notes'            => ['type' => 'string',  'description' => 'Additional notes'],
            ],
            'required' => ['name'],
        ],
        'handler'   => 'create',
        'service'   => 'asset',
        'follow_up' => 'The asset was created successfully. Ask if the user wants to assign it to a user or record the purchase date and warranty expiry.',
    ],
    [
        'name'        => 'update_asset',
        'description' => 'Update an existing asset by ID. Only fields provided will be changed.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'               => ['type' => 'integer', 'description' => 'Asset ID to update (required)'],
                'name'             => ['type' => 'string',  'description' => 'Asset name'],
                'type'             => ['type' => 'string',  'description' => 'Hardware | Software | Network | Mobile | License | Other'],
                'category'         => ['type' => 'string',  'description' => 'Category value'],
                'status'           => ['type' => 'string',  'description' => 'Active | In Stock | In Repair | Retired | Lost/Stolen'],
                'manufacturer'     => ['type' => 'string',  'description' => 'Manufacturer'],
                'model'            => ['type' => 'string',  'description' => 'Model'],
                'serial_number'    => ['type' => 'string',  'description' => 'Serial number'],
                'location'         => ['type' => 'string',  'description' => 'Location'],
                'assigned_to'      => ['type' => 'integer', 'description' => 'User ID of new assignee'],
                'purchase_date'    => ['type' => 'string',  'description' => 'Purchase date (YYYY-MM-DD)'],
                'warranty_expires' => ['type' => 'string',  'description' => 'Warranty expiry date (YYYY-MM-DD)'],
                'cost'             => ['type' => 'number',  'description' => 'Asset cost'],
                'notes'            => ['type' => 'string',  'description' => 'Notes'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'asset',
        'label'   => 'Asset',
    ],
];
