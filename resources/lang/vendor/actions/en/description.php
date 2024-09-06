<?php

declare(strict_types=1);

use App\Core\Groups\Role;
use Actions\Core\ActionType;
use App\Core\TodoActionType;
use App\Core\MarkerActionType;
use App\Core\MemberActionType;
use App\Core\MappingActionType;
use App\Core\RelationshipActionType;
use App\Core\Mappings\FieldFilterOperator;
use App\Core\Mappings\MarkerFilterOperator;
use App\Core\Actions\ActionTypes\SavedFilterActionType;
use App\Core\MappingActionType as BaseMappingActionType;

return [
    ActionType::CREATE => ':SubjectType ":subject" created|:SubjectType ":subject" created by :performer',
    ActionType::UPDATE => ':SubjectType ":subject" updated|:SubjectType ":subject" updated by :performer',
    ActionType::DELETE => ':SubjectType ":subject" deleted|:SubjectType ":subject" deleted by :performer',
    ActionType::RESTORE => ':SubjectType ":subject" restored|:SubjectType ":subject" restored by :performer',

    SavedFilterActionType::SAVED_FILTER_CREATE => 'Filter saved on :SubjectType ":subject"|Filter saved on :SubjectType ":subject" by :performer',
    SavedFilterActionType::SAVED_FILTER_UPDATE => 'Filter updated on :SubjectType ":subject"|Filter updated on :SubjectType ":subject" by :performer',
    SavedFilterActionType::SAVED_FILTER_DELETE => 'Filter removed from :SubjectType ":subject"|Filter removed from :SubjectType ":subject" by :performer',
    SavedFilterActionType::PRIVATE_SAVED_FILTER_CREATE => 'Private filter saved on :SubjectType ":subject"|Private filter saved on :SubjectType ":subject" by :performer',
    SavedFilterActionType::PRIVATE_SAVED_FILTER_UPDATE => 'Private filter updated on :SubjectType ":subject"|Private filter updated on :SubjectType ":subject" by :performer',
    SavedFilterActionType::PRIVATE_SAVED_FILTER_DELETE => 'Private filter removed from :SubjectType ":subject"|Private filter removed from :SubjectType ":subject" by :performer',

    'subject' => [
        'Todo' => 'todo',
        'Page' => 'page',
        'Mapping' => 'blueprint',
        'TodoList' => 'todo list',
        'Event' => 'event',
        'Calendar' => 'calendar',
        'Note' => 'note',
        'Attachment' => 'attachment',
        'TagGroup' => 'tag group',
        'Tag' => 'tag',
        'Item' => 'item',
        'Category' => 'category',
        'CategoryItem' => 'category item',
        'LinkList' => 'link list',
        'MarkerGroup' => 'marker group',
    ],

    'field' => [
        'add' => 'Added the :field',
        'change' => 'Changed the :field',
        'remove' => 'Removed the :field',

        'order' => [
            'add' => 'Placed at the start|placed at the end',
            'change' => 'Changed the order',
        ],
    ],

    'change' => [
        'priority' => [
            'urgent' => 'Urgent',
            'high' => 'High',
            'normal' => 'Normal',
            'low' => 'Low',
        ],
    ],

    'attributes' => [
        'name' => 'name',
        'api_name' => 'API name',
        'description' => 'description',
        'singular_name' => 'singular name',
        'icon' => 'icon',
        'due_by' => 'due by date',
        'start_at' => 'start date',
        'end_at' => 'end date',
        'is_all_day' => 'all day',
        'mime_type' => 'mime type',
        'filename' => 'file name',
        'symbol' => 'icon',
    ],

    'user' => [
        ActionType::CREATE => 'Account created!',
    ],

    'note' => [
        'field' => [
            'notebook_id' => [
                'add' => 'Created on notebook',
                'change' => 'Moved to notebook',
            ],
        ],
    ],

    'todo' => [
        TodoActionType::COMPLETE => ':SubjectType ":subject" marked as complete|:SubjectType ":subject" marked as complete by :performer',
        TodoActionType::UNCOMPLETE => ':SubjectType ":subject" Marked as incomplete|:SubjectType ":subject" marked as incomplete by :performer',
        'field' => [
            'todo_list_id' => [
                'add' => 'Created on todo list',
                'change' => 'Moved to todo list',
            ],
        ],
        'change' => [
            'recurrence' => [
                'every' => [
                    'daily' => 'every day|every :interval days',
                    'weekly' => 'every week|every :interval weeks',
                    'monthly' => 'every month|every :interval months',
                    'yearly' => 'every year|every :interval years',
                ],
                'on' => 'on :days',
            ],
        ],
    ],

    'event' => [
        'field' => [
            'calendar_id' => [
                'add' => 'Created on calendar',
                'change' => 'Moved to calendar',
            ],
            'is_all_day' => [
                'add' => '{0}|{1} Made an all day event',
                'change' => '{0} Changed from an all day event to a specific time|{1} Changed from a specific time to an all day event',
            ],
        ],
    ],

    'document' => [
        ActionType::CREATE => ':SubjectType ":subject" uploaded|:SubjectType ":subject" uploaded by :performer',
        'field' => [
            'drive_id' => [
                'add' => 'Created on drive',
                'change' => 'Moved to drive',
            ],
        ],
    ],

    'pin' => [
        'field' => [
            'pinboard_id' => [
                'add' => 'Created on pinboard',
                'change' => 'Moved to pinboard',
            ],
            'document_id' => [
                'add' => 'Added the image',
                'change' => 'Changed the image',
            ],
        ],
    ],

    'link' => [
        'field' => [
            'link_list_id' => [
                'add' => 'Created on link list',
                'change' => 'Moved to link list',
            ],
        ],
    ],

    'marker_group' => [
        'field' => [
            'features' => [
                'add' => 'Made available to features',
                'change' => 'Made available to features',
            ],
        ],
    ],

    'image' => [
        ActionType::CREATE => ':SubjectType ":subject" uploaded|:SubjectType ":subject" uploaded by :performer',
    ],

    'page' => [
        'attributes' => [
            'path' => 'name',
            'default_view' => 'default view',
            'default_filter_id' => 'default filter',
            'personal_default_filter_id' => 'personal default filter',
        ],
        'field' => [
            'entityId' => [
                'add' => 'Associated entity',
                'change' => 'Changed the entity associated to the page',
            ],
            'view' => [
                'add' => 'Added a view',
                'change' => 'Changed a view',
            ],
            'newFields' => [
                'add' => 'Added the fields that appear in the create form',
                'change' => 'Changed the fields that appear in the create form',
            ],
            'newMarkers' => [
                'add' => 'Added the marker groups that appear in the create form',
                'change' => 'Changed the marker groups that appear in the create form',
            ],
            'lists' => [
                'add' => 'Added lists that will appear on this page',
                'change' => 'Changed the lists that will appear on this page',
            ],
            'markerFilters' => [
                'add' => 'Added marker filters to refine what appears on this page',
                'change' => 'Changed marker filters to refine what appears on this page',
            ],
            'fieldFilters' => [
                'add' => 'Added field filters to refine what appears on this page',
                'change' => 'Changed field filters to refine what appears on this page',
            ],
        ],
        'change' => [
            'markerFilters' => [
                MarkerFilterOperator::IS->value => 'must have marker ":name"',
                MarkerFilterOperator::IS_NOT->value => 'must not have marker ":name"',
            ],
            'fieldFilters' => [
                FieldFilterOperator::IS->value => 'field ":name" must have value ":value"',
                FieldFilterOperator::IS_NOT->value => 'field ":name" must not have value ":value"',
            ],
        ],
    ],

    'mapping' => [
        BaseMappingActionType::ADD_MAPPING_FIELD => 'Added ":payload.name" field to ":subject" page.|":payload.name" field added to ":subject" page by :performer.',
        BaseMappingActionType::CHANGE_MAPPING_FIELD => 'Changed ":payload.name" field on ":subject" page.|":payload.name" field changed on ":subject" page by :performer.',
        BaseMappingActionType::REMOVE_MAPPING_FIELD => 'Removed ":payload.name" field from ":subject" page.|":payload.name" field removed from ":subject" page by :performer.',

        BaseMappingActionType::ADD_MAPPING_FEATURE => 'Enabled the ":payload.name" feature on ":subject" page.|":payload.name" feature enabled on ":subject" page by :performer.',
        BaseMappingActionType::CHANGE_MAPPING_FEATURE => 'Customized the ":payload.name" feature on ":subject" page.|":payload.name" feature customized on ":subject" page by :performer.',
        BaseMappingActionType::REMOVE_MAPPING_FEATURE => 'Disabled the ":payload.name" feature from ":subject" page.|":payload.name" feature disabled on ":subject" page by :performer.',

        BaseMappingActionType::ADD_MAPPING_RELATIONSHIP => 'Added ":payload.name" relationship to ":subject" page.|Added ":payload.name" relationship to ":subject" page by :performer.',
        BaseMappingActionType::CHANGE_MAPPING_RELATIONSHIP => 'Changed ":payload.name" relationship on ":subject" page.|Changed ":payload.name" relationship on ":subject" page by :performer.',
        BaseMappingActionType::REMOVE_MAPPING_RELATIONSHIP => 'Removed ":payload.name" relationship from ":subject" page.|Removed ":payload.name" relationship from ":subject" page by :performer.',

        BaseMappingActionType::ADD_MAPPING_TAG_GROUP => 'Added ":payload.name" marker group to ":subject" page.|":payload.name" marker group added to ":subject" page by :performer.',
        BaseMappingActionType::CHANGE_MAPPING_TAG_GROUP => 'Changed ":payload.name" marker group on ":subject" page.|":payload.name" marker group added to ":subject" page by :performer.',
        BaseMappingActionType::REMOVE_MAPPING_TAG_GROUP => 'Removed ":payload.name" marker group from ":subject" page.|":payload.name" marker group added to ":subject" page by :performer.',

        MappingActionType::CHANGE_MAPPING_DESIGN => 'Changed the interface appearance on ":subject" page.|Interface appearance changed on ":subject" by :performer.',

        'field' => [
            BaseMappingActionType::ADD_MAPPING_RELATIONSHIP => [
                'to' => [
                    'add' => 'Set related blueprint',
                ],
            ],
        ],
    ],

    'item' => [
        'field' => [
            'favorited_at' => '{0} Unfavorited|{1} Favorited',
        ],

        RelationshipActionType::RELATIONSHIP_ADDED => 'Added ":payload.related.name" to relationship ":payload.relationship.name" on ":subject".|":payload.related.name" added to relationship ":payload.relationship.name" on ":subject" by :performer.',
        RelationshipActionType::RELATIONSHIP_REMOVED => 'Removed ":payload.related.name" from relationship ":payload.relationship.name" on ":subject".|":payload.related.name" removed from relationship ":payload.relationship.name" on ":subject" by :performer.',

        'salary' => [
            'period' => [
                'HOURLY' => 'per hour',
                'DAILY' => 'per day',
                'WEEKLY' => 'per week',
                'MONTHLY' => 'per month',
                'YEARLY' => 'per year',
                'ONE_TIME' => 'one time',
            ],
        ],
    ],

    'base_user_pivot' => [
        ActionType::UPDATE => 'Personal base settings updated',
        'field' => [
            'settings' => 'Base customizations changed',
            'use_account_avatar' => '{0} Changed to use account avatar|{1} Changed to use custom avatar',
        ],
        'payload' => [
            'role' => [
                Role::OWNER->value => 'Owner',
                Role::ADMIN->value => 'Admin',
                Role::MEMBER->value => 'Member',
            ],
        ],
    ],

    MarkerActionType::MARKER_ADDED => 'Added marker ":payload.marker.name" to ":subject".|":payload.marker.name" added to ":subject" by :performer.',
    MarkerActionType::MARKER_REMOVED => 'Removed marker ":payload.marker.name" from ":subject".|":payload.marker.name" removed from ":subject" by :performer.',

    MemberActionType::MEMBER_INVITED => 'Invitation to join the base was sent to ":payload.email"|:performer sent an invite to ":payload.email" to join the base.',
    MemberActionType::MEMBER_INVITE_RESENT => 'Invitation resent to ":payload.email"|:performer resent an invite to ":payload.email".',
    MemberActionType::MEMBER_INVITE_ACCEPTED => 'An invitation to join the base was accepted.|:performer accepted the invitation to join the base.',
    MemberActionType::MEMBER_REMOVED => 'Removed ":subject" from the base.|":subject" removed from the base by :performer.',
    MemberActionType::MEMBER_ROLE_UPDATED => 'Changed the role of ":subject".|The role of ":subject" changed by :performer.',
];
