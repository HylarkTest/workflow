import { gql } from '@apollo/client';
import { getStrAroundToken } from '@/core/utils.js';
import { getEnabledFeatureOptions } from '@/core/display/getAllEntityData.js';

import GROUPABLE_FRAGMENT from '@/graphql/GroupableFragment.gql';
import MARKER_FRAGMENT from '@/graphql/markers/MarkerFragment.gql';
import FEATURE_LIST_FRAGMENT from '@/graphql/FeatureListFragment.gql';
import FETCHES_ACTIONS_FRAGMENT from '@/graphql/FetchesActionsFragment.gql';
import MEMBER_FRAGMENT from '@/graphql/MemberFragment.gql';

const featureFragments = {
    NOTES: `
id
name
preview: plaintext(truncate: 255)
isFavorite
`,
    LINKS: `
id
name
url
description
isFavorite
`,
    DOCUMENTS: `
id
name
filename
url
downloadUrl
extension
mimeType
isFavorite
`,
    PINBOARD: `
id
name
image {
    filename
    url
    mimeType
}
description
isFavorite
`,
    TODOS: `
id
name
completedAt
dueBy
description
location
priority
list: todoList {
    id
}
assigneeGroups {
    group {
        id
        name
    }
    assignees {
        id
        name
        email
        avatar
        role
        addedAt
        isAuthenticatedUser
    }
}
`,
    EVENTS: `
id
name
date: startAt
end: endAt
timezone
description
isAllDay
location
priority
calendar {
    id
    name
    color
}
`,
};

export const nestedFields = {
    ADDRESS: [
        'line1',
        'line2',
        'city',
        'state',
        'country',
        'postcode',
    ],
    DURATION: [
        'months',
        'weeks',
        'days',
        'hours',
        'minutes',
    ],
    DURATION_RANGE: [
        'from { months weeks days hours minutes }',
        'to { months weeks days hours minutes }',
    ],
    IMAGE: [
        'filename',
        'url',
        'originalUrl',
        'xOffset',
        'yOffset',
        'width',
        'height',
    ],
    CATEGORY: [
        'id',
        'name',
    ],
    FILE: [
        'url',
        'size',
        'extension',
        'filename',
    ],
    LOCATION: [
        'name',
        'id',
    ],
    RATING: [
        'stars',
        'max',
    ],
    MONEY: [
        'currency',
        'amount',
    ],
    MONEY_RANGE: [
        'currency',
        'amount { from to }',
    ],
    SALARY: [
        'currency',
        'period',
        'amount',
    ],
    SALARY_RANGE: [
        'currency',
        'period',
        'amount { from to }',
    ],
    SELECT: [
        'selectKey',
        'selectValue',
    ],
};

export const tagFragment = `{
    id
    name
    color(format: HEX)
    lightColor: color(format: HEX, lightness: 92)
    order
}`;

export function getNestedFieldsForField(field) {
    let subFields = [];
    if (field.type === 'MULTI') {
        /* eslint-disable no-use-before-define */
        subFields = `{
            ${buildDataRequests(field.options.fields)}
        }`;
        /* eslint-enable */
    } else {
        const nestedKey = field.type;
        subFields = _.get(nestedFields, nestedKey, []);

        if (field.options?.isRange) {
            if (_.has(nestedFields, `${nestedKey}_RANGE`)) {
                subFields = _.get(nestedFields, `${nestedKey}_RANGE`, []);
            } else {
                subFields = subFields.concat(['from', 'to']);
            }
        }
    }

    if (_.isArray(subFields)) {
        subFields = subFields.length ? `{ ${subFields.join(' ')} }` : '';
    }

    const isList = field.options?.list;
    if (isList) {
        subFields = `${subFields} main`;
    }

    if (field.options?.labeled) {
        subFields = `{ label labelKey fieldValue ${subFields} }`;
    } else {
        subFields = `{ fieldValue ${subFields} }`;
    }

    if (isList) {
        subFields = `{ listValue ${subFields} }`;
    }

    return subFields;
}

