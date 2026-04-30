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
];
