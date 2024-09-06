<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal :value.',
        'string' => 'The :attribute must be less than or equal :value characters.',
    ],
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'file_MB' => 'The :attribute must not be greater than :max megabytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must have at least :min characters.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required.',
    'required_unless' => 'The :attribute field is required.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
     |--------------------------------------------------------------------------
     | Application Specific Validation Language Lines
     |--------------------------------------------------------------------------
     */

    'name_field_required' => 'At least one field of type SYSTEM_NAME is required',
    'password_rules' => 'The :attribute must be at least :length characters and contain at least one uppercase character, and one number or one special character.',
    'auth' => 'The password is incorrect',
    'includeRecurringInstances' => 'A date range must be specified when querying recurring instances.',

    'delta' => 'The :attribute must have a valid delta format',
    'delta_max' => 'Notes can have a maximum of 10 000 characters. Please shorten your note.',

    'lists' => 'The lists field must be of the specified type.',

    'exceeded' => 'You have reached the limit for this account.',

    'time' => 'The :attribute must be a valid time.',

    'invalid' => 'The provided :attribute was invalid.',

    'rate_limit' => '{1} You may only request :requests :attribute per :period.|[2,*] You may only request :requests :attributes per :period.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'invite' => [
            'email' => [
                'member' => 'A user with the email address :emails is already a member of this base.|The emails :emails are already associated with members of this base.',
                'invited' => 'An invitation has already been sent to the address ":emails" within the last 24 hours.|Invitations have already been sent to the addresses :emails within the last 24 hours.',
            ],
        ],
        'document' => [
            'file' => [
                'required' => 'Upload a file',
            ],
        ],
        'pinboard' => [
            'name' => [
                'required' => ($_ = 'Pinboards must have a name.'),
                'filled' => $_,
                'unique' => 'You already have a pinboard with this name.',
            ],
        ],
        'todo' => [
            'name' => [
                'filled' => 'What is your to-do?',
            ],
        ],
        'event' => [
            'name' => [
                'filled' => 'What is your event called?',
            ],
            'timezone' => [
                'required' => 'Please select a timezone.',
            ],
        ],
        'note' => [
            'name' => [
                'filled' => 'Add a title to your note!',
            ],
        ],
        'pin' => [
            'name' => [
                'filled' => 'Add a name to your pin.',
            ],
            'image' => [
                'required' => 'Upload an image to save this pin.',
            ],
        ],
        'link' => [
            'name' => [
                'filled' => 'Add a title to your link',
            ],
            'url' => [
                'url' => 'Please add a valid URL.',
                'filled' => 'Please add a valid URL.',
                'required' => 'Please add a valid URL.',
                'max' => 'The URL must not be greater than 1000 characters.',
            ],
        ],
        'calendar' => [
            'name' => [
                'unique' => 'A calendar with that name already exists.',
            ],
        ],
        'todoList' => [
            'name' => [
                'unique' => 'You already have a to-do list with this name.',
            ],
        ],
        'linkList' => [
            'name' => [
                'unique' => 'You already have a links collection with this name.',
            ],
        ],
        'notebook' => [
            'name' => [
                'unique' => 'You already have a notebook with this name.',
            ],
        ],
        'drive' => [
            'name' => [
                'unique' => 'You already have a drive with this name.',
            ],
        ],
        'relationships' => [
            'ids' => [
                'unique' => 'That item has already been added as a relationship',
            ],
        ],
        'page' => [
            'id' => [
                'subset' => 'Other pages filter the full data found on this page. If you wish to delete this page, please delete the subset pages first.',
                'no_other_full_pages' => 'There are no other pages showing all records for this blueprint. Please create one before changing this page to a subset page.',
            ],
        ],

        'mapping_marker_group' => [
            'used' => 'Cannot remove marker group ":name" as its markers are used as filters on the page :pages.|Cannot remove marker group ":name" as its markers are used as filters on the pages :pages.',
        ],

        'relationship' => [
            'input.to' => [
                'required' => 'Please select a blueprint for the relationship.',
            ],
            'input.name' => [
                'filled' => 'Please provide a name for the relationship',
            ],
        ],

        'input.options.labeled.labels.*' => [
            'required_if' => 'Please add at least one label option.',
        ],

        'fields' => [
            'money' => [
                'amount' => [
                    'required_unless_filled' => 'Add at least one valid amount for this range.',
                    'required_with' => 'Add a valid amount.',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'mapping.name' => 'mapping name',
        'mapping.singularName' => 'mapping singular name',
        'mapping.description' => 'mapping description',
        'mapping.type' => 'mapping type',

        '*.markerGroups' => 'tag groups',
        '*.markerGroups.*.id' => 'tag group ID',
        '*.markerGroups.*.name' => 'tag group name',
        '*.markerGroups.*.description' => 'tag group description',
        '*.markerGroups.*.markers' => 'tags',
        '*.markerGroups.*.markers.*.name' => 'tag name',
        '*.markerGroups.*.markers.*.color' => 'tag color',
        '*.categories' => 'categories',
        '*.categories.*.id' => 'category ID',
        '*.categories.*.name' => 'category name',
        '*.categories.*.description' => 'category description',
        '*.categories.*.items' => 'category items',
        '*.categories.*.items.*.name' => 'category item name',
        '*.name' => 'base name',
        '*.spaces' => 'spaces',
        '*.spaces.*.name' => 'space name',
        '*.spaces.*.description' => 'space description',
        '*.spaces.*.pages' => 'pages',
        '*.spaces.*.pages.*.id' => 'page ID',
        '*.spaces.*.pages.*.name' => 'page name',
        '*.spaces.*.pages.*.description' => 'page description',
        '*.spaces.*.pages.*.pageType' => 'page type',
        '*.spaces.*.pages.*.singularName' => 'page singular name',
        '*.spaces.*.pages.*.fields' => 'fields',
        '*.spaces.*.pages.*.fields.*.name' => 'field name',
        '*.spaces.*.pages.*.fields.*.apiName' => 'field API name',
        '*.spaces.*.pages.*.fields.*.section' => 'field section',
        '*.spaces.*.pages.*.fields.*.type' => 'field type',
        '*.spaces.*.pages.*.fields.*.meta' => 'field meta',
        '*.spaces.*.pages.*.fields.*.options.category' => 'category',
        '*.spaces.*.pages.*.relationships' => 'relationships',
        '*.spaces.*.pages.*.relationships.*.name' => 'relationship name',
        '*.spaces.*.pages.*.relationships.*.type' => 'relationship type',
        '*.spaces.*.pages.*.relationships.*.to' => 'relationship to',
        '*.spaces.*.pages.*.relationships.*.inverseName' => 'relationship inverse name',
        '*.spaces.*.pages.*.relationships.*.tagGroup' => 'relationship tag group',

        'input.name' => 'name',
        'input.apiName' => 'API name',
        'input.recurrence' => 'recurrence',
        'input.dueBy' => 'due by',
        'input.startAt' => 'start at',
        'input.defaultFilterId' => 'default filter',
        'input.personalDefaultFilterId' => 'default filter',

        'dueBy' => 'due by',

        'fullName' => 'full name',
        'email' => 'email',

        'startAt' => 'start at date',
        'endAt' => 'end at date',

        'html' => 'HTML',
        'markdown' => 'markdown',
        'plaintext' => 'plain text',
    ],
];