export function buildDataRequests(fields) {
    return fields.map((field) => {
        const subFields = getNestedFieldsForField(field);

        return `
            ${field.id}: ${field.apiName} ${subFields}
        `;
    }).join('');
}

export function buildSingleRelationFragment(mapping, relation) {
    const isToMany = _.endsWith(relation.type, 'TO_MANY');

    let itemFragment = `{ node {
        id
        spaceId
        name
        image {
            filename
            size
            url
            extension
        }
        mapping {
            id
            pages {
                id
                name
            }
        }
    } }`;

    if (isToMany) {
        itemFragment = `{
            edges ${itemFragment}
            pageInfo {
                hasNextPage
                endCursor
                total
                rawTotal
            }
        }`;
    }
    return `
        ${relation.id}: ${relation.apiName} ${itemFragment}
    `;
}

function buildRelationRequests(page, relationIds, tags) {
    const keyedTags = _.keyBy(tags, 'relationship.id');
    const relations = _.keyBy(page.relationships, 'id');

    return _(relationIds)
        .map((id) => relations[id])
        .compact()
        .map((relation) => {
            let relationTagFragment = '';
            const id = relation.id;
            if (keyedTags && keyedTags[id]) {
                relationTagFragment = `edges { tags { ${keyedTags[id].apiName} ${tagFragment} } }`;
            }
            if (relation.type.match(/TO_MANY$/)) {
                return `
                ${id}: ${relation.apiName}(first: 1) {
                    pageInfo { total }
                    ${relationTagFragment}
                }
            `;
            }
            // if (keys[0][1] === 'COUNT') {
            //     return `${relation.apiName}(first: 1) { pageInfo { total } ${relationTagFragment} }`;
            // }
            return `
                ${id}: ${relation.apiName} { node {
                    id
                    name
                    image { url }
                    mapping {
                        id
                        pages {
                            id
                            name
                        }
                    }
                    ${relationTagFragment}
                } }
            `;
        })
        .join();
}

