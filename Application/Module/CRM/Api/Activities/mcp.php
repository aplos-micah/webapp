<?php

$pagination = [
    'search'         => ['type' => 'string',  'description' => 'Text search filter (activity type name, notes, outcome)'],
    'account_id'     => ['type' => 'integer', 'description' => 'Filter by account ID — returns only activities linked to this account'],
    'contact_id'     => ['type' => 'integer', 'description' => 'Filter by contact ID — returns only activities linked to this contact'],
    'opportunity_id' => ['type' => 'integer', 'description' => 'Filter by opportunity ID — returns only activities linked to this opportunity'],
    'limit'          => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset'         => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_activities',
        'description' => 'List CRM activities. Filter by account, contact, or opportunity ID to see activities for a specific record. Supports text search and pagination.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list_filtered',
        'service'     => 'activity',
    ],
    [
        'name'        => 'get_activity',
        'description' => 'Get a single CRM activity by ID, including type, cost, outcome, linked entities, and owner.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Activity ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'activity',
        'label'   => 'Activity',
    ],
    [
        'name'        => 'create_activity',
        'description' => 'Log a new CRM activity. At least one of account_id, contact_id, or opportunity_id is required.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'activity_type_id' => ['type' => 'integer', 'description' => 'Activity type ID (required) — use list_activity_types to find available types'],
                'activity_date'    => ['type' => 'string',  'description' => 'Date of the activity (YYYY-MM-DD, required)'],
                'account_id'       => ['type' => 'integer', 'description' => 'Linked account ID'],
                'contact_id'       => ['type' => 'integer', 'description' => 'Linked contact ID'],
                'opportunity_id'   => ['type' => 'integer', 'description' => 'Linked opportunity ID'],
                'cost'             => ['type' => 'number',  'description' => 'Actual cost of the activity in USD (defaults to activity type average cost if omitted)'],
                'duration_minutes' => ['type' => 'integer', 'description' => 'Duration of the activity in minutes'],
                'outcome'          => ['type' => 'string',  'description' => 'Outcome: Positive, Neutral, Negative, Completed, No Response, Follow-up Required, or Cancelled'],
                'notes'            => ['type' => 'string',  'description' => 'Notes or description of what happened'],
            ],
            'required' => ['activity_type_id', 'activity_date'],
        ],
        'handler'   => 'create',
        'service'   => 'activity',
        'follow_up' => 'The activity was logged successfully. Ask the user if they would like to log another activity or view the related account, contact, or opportunity.',
    ],
    [
        'name'        => 'update_activity',
        'description' => 'Update an existing CRM activity by ID. Only the fields provided will be changed.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'               => ['type' => 'integer', 'description' => 'Activity ID to update (required)'],
                'activity_type_id' => ['type' => 'integer', 'description' => 'Activity type ID'],
                'activity_date'    => ['type' => 'string',  'description' => 'Date of the activity (YYYY-MM-DD)'],
                'account_id'       => ['type' => 'integer', 'description' => 'Linked account ID'],
                'contact_id'       => ['type' => 'integer', 'description' => 'Linked contact ID'],
                'opportunity_id'   => ['type' => 'integer', 'description' => 'Linked opportunity ID'],
                'cost'             => ['type' => 'number',  'description' => 'Actual cost in USD'],
                'duration_minutes' => ['type' => 'integer', 'description' => 'Duration in minutes'],
                'outcome'          => ['type' => 'string',  'description' => 'Outcome: Positive, Neutral, Negative, Completed, No Response, Follow-up Required, or Cancelled'],
                'notes'            => ['type' => 'string',  'description' => 'Notes or description'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'activity',
        'label'   => 'Activity',
    ],
];
