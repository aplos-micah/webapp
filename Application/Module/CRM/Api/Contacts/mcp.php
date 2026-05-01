<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Text search filter (first name, last name, email, job title, account name)'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_contacts',
        'description' => 'Search and list CRM contacts. Filterable by first name, last name, email, job title, or account name.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'contact',
    ],
    [
        'name'        => 'get_contact',
        'description' => 'Get a single CRM contact by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Contact ID']],
            'required'   => ['id'],
        ],
        'handler'     => 'get',
        'service'     => 'contact',
        'label'       => 'Contact',
    ],
    [
        'name'        => 'create_contact',
        'description' => 'Create a new CRM contact. Returns the new contact ID on success.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'first_name'               => ['type' => 'string',  'description' => 'First name (required)'],
                'last_name'                => ['type' => 'string',  'description' => 'Last name (required)'],
                'job_title'                => ['type' => 'string',  'description' => 'Job title'],
                'email'                    => ['type' => 'string',  'description' => 'Email address'],
                'work_phone'               => ['type' => 'string',  'description' => 'Work phone number'],
                'mobile_phone'             => ['type' => 'string',  'description' => 'Mobile phone number'],
                'account_id'               => ['type' => 'integer', 'description' => 'ID of the associated account'],
                'lead_source'              => ['type' => 'string',  'description' => 'Lead source'],
                'lifecycle_stage'          => ['type' => 'string',  'description' => 'Lifecycle stage'],
                'status'                   => ['type' => 'string',  'description' => 'Contact status'],
                'industry'                 => ['type' => 'string',  'description' => 'Industry'],
                'buying_role'              => ['type' => 'string',  'description' => 'Buying role'],
                'communication_preference' => ['type' => 'string',  'description' => 'Preferred communication channel'],
                'mailing_address'          => ['type' => 'string',  'description' => 'Mailing address'],
                'linkedin_url'             => ['type' => 'string',  'description' => 'LinkedIn profile URL'],
                'description'              => ['type' => 'string',  'description' => 'Notes or description'],
            ],
            'required' => ['first_name', 'last_name'],
        ],
        'handler' => 'create',
        'service' => 'contact',
    ],
];
