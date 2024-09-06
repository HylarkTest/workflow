<?php

declare(strict_types=1);

return [
    'tenant_column' => 'base_id',
    'distribution_table' => 'bases',
    'reference_tables' => [],
    // The order here matters!
    'distributed_tables' => [
        // Account
        'spaces',

        // Mappings
        'mappings',
        'items',
        'relationships',
        'pages',
        'personal_page_settings',

        // Customization
        'deadline_groups',
        'deadlines',
        'deadlinables',
        'marker_groups',
        'markers',
        'markables',
        'categories',
        'category_items',

        // Organization
        'assignee_groups',
        'assignables',
        'calendars',
        'events',
        'eventables',
        'drives',
        'documents',
        'images',
        'attachables',
        'link_lists',
        'links',
        'linkables',
        'notebooks',
        'notes',
        'notables',
        'pinboards',
        'pins',
        'pinables',
        'todo_lists',
        'todos',
        'todoables',
        'integration_accounts',
        'emailables',
        'email_addressables' => 'integration_accounts',
        'external_todoables',
        'external_eventables',

        // Other
        'actions',
        'comments',
    ],
];
