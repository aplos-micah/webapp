<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Text search filter (opportunity name, stage, forecast category, account name)'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_opportunities',
        'description' => 'Search and list CRM opportunities. Filterable by opportunity name, stage, forecast category, or account name.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'opportunity',
    ],
    [
        'name'        => 'get_opportunity',
        'description' => 'Get a single CRM opportunity by ID, including its product line items.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Opportunity ID']],
            'required'   => ['id'],
        ],
        'handler'     => 'get_with_items',
        'service'     => 'opportunity',
        'rel_service' => 'line_item',
        'rel_method'  => 'findByOpportunity',
        'rel_key'     => 'line_items',
        'label'       => 'Opportunity',
    ],
    [
        'name'        => 'create_opportunity',
        'description' => 'Create a new CRM opportunity. Returns the new opportunity ID on success.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'opportunity_name'  => ['type' => 'string',  'description' => 'Opportunity name (required)'],
                'stage'             => ['type' => 'string',  'description' => 'Pipeline stage (New, Building, Review, Quote, Negotiating, Closed Won, Closed Lost)'],
                'amount'            => ['type' => 'number',  'description' => 'Opportunity value in USD'],
                'close_date'        => ['type' => 'string',  'description' => 'Expected close date (YYYY-MM-DD)'],
                'account_id'        => ['type' => 'integer', 'description' => 'ID of the associated account'],
                'contact_id'        => ['type' => 'integer', 'description' => 'ID of the associated contact'],
                'probability'       => ['type' => 'integer', 'description' => 'Win probability 0–100'],
                'forecast_category' => ['type' => 'string',  'description' => 'Forecast category (Omitted, Pipeline, Best Case, Commit, Closed)'],
                'opportunity_type'  => ['type' => 'string',  'description' => 'Opportunity type'],
                'lead_source'       => ['type' => 'string',  'description' => 'Lead source'],
                'description'       => ['type' => 'string',  'description' => 'Notes or description'],
            ],
            'required' => ['opportunity_name'],
        ],
        'handler'   => 'create',
        'service'   => 'opportunity',
        'follow_up' => 'The opportunity was created successfully. At least one product must be added to this opportunity. Ask the user which product(s) they would like to add, then use the available tools to look up the product and add it as a line item.',
    ],
    [
        'name'        => 'update_opportunity',
        'description' => 'Update an existing CRM opportunity by ID. Only the fields provided will be changed.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'                => ['type' => 'integer', 'description' => 'Opportunity ID to update (required)'],
                'opportunity_name'  => ['type' => 'string',  'description' => 'Opportunity name'],
                'stage'             => ['type' => 'string',  'description' => 'Pipeline stage (New, Building, Review, Quote, Negotiating, Closed Won, Closed Lost)'],
                'amount'            => ['type' => 'number',  'description' => 'Opportunity value in USD'],
                'close_date'        => ['type' => 'string',  'description' => 'Expected close date (YYYY-MM-DD)'],
                'account_id'        => ['type' => 'integer', 'description' => 'ID of the associated account'],
                'contact_id'        => ['type' => 'integer', 'description' => 'ID of the associated contact'],
                'probability'       => ['type' => 'integer', 'description' => 'Win probability 0–100'],
                'forecast_category' => ['type' => 'string',  'description' => 'Forecast category'],
                'opportunity_type'  => ['type' => 'string',  'description' => 'Opportunity type'],
                'lead_source'       => ['type' => 'string',  'description' => 'Lead source'],
                'loss_reason'       => ['type' => 'string',  'description' => 'Loss reason (if Closed Lost)'],
                'description'       => ['type' => 'string',  'description' => 'Notes or description'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'opportunity',
        'label'   => 'Opportunity',
    ],
];
