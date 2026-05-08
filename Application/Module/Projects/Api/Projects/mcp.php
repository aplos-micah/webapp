<?php

$pagination = [
    'search'   => ['type' => 'string',  'description' => 'Filter by name or description'],
    'status'   => ['type' => 'string',  'description' => 'Filter by status: Draft, Active, On Hold, Completed, Cancelled'],
    'phase'    => ['type' => 'string',  'description' => 'Filter by phase: Initiation, Planning, Execution, Monitoring, Closure'],
    'priority' => ['type' => 'string',  'description' => 'Filter by priority: Low, Medium, High, Critical'],
    'limit'    => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset'   => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_projects',
        'description' => 'Search and list projects. Filterable by status, phase, or priority.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'project',
    ],
    [
        'name'        => 'get_project',
        'description' => 'Get a single project by its numeric ID, including all fields.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Project ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'project',
        'label'   => 'Project',
    ],
    [
        'name'        => 'create_project',
        'description' => 'Create a new project. New projects default to Draft status and Initiation phase.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'name'        => ['type' => 'string',  'description' => 'Project name (required)'],
                'description' => ['type' => 'string',  'description' => 'Project description'],
                'status'      => ['type' => 'string',  'description' => 'Draft | Active | On Hold | Completed | Cancelled (default: Draft)'],
                'phase'       => ['type' => 'string',  'description' => 'Initiation | Planning | Execution | Monitoring | Closure (default: Initiation)'],
                'priority'    => ['type' => 'string',  'description' => 'Low | Medium | High | Critical (default: Medium)'],
                'owner_id'    => ['type' => 'integer', 'description' => 'User ID of the project owner'],
                'start_date'  => ['type' => 'string',  'description' => 'Start date (YYYY-MM-DD)'],
                'due_date'    => ['type' => 'string',  'description' => 'Due date (YYYY-MM-DD)'],
                'budget'      => ['type' => 'number',  'description' => 'Project budget'],
                'notes'       => ['type' => 'string',  'description' => 'Additional notes'],
            ],
            'required' => ['name'],
        ],
        'handler'   => 'create',
        'service'   => 'project',
        'follow_up' => 'The project was created as a Draft. Ask if the user wants to set an owner, due date, or advance the status to Active.',
    ],
    [
        'name'        => 'update_project',
        'description' => 'Update an existing project by ID. Use status and phase fields to advance the project through its lifecycle. Setting status to Completed automatically records the completed date.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'          => ['type' => 'integer', 'description' => 'Project ID to update (required)'],
                'name'        => ['type' => 'string',  'description' => 'Project name'],
                'description' => ['type' => 'string',  'description' => 'Project description'],
                'status'      => ['type' => 'string',  'description' => 'Draft | Active | On Hold | Completed | Cancelled'],
                'phase'       => ['type' => 'string',  'description' => 'Initiation | Planning | Execution | Monitoring | Closure'],
                'priority'    => ['type' => 'string',  'description' => 'Low | Medium | High | Critical'],
                'owner_id'    => ['type' => 'integer', 'description' => 'User ID of the project owner'],
                'start_date'  => ['type' => 'string',  'description' => 'Start date (YYYY-MM-DD)'],
                'due_date'    => ['type' => 'string',  'description' => 'Due date (YYYY-MM-DD)'],
                'budget'      => ['type' => 'number',  'description' => 'Project budget'],
                'notes'       => ['type' => 'string',  'description' => 'Notes'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'project',
        'label'   => 'Project',
    ],
];
