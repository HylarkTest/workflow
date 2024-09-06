<?php

declare(strict_types=1);

use App\Core\Groups\Role;
use App\Core\Mappings\Features\MappingFeatureType;

return [
    'features' => [
        MappingFeatureType::DOCUMENTS->value => 'Documents',
        MappingFeatureType::EVENTS->value => 'Events',
        MappingFeatureType::CALENDAR->value => 'Calendar',
        MappingFeatureType::PINBOARD->value => 'Pinboard',
        MappingFeatureType::LINKS->value => 'Links',
        MappingFeatureType::COLLABORATION->value => 'Collaboration',
        MappingFeatureType::COMMENTS->value => 'Comments',
        MappingFeatureType::GOALS->value => 'Goals',
        MappingFeatureType::HEALTH->value => 'Health',
        MappingFeatureType::NOTES->value => 'Notes',
        MappingFeatureType::PLANNER->value => 'Planner',
        MappingFeatureType::PRIORITIES->value => 'Priorities',
        MappingFeatureType::STATISTICS->value => 'Statistics',
        MappingFeatureType::TODOS->value => 'Todos',
        MappingFeatureType::TIMEKEEPER->value => 'Timekeeper',
        MappingFeatureType::EMAILS->value => 'Emails',
        MappingFeatureType::FAVORITES->value => 'Favorites',
    ],
    'roles' => [
        Role::MEMBER->value => 'Member',
        Role::ADMIN->value => 'Admin',
        Role::OWNER->value => 'Owner',
    ],
];
