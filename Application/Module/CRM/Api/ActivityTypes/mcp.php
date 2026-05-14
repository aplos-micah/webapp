<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Filter by activity type name'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 100, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_activity_types',
        'description' => 'List all CRM activity types including their average costs. Returns both active and inactive types.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'activity_type',
    ],
    [
        'name'        => 'get_activity_type',
        'description' => 'Get a single CRM activity type by ID, including its name, description, average cost, and active status.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Activity type ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'activity_type',
        'label'   => 'Activity type',
    ],
    [
        'name'        => 'create_activity_type',
        'description' => 'Create a new CRM activity type with a name and average cost. Requires Manager or Admin access.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'name'         => ['type' => 'string', 'description' => 'Activity type name (required), e.g. Phone Call, Site Visit, Product Demo'],
                'description'  => ['type' => 'string', 'description' => 'Description of when or how this type is used'],
                'average_cost' => ['type' => 'number', 'description' => 'Average cost in USD to perform this activity type (default 0.00)'],
            ],
            'required' => ['name'],
        ],
        'handler'             => 'create',
        'service'             => 'activity_type',
        'requiresModuleTier'  => 'Manager',
    ],
    [
        'name'        => 'update_activity_type',
        'description' => 'Update an existing CRM activity type by ID. Use is_active: 0 to deactivate a type. Requires Manager or Admin access.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'           => ['type' => 'integer', 'description' => 'Activity type ID to update (required)'],
                'name'         => ['type' => 'string',  'description' => 'Activity type name'],
                'description'  => ['type' => 'string',  'description' => 'Description of when or how this type is used'],
                'average_cost' => ['type' => 'number',  'description' => 'Average cost in USD'],
                'is_active'    => ['type' => 'integer', 'description' => '1 = active, 0 = inactive (deactivated types cannot be selected when logging new activities)'],
            ],
            'required' => ['id'],
        ],
        'handler'            => 'update',
        'service'            => 'activity_type',
        'label'              => 'Activity type',
        'requiresModuleTier' => 'Manager',
    ],
];