export function buildItemFragment(page, fieldIds, markerIds, relationIds, featureIds) {
    const fields = fieldIds
        ? _.intersectionWith(page.fields, fieldIds, (field, id) => {
            return _.isObject(id) ? field === id : field.id === id || id.startsWith(`${field.id}.`);
        })
        : page.fields;

    const markers = markerIds
        ? _.intersectionWith(page.markerGroups, markerIds, (group, id) => group.id === id)
        : page.markerGroups;

    const enabledFeatureOptions = getEnabledFeatureOptions(page);

    // featureIds are the features used in the current view's visible data
    // (e.g this.currentView.visibleData in EntitiesContent.vue)
    // In order to only request features that are enabled,
    // we need to filter based on all possible enabled feature options.
    const features = featureIds
        ? featureIds.filter((val) => [...enabledFeatureOptions, 'EMAILS_PRESENT'].includes(val))
        : enabledFeatureOptions;

    const [relationshipTags, normalTags] = _.partition(markers, 'relationship');

    const relationRequest = buildRelationRequests(page, relationIds, relationshipTags);

    const dataRequests = buildDataRequests(fields);
    const tagRequest = normalTags.map(({ apiName, id }) => `
        ${id}: ${apiName} ${tagFragment}
    `).join('');
    const featureRequest = features.filter((val) => {
        return ![
            'FAVORITES.FAVORITES',
            'PRIORITIES.PRIORITIES',
            'TIMEKEEPER.TIMEKEEPER',
        ].includes(val) && !val.startsWith('TIMEKEEPER.');
    }).map((featureId) => {
        const [val, feature] = getStrAroundToken(featureId, '.');
        const fieldAlias = `${val}__${feature}`;
        let key = val.toLowerCase();
        if (key === 'pinboard') {
            key = 'pins';
        } else if (key === 'attachments') {
            key = 'documents';
        }
        if (feature.startsWith('FIRST_')) {
            const fragment = featureFragments[val];
            return `${fieldAlias}: ${key}(
                ${val === 'TODOS' ? 'filters: [{ isCompleted: false }],' : ''}
                orderBy: { field: CREATED_AT, direction: ASC },
                first: 1
            ) {
                edges { node { ${fragment} } }
            }
            `;
        }
        if (feature.startsWith('LAST_')) {
            const fragment = featureFragments[val];
            return `${fieldAlias}: ${key}(
                ${val === 'TODOS' ? 'filters: [{ isCompleted: false }],' : ''}
                orderBy: { field: CREATED_AT, direction: DESC },
                first: 1
            ) {
                edges { node { ${fragment} } }
            }
            `;
        }
        if (feature === 'UPCOMING_EVENT') {
            const fragment = featureFragments.EVENTS;
            return `${fieldAlias}: ${val.toLowerCase()}(
                orderBy: { field: DATE, direction: ASC },
                endsAfter: "now",
                first: 1,
                includeRecurringInstances: true,
            ) {
                edges { node { ${fragment} } }
            }
            `;
        }
        if (feature === 'NEXT_TODO') {
            const fragment = featureFragments.TODOS;
            return `${fieldAlias}: ${val.toLowerCase()}(
                filters: [{ isCompleted: false }],
                orderBy: [{ field: DUE_BY, direction: ASC }, { field: CREATED_AT, direction: DESC }]
                first: 1
            ) {
                edges { node { ${fragment} } }
            }
            `;
        }
        if (featureId === 'EMAILS_PRESENT') {
            return `${fieldAlias}: emailAssociations {
                directAssociations { pageInfo { total } }
                addressAssociations { edges { node {
                    id
                    email: address
                    account { id accountName provider }
                } } pageInfo { total } }
            }`;
        }
        if (val === 'TODOS' || val === 'EVENTS') {
            return `
            ${fieldAlias}: ${val.toLowerCase()} {
                pageInfo { total }
            }
            EXTERNAL_${fieldAlias}: ${_.camelCase(`external-${val}`)} {
                paginatorInfo { total }
            }
            `;
        }
        return `${fieldAlias}: ${key} {
            pageInfo { total }
        }
        `;
    }).join('');

    let request = '';
    if (dataRequests) {
        request = `
            data {
                ${dataRequests}
            }
        `;
    }

    if (tagRequest) {
        request = `
            ${request}
            markers {
                ${tagRequest}
            }
        `;
    }

    if (featureRequest) {
        request = `
            ${request}
            features {
                ${featureRequest}
            }
        `;
    }
    if (features.includes('FAVORITES.FAVORITES')) {
        request = `
            ${request}
            isFavorite
        `;
    }
    if (features.includes('PRIORITIES.PRIORITIES')) {
        request = `
            ${request}
            priority
        `;
    }
    if (
        features.includes('TIMEKEEPER.TIMEKEEPER')
        || features.includes('TIMEKEEPER.TIME_START')
        || features.includes('TIMEKEEPER.TIME_DUE')
        || features.includes('TIMEKEEPER.TIME_PHASE')
    ) {
        request = `
            ${request}
            deadlines {
                startAt
                dueBy
                isCompleted
                status
            }
        `;
    }

    if (relationRequest) {
        request = `
            ${request}
            relations {
                ${relationRequest}
            }
        `;
    }
    request = `
        id
        spaceId
        name
        image { url }
        ${request}
        markerGroups {
            group {
                id
                name
                type
            }
            ...on TagMarkerCollection {
                markers {
                    id
                    name
                    color
                    order
                }
            }
            ...on PipelineMarkerCollection {
                markers {
                    id
                    name
                    color
                    order
                }
            }
            ...on StatusMarkerCollection {
                marker {
                    id
                    name
                    color
                    order
                }
            }
        }
        assigneeGroups {
            group {
                id
                name
            }
            assignees {
                id
                name
                email
                avatar
                role
                addedAt
                isAuthenticatedUser
            }
        }
        mapping {
            id
            apiName
            apiSingularName
            pages {
                id
                name
            }
        }
        createdAt
        updatedAt
    `;

    return request;
}

