<?php

$pagination = [
    'search'   => ['type' => 'string',  'description' => 'Filter by title or ticket number'],
    'status'   => ['type' => 'string',  'description' => 'Filter by status: New, In Progress, Pending, Resolved, Closed'],
    'priority' => ['type' => 'string',  'description' => 'Filter by priority: Low, Medium, High, Critical'],
    'type'     => ['type' => 'string',  'description' => 'Filter by type: Incident, Service Request, Problem, Change'],
    'limit'    => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset'   => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_tickets',
        'description' => 'Search and list ITSM tickets. Filterable by status, priority, type, or free-text search.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'ticket',
    ],
    [
        'name'        => 'get_ticket',
        'description' => 'Get a single ITSM ticket by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Ticket ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'ticket',
        'label'   => 'Ticket',
    ],
    [
        'name'        => 'create_ticket',
        'description' => 'Create a new ITSM ticket. Returns the new ticket ID and auto-generated ticket number.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'title'             => ['type' => 'string',  'description' => 'Brief description of the issue (required)'],
                'description'       => ['type' => 'string',  'description' => 'Detailed description, steps to reproduce, or impact'],
                'type'              => ['type' => 'string',  'description' => 'Incident | Service Request | Problem | Change (default: Incident)'],
                'priority'          => ['type' => 'string',  'description' => 'Low | Medium | High | Critical (default: Medium)'],
                'category'          => ['type' => 'string',  'description' => 'Hardware | Software | Network | Access | Email | Security | Other'],
                'assigned_to'       => ['type' => 'integer', 'description' => 'User ID of the assignee'],
                'reported_by_name'  => ['type' => 'string',  'description' => 'Name of the person who reported the issue'],
                'reported_by_email' => ['type' => 'string',  'description' => 'Email of the reporter'],
            ],
            'required' => ['title'],
        ],
        'handler'   => 'create',
        'service'   => 'ticket',
        'follow_up' => 'The ticket was created successfully. Ask if the user would like to assign it to a team member or set a priority.',
    ],
    [
        'name'        => 'update_ticket',
        'description' => 'Update an existing ITSM ticket by ID. Only fields provided will be changed. Use status transitions to move the ticket through its workflow (New → In Progress → Pending → Resolved → Closed).',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'                => ['type' => 'integer', 'description' => 'Ticket ID to update (required)'],
                'title'             => ['type' => 'string',  'description' => 'Updated title'],
                'description'       => ['type' => 'string',  'description' => 'Updated description'],
                'type'              => ['type' => 'string',  'description' => 'Incident | Service Request | Problem | Change'],
                'priority'          => ['type' => 'string',  'description' => 'Low | Medium | High | Critical'],
                'status'            => ['type' => 'string',  'description' => 'New | In Progress | Pending | Resolved | Closed'],
                'category'          => ['type' => 'string',  'description' => 'Category value'],
                'assigned_to'       => ['type' => 'integer', 'description' => 'User ID of the new assignee'],
                'resolution'        => ['type' => 'string',  'description' => 'Resolution notes (required when resolving)'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'ticket',
        'label'   => 'Ticket',
    ],
];
