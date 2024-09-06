// Gets all mapping/blueprint data for pages that are entities and entity type

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

export const featureOptions = {
    TODOS: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'NEXT_TODO',
        'FIRST_TODO',
        'LAST_TODO',
    ],
    EVENTS: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'UPCOMING_EVENT',
        'FIRST_EVENT',
        'LAST_EVENT',
    ],
    DOCUMENTS: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'FIRST_DOCUMENT',
        'LAST_DOCUMENT',
    ],
    NOTES: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'FIRST_NOTE',
        'LAST_NOTE',
    ],
    PINBOARD: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'FIRST_PIN',
        'LAST_PIN',
    ],
    LINKS: [
        'FEATURE_COUNT',
        'FEATURE_NEW',
        'FEATURE_GO',
        'FIRST_LINK',
        'LAST_LINK',
    ],
    EMAILS: [
        'FEATURE_GO',
    ],
    TIMEKEEPER: [
        'TIMEKEEPER',
        'TIME_START',
        'TIME_DUE',
        'TIME_PHASE',
    ],
    PRIORITIES: [
        'PRIORITIES',
    ],
    FAVORITES: [
        'FAVORITES',
    ],
};

const excludeFromFeatureDisplayOptions = [
    'TIMEKEEPER',
];

const hylarkOnly = [
    'TODOS.NEXT_TODO',
    'TODOS.FIRST_TODO',
    'TODOS.LAST_TODO',
    'TODOS.FEATURE_COUNT',
    'EVENTS.UPCOMING_EVENT',
    'EVENTS.FIRST_EVENT',
    'EVENTS.LAST_EVENT',
    'EVENTS.FEATURE_COUNT',
];

export function isHylarkOnlyData(formattedId) {
    return hylarkOnly.includes(formattedId);
}

const relationshipOptions = {
    ONE: [
        'RELATIONSHIP_RECORD',
    ],
    MANY: [
        'RELATIONSHIP_COUNT',
    ],
};

const fieldOptions = {
    LIST: [
        'LIST_COUNT',
        'LIST_FIRST',
        'LIST_MAIN',
    ],
};

// function getMultiOptions(field) {
//     return getFields(field.options);
// }

function getFieldsWithoutExclusions(fields, exclude = []) {
    if (exclude.includes('LISTS')) {
        const remaining = fields.filter((field) => {
            return !field.options?.list;
        });
        return remaining;
    }
    return fields;
}

export function getFields(fieldContainer, exclude) {
    const fields = fieldContainer.fields;
    const validFields = getFieldsWithoutExclusions(fields, exclude);
    return _(validFields).map((field) => {
        const obj = _.cloneDeep(field);
        if (field.options?.list) {
            obj.displayOptions = fieldOptions.LIST;
        }
        if (field.type === 'MULTI') {
            const subfields = getFields(field.options, exclude);
            obj.options.fields = subfields;
        }
        return obj;
    }).value();
}

function getRelationships(mapping) {
    const relationships = mapping.relationships;
    return _(relationships).map((relationship) => {
        const relationshipType = relationship.type.split('_TO_')[1];
        return {
            ...relationship,
            displayOptions: relationshipOptions[relationshipType],
        };
    }).value();
}

export function getFeatures(mapping) {
    const features = mapping.features;
    return _(features).map((feature) => {
        const options = featureOptions[feature.val];
        const filteredOptions = options.filter((option) => {
            return !excludeFromFeatureDisplayOptions.includes(option);
        });
        return {
            ...feature,
            displayOptions: filteredOptions,
        };
    }).compact().value();
}

export function getEnabledFeatureOptions(mapping) {
    const features = mapping.features;
    return _(features).flatMap((feature) => {
        const val = feature.val;
        return featureOptions[feature.val].filter((option) => {
            return option !== 'FEATURE_NEW' && option !== 'FEATURE_GO';
        }).map((option) => `${val}.${option}`);
    }).value();
}

// function filterMarkers(groups, markerType) {
//     if (!groups) {
//         return null;
//     }
//     return groups.filter((group) => {
//         return group.group.type === markerType;
//     });
// }

function getMarkers(mapping) {
    const groups = mapping.markerGroups;
    return groups;
}

const systemData = [
    {
        val: 'CREATED_AT',
    },
    {
        val: 'UPDATED_AT',
    },

];

const collaborationData = [
    {
        val: 'ASSIGNEES',
    },
];

function getCollaboration() {
    return collaborationData;
}

export function allData(mapping, exclude = []) {
    const fields = getFields(mapping, exclude);
    const relationships = getRelationships(mapping);
    const features = getFeatures(mapping);
    const markers = getMarkers(mapping);
    const showCollaboration = isActiveBaseCollaborative();

    const obj = {};

    const excludeRelationships = exclude.includes('RELATIONSHIPS');

    if (fields?.length) {
        obj.FIELDS = fields;
    }
    if (markers?.length) {
        obj.MARKERS = markers;
    }
    if (!excludeRelationships && relationships?.length) {
        obj.RELATIONSHIPS = relationships;
    }
    if (features?.length) {
        obj.FEATURES = features;
    }
    if (showCollaboration) {
        const collaboration = getCollaboration();
        obj.COLLABORATION = collaboration;
    }
    obj.SYSTEM = systemData;
    return obj;
}

export function getUsedSlotsDefault(availableData) {
    const filteredData = availableData.filter((item) => {
        return item.slot;
    });

    let dataArr = [];
    if (!filteredData.length) {
        const name = availableData.find((item) => {
            return item.info?.subType === 'SYSTEM_NAME' || item.info?.subType === 'NAME';
        });
        if (name) {
            name.slot = 'HEADER1';
            dataArr.push(name);
        }
    } else {
        dataArr = filteredData;
    }

    return dataArr;
}

export function getColumnDefaults(data) {
    return data.filter((datum) => {
        const dataType = datum.dataType;
        const omits = ['RELATIONSHIPS'];
        if (omits.includes(dataType)) {
            return false;
        }
        if (dataType === 'FIELDS') {
            const hasParent = datum.info.parent;
            const hasDisplayOption = datum.displayOption;
            const isList = datum.info?.options?.list;
            const isMulti = datum.info.fieldType === 'MULTI';
            const omitFromDefault = hasParent || hasDisplayOption || isList || isMulti;

            if (omitFromDefault) {
                return false;
            }
            return true;
        }
        if (dataType === 'FEATURES') {
            const omittedDisplays = [
                'FEATURE_NEW',
                'FEATURE_GO',
                'FEATURE_NEW',
                'FIRST_TODO',
                'LAST_TODO',
                'FIRST_EVENT',
                'LAST_EVENT',
                'FIRST_DOCUMENT',
                'FIRST_NOTE',
                'FIRST_PIN',
                'FIRST_LINK',
            ];
            if (omittedDisplays.includes(datum.displayOption)) {
                return false;
            }
            return true;
        }
        return true;
    });
}

export default {
    allData,
    featureOptions,
    getUsedSlotsDefault,
    getFields,
};
