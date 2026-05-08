<?php

$pagination = [
    'search'        => ['type' => 'string',  'description' => 'Filter by title, content, or tags'],
    'category'      => ['type' => 'string',  'description' => 'Filter by category: Procedure, Troubleshooting, FAQ, Policy, Reference, Other'],
    'include_drafts'=> ['type' => 'boolean', 'description' => 'Set to true to include Draft articles (default: false — Published only)'],
    'limit'         => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset'        => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_articles',
        'description' => 'Search and list Knowledge Base articles. Returns Published articles by default. Use include_drafts to see drafts.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => 'article',
    ],
    [
        'name'        => 'get_article',
        'description' => 'Get a single Knowledge Base article by its numeric ID, including full content.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => 'Article ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => 'article',
        'label'   => 'Article',
    ],
    [
        'name'        => 'create_article',
        'description' => 'Create a new Knowledge Base article. New articles default to Draft status.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'title'    => ['type' => 'string', 'description' => 'Article title (required)'],
                'category' => ['type' => 'string', 'description' => 'Procedure | Troubleshooting | FAQ | Policy | Reference | Other'],
                'status'   => ['type' => 'string', 'description' => 'Draft | Published | Archived (default: Draft)'],
                'tags'     => ['type' => 'string', 'description' => 'Comma-separated tags for search (e.g. vpn, password reset)'],
                'content'  => ['type' => 'string', 'description' => 'Full article body text'],
            ],
            'required' => ['title'],
        ],
        'handler'   => 'create',
        'service'   => 'article',
        'follow_up' => 'The article was created as a Draft. Ask if the user wants to publish the article now or save it as a draft for review.',
    ],
    [
        'name'        => 'update_article',
        'description' => 'Update an existing Knowledge Base article by ID. Change status to Published to make it visible to all users.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'       => ['type' => 'integer', 'description' => 'Article ID to update (required)'],
                'title'    => ['type' => 'string',  'description' => 'Updated title'],
                'category' => ['type' => 'string',  'description' => 'Procedure | Troubleshooting | FAQ | Policy | Reference | Other'],
                'status'   => ['type' => 'string',  'description' => 'Draft | Published | Archived'],
                'tags'     => ['type' => 'string',  'description' => 'Comma-separated tags'],
                'content'  => ['type' => 'string',  'description' => 'Updated article body text'],
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => 'article',
        'label'   => 'Article',
    ],
];