export function buildPreviewItemFragment(page) {
    return `
        id
        spaceId
        name
        image { url }
        data {
            ${buildDataRequests(page.fields)}
        }
        mapping {
            id
            apiName
            apiSingularName
            pages {
                id
                name
            }
        }
        createdAt
        updatedAt
    `;
}

export function buildGetManyRequest(
    page,
    fieldIds = null,
    markerIds = null,
    relationIds = null,
    featureIds = null,
    queryName = null
) {
    return gql`
        query ${queryName || page.apiName}(
            $forRelation: RelationQueryInput,
            $orderBy: [OrderByClause!],
            $filters: [ItemFilterInput!],
            $markers: [MarkerFilterInput!],
            $fields: [FieldFilterInput!],
            $after: String,
        ) {
            items {
                ${_.lowerFirst(page.apiName)}(
                    forRelation: $forRelation,
                    orderBy: $orderBy,
                    filters: $filters,
                    markers: $markers,
                    fields: $fields,
                    after: $after,
                ) {
                    edges {
                        node {
                            ${buildItemFragment(page, fieldIds, markerIds, relationIds, featureIds)}
                        }
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                        total
                        rawTotal
                    }
                }
            }
        }
  `;
}

export function buildGroupedRequest(
    page,
    fieldIds = null,
    markerIds = null,
    relationIds = null,
    featureIds = null,
    queryName = null
) {
    return gql`
        query ${queryName || page.apiName}_Grouped(
            $orderBy: [OrderByClause!],
            $filters: [ItemFilterInput!],
            $markers: [MarkerFilterInput!],
            $fields: [FieldFilterInput!],
            $after: String,
            $group: String!,
            $includeGroups: [String],
        ) {
            groupedItems {
                ${_.lowerFirst(page.apiName)}(
                    orderBy: $orderBy,
                    filters: $filters,
                    markers: $markers,
                    fields: $fields,
                    after: $after,
                    group: $group,
                    includeGroups: $includeGroups,
                ) {
                    groups {
                        groupHeader
                        group {
                            ...Groupable
                        }
                        edges {
                            node {
                                ${buildItemFragment(page, fieldIds, markerIds, relationIds, featureIds)}
                            }
                        }
                        pageInfo {
                            hasNextPage
                            endCursor
                            total
                            rawTotal
                        }
                    }
                }
            }
        }
        ${GROUPABLE_FRAGMENT}
        ${MARKER_FRAGMENT}
        ${FEATURE_LIST_FRAGMENT}
    `;
}

export function buildGetSingleRequest(
    page,
    fieldIds = null,
    tagIds = null,
    relationIds = null,
    features = null,
    queryName = null
) {
    return gql`
        query ${queryName || page.apiSingularName}($id: ID!) {
            items {
                item: ${_.lowerFirst(page.apiSingularName)}(id: $id) {
                    ${buildItemFragment(page, fieldIds, tagIds, relationIds, features)}
                    ...FetchesActionsFragment
                }
            }
        }
        ${FETCHES_ACTIONS_FRAGMENT}
        ${MEMBER_FRAGMENT}
    `;
}

export function simpleMappingRequestFeatures(mapping) {
    return mapping.features.map(({ val }) => {
        if (['FAVORITES', 'PRIORITIES', 'TIMEKEEPER'].includes(val)) {
            return `${val}.${val}`;
        }
        if (val === 'EMAILS') {
            return 'EMAILS_PRESENT';
        }
        return `${val}.FEATURE_COUNT`;
    });
}

export function simpleMappingRequest(mapping, type, includesFeatures = false, queryName = null) {
    if (type === 'ONE') {
        return buildGetSingleRequest(
            mapping,
            null,
            null,
            null,
            includesFeatures ? simpleMappingRequestFeatures(mapping) : null,
            queryName
        );
    }
    if (type === 'MANY') {
        return buildGetManyRequest(
            mapping,
            null,
            null,
            null,
            includesFeatures ? simpleMappingRequestFeatures(mapping) : null,
            queryName
        );
    }
    return null;
}
