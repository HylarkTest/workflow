import * as Sentry from '@sentry/vue';
import { gql } from '@apollo/client';
import { getOperationName } from '@apollo/client/utilities';
import {
    buildItemFragment,
    getNestedFieldsForField,
    tagFragment,
} from '@/http/apollo/buildMappingRequests.js';
import {
    getCachedOperationNames,
    removeTypename,
} from '@/core/helpers/apolloHelpers.js';
import ASSOCIATE_ITEM from '@/graphql/items/mutations/AssociateItem.gql';
import ASSOCIATE_ITEM_TO_EXTERNAL_EVENT from '@/graphql/items/mutations/AssociateItemToExternalEvent.gql';
import DISSOCIATE_ITEM_FROM_EXTERNAL_EVENT from '@/graphql/items/mutations/DissociateItemFromExternalEvent.gql';
import ASSOCIATE_ITEM_TO_EXTERNAL_TODO from '@/graphql/items/mutations/AssociateItemToExternalTodo.gql';
import DISSOCIATE_ITEM_FROM_EXTERNAL_TODO from '@/graphql/items/mutations/DissociateItemFromExternalTodo.gql';
import ASSOCIATE_ITEM_TO_EMAIL from '@/graphql/items/mutations/AssociateItemToEmail.gql';
import DISSOCIATE_ITEM_FROM_EMAIL from '@/graphql/items/mutations/DissociateItemFromEmail.gql';
import ASSOCIATE_ITEM_TO_EMAIL_ADDRESS from '@/graphql/items/mutations/AssociateItemToEmailAddress.gql';
import DISSOCIATE_ITEM_FROM_EMAIL_ADDRESS from '@/graphql/items/mutations/DissociateItemFromEmailAddress.gql';
import REMOVE_ITEM from '@/graphql/items/mutations/RemoveItem.gql';
import EMAIL_ADDRESS_ASSOCIATIONS from '@/graphql/mail/queries/EmailAddressAssociations.gql';
import EMAILS from '@/graphql/mail/queries/Emails.gql';
import GROUPED_EMAILS from '@/graphql/mail/queries/GroupedEmails.gql';
import { validationFeedback, warningFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import eventBus from '@/core/eventBus.js';
import initializeConnections, { instantiateNode } from '@/http/apollo/initializeConnections.js';
import IntegratableListItem from '@/core/models/IntegratableListItem.js';
import Event from '@/core/models/Event.js';
import Email from '@/core/models/Email.js';
import Todo from '@/core/models/Todo.js';
import { isValidationError } from '@/http/checkResponse.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export const RELATIONSHIP_ADDED = Symbol('Relationship added');
export const RELATIONSHIP_REMOVED = Symbol('Relationship removed');
export const ITEM_DISASSOCIATED = Symbol('Item disassociated');
export const ITEM_ASSOCIATED = Symbol('Item associated');
export const ITEM_DELETED = Symbol('Item deleted');
export const ITEM_UPDATED = Symbol('Item deleted');
export const ITEM_CREATED = Symbol('Item deleted');

export function initializeItem(item) {
    const features = {};
    _.forEach(item.features, (data, key) => {
        if (key === '__typename') {
            return;
        }
        let feature = key.replace('__', '.');
        if (_.startsWith(feature, 'EXTERNAL_')) {
            feature = feature.substr('EXTERNAL_'.length);
            const count = data.paginatorInfo.total || 0;
            if (_.has(features, feature)) {
                features[feature] += count;
            } else {
                features[feature] = count;
            }
        } else if (_.endsWith(feature, 'FEATURE_COUNT')) {
            const count = data.pageInfo.total || 0;
            if (_.has(features, feature)) {
                features[feature] += count;
            } else {
                features[feature] = count;
            }
            features[feature] = data.pageInfo.total;
        } else if (feature === '.EMAILS_PRESENT') {
            features.__EMAILS_PRESENT = !!(data.directAssociations.pageInfo.total
                + data.addressAssociations.pageInfo.total);
            features.EMAILS_ASSOCIATED_ADDRESSES = initializeConnections(data.addressAssociations);
        } else {
            features[feature] = _.first(data.edges.map((edge) => edge.node && instantiateNode(edge)));
        }
    });
    return {
        ...item,
        features,
    };
}

export function initializeItems(data) {
    return initializeConnections(data, initializeItem, false, true);
}

function formatOnMultis(cb) {
    return (value, field) => {
        if (!value) {
            return value;
        }
        if (field.options?.multiSelect) {
            return value.map((valueItem) => {
                return cb(valueItem, field);
            });
        }
        return cb(value, field);
    };
}
function extractKey(key = 'id') {
    return formatOnMultis((value) => {
        if (_.isObject(value)) {
            return value[key];
        }
        return value;
    });
}

// function formatOnRange(cb) {
//     return (value, field) => {
//         if (field.options?.isRange) {
//             return {
//                 min: cb(value.min, field),
//                 max: cb(value.max, field),
//             };
//         }
//         return cb(value, field);
//     };
// }

const dataFormatters = {
    IMAGE: formatOnMultis((value) => {
        // If there is an image in the form but it still has filename
        // assume it hasn't been updated by cropper and remove from request.
        if (value?.filename) {
            return undefined;
        }
        return value;
    }),
    MONEY: (value, field) => {
        if (field.options?.currency) {
            return _.pick(value, ['amount']);
        }
        return value;
    },
    SALARY: (value, field) => {
        if (field.options?.currency && field.options?.period) {
            return value.amount;
        }
        return value;
    },
    // DURATION: formatOnRange((value, field) => {
    //
    // }),
    RATING: extractKey('stars'),
    SELECT: extractKey('selectKey'),
    CATEGORY: extractKey(),
    LOCATION: extractKey(),
    MULTI: formatOnMultis((value, field) => {
        const multiSubFields = field.options.fields;

        const data = { ...value };
        _.forEach(data, (val, id) => {
            const subField = _.find(multiSubFields, { id });

            // eslint-disable-next-line no-use-before-define
            const newValue = formatFieldValue(val, subField);
            if (_.isUndefined(newValue)) {
                delete data[subField.id];
            } else {
                data[subField.id] = newValue;
            }
        });
        return _.mapKeys(data, (val, id) => {
            return _.find(multiSubFields, { id }).apiName;
        });
    }),
};

function runThroughListOrSingle(value, field, cb) {
    if (field.options?.list) {
        return {
            ...value,
            listValue: value.listValue?.filter((val) => {
                if (val?.label) {
                    return true;
                }
                const checkVal = val?.fieldValue;
                if (_.isBoolean(checkVal)) {
                    return true;
                }
                return checkVal;
            }).map((val) => cb(val, field)),
        };
    }
    return cb(value, field);
}

function findField(fields, fieldId) {
    for (const field of fields) {
        if (field.id === fieldId) {
            return field;
        }
        if (field.type === 'MULTI') {
            const foundField = findField(field.options.fields, fieldId);
            if (foundField) {
                return foundField;
            }
        }
    }
    return null;
}

function formatFieldValue(value, field) {
    if (!value) {
        return value;
    }
    return runThroughListOrSingle(value, field, (val) => {
        let formatted = val.fieldValue;
        if (_.has(dataFormatters, field.type)) {
            formatted = dataFormatters[field.type](formatted, field);
        }
        formatted = { fieldValue: formatted };
        if (field.options?.labeled) {
            formatted.label = field.options.labeled.freeText ? val.label : val.labelKey;
        }
        if (field.options?.list) {
            formatted.main = !!val.main;
        }
        return formatted;
    });
}

function getResultsPath(mapping, action) {
    return `data.items.${mapping.apiName}.${action}${_.upperFirst(mapping.apiSingularName)}.${mapping.apiSingularName}`;
}

function buildMutationQuery(mapping, method, response = 'id spaceId name image { url }') {
    const singularQueryKey = mapping.apiSingularName;
    const queryKey = mapping.apiName;
    const type = _.upperFirst(singularQueryKey);
    const methodType = _.upperFirst(method);
    const methodField = _.lowerFirst(method);
    const queryName = `${methodType}${type}`;
    const inputType = `${type}Item${methodType}Input`;
    const mutationField = `${methodField}${type}`;

    const extra = response ? `${singularQueryKey} { ${response} }` : '';

    return gql`
        mutation ${queryName}($input: ${inputType}!) {
            items {
                ${queryKey} {
                    ${mutationField}(input: $input) {
                        code
                        ${extra}
                    }
                }
            }
        }
    `;
}

export function updateItemProperty(mapping, itemId, field, value) {
    const fields = field.split('.').reverse();
    const response = fields.reduce((combined, key) => {
        if (combined) {
            return `${key} { ${combined} }`;
        }
        return key;
    }, '');

    const mutation = buildMutationQuery(mapping, 'update', `id spaceId name image { url } ${response}`);

    const input = {
        id: itemId,
    };
    _.set(input, _.isString(field) ? field : field.apiName, value);

    return baseApolloClient().mutate({
        mutation,
        refetchQueries: [mapping.apiName, `${mapping.apiName}_Grouped`],
        variables: {
            input,
        },
    }).then((result) => {
        eventBus.dispatch(ITEM_UPDATED, _.get(result.data, getResultsPath(mapping, 'update')));
        return result;
    });
}

/*
 * Alright, what's going on here?
 * This function takes a form and a field ID and then updates that value on an
 * item, returning only the changed field.
 */
export function updateItemField(mapping, itemId, fieldId, form) {
    // The trickiest part is to build the GraphQL query from the field ID which
    // is a dot separated string of field IDs.
    // To build the query, we need to work backwards, so we split the field ID
    // and revers it.
    const path = fieldId.split('.').reverse();
    // Response path will be used later to format the validation errors.
    const responsePath = [];

    let response = path.reduce((combined, key) => {
        // If the key is a number then it is not necessary for building the
        // query as it references an index in an array. We need it for formatting
        // the validation errors though.
        if (/^\d+$/.test(key)) {
            responsePath.unshift(key);
            return combined;
        }

        const field = findField(mapping.fields, key);
        responsePath.unshift(field.apiName);
        const isLabeled = field.options?.labeled;

        // If the combined variable is not empty that means we are dealing with
        // multi fields. We just need to fetch the name of the field and wrap
        // the combined value with it.
        if (combined) {
            let subFields = `fieldValue ${combined}`;
            if (isLabeled) {
                subFields = `{ label labelKey fieldValue ${subFields} }`;
            } else {
                subFields = `{ fieldValue ${subFields} }`;
            }
            if (field.options?.list) {
                subFields = `{ listValue ${subFields} }`;
            }
            return `{ ${key}: ${field.apiName} ${subFields} }`;
        }

        const nested = getNestedFieldsForField(field);
        return `{ ${key}: ${field.apiName} ${nested} }`;
    }, '');

    response = `
    data ${response}
    `;

    const query = buildMutationQuery(mapping, 'update', `id spaceId name image { url } ${response}`);

    return form.post({
        query,
        refetchQueries: [mapping.apiName, `${mapping.apiName}_Grouped`],
        formatData(originalData) {
            const lastField = findField(mapping.fields, path[0]);
            const val = formatFieldValue(removeTypename(originalData.dataValue), lastField);
            const data = {};
            _.set(data, responsePath, val);
            return {
                input: {
                    id: itemId,
                    data,
                },
            };
        },
        wrapInput: false,
        formatErrorResponse({ graphQLErrors: errors }) {
            const messages = errors[0].extensions.validation;
            return _.mapKeys(messages, (_, key) => key.replace(`input.data.${responsePath.join('.')}`, 'dataValue'));
        },
    }).then((result) => {
        eventBus.dispatch(ITEM_UPDATED, _.get(result, getResultsPath(mapping, 'update')));
        return result;
    });
}

function formatFieldIdToApiNameOptions(mapping) {
    return {
        wrapInput: false,
        formatData(request) {
            const requestWithoutTypename = removeTypename(request);
            let data = { ...requestWithoutTypename.data };

            // Sometimes the form has fields that are not in the mapping.
            Sentry.setExtra('mapping fields', mapping.fields);
            Sentry.setExtra('data', data);
            _.forEach(data, (value, id) => {
                const field = _.find(mapping.fields, { id });
                const newValue = formatFieldValue(value, field);
                if (_.isUndefined(newValue)) {
                    delete data[field.id];
                } else {
                    data[field.id] = newValue;
                }
            });
            data = _.mapKeys(data, (value, id) => {
                return _.find(mapping.fields, { id }).apiName;
            });
            return {
                input: {
                    ...requestWithoutTypename,
                    data,
                },
            };
        },
        formatErrorResponse({ graphQLErrors: errors }) {
            const messages = errors[0].extensions.validation;
            return _.mapKeys(messages, (__, key) => {
                const field = _.find(mapping.fields, ['apiName', key.replace(/input\.data\.(\w+)\..*$/, '$1')]);
                if (field) {
                    return key.replace(`input.data.${field.apiName}`, `data.${field.id}`);
                }
                return key.replace(/^input\./, '');
            });
        },
    };
}

export function createItem(form, mapping) {
    const response = buildItemFragment(mapping);
    const query = buildMutationQuery(mapping, 'create', response);
    const formatOptions = formatFieldIdToApiNameOptions(mapping);
    return form.post({
        query,
        refetchQueries: [mapping.apiName, `${mapping.apiName}_Grouped`],
        ...formatOptions,
        formatData(request) {
            const { input } = formatOptions.formatData(request);
            return {
                input: {
                    ...input,
                    ...(input.markers ? {
                        markers: input.markers.map((marker) => ({
                            markers: marker.markers,
                            groupId: _.find(mapping.markerGroups, { id: marker.blueprintGroupId }).group.id,
                            context: marker.blueprintGroupId,
                        })),
                    } : {}),
                },
            };
        },
    }).then(_.property(getResultsPath(mapping, 'create'))).then((item) => {
        eventBus.dispatch(ITEM_CREATED, item);
        return item;
    });
}

export function updateItem(form, mapping) {
    const response = buildItemFragment(mapping);
    const query = buildMutationQuery(mapping, 'update', response);
    return form.post({
        query,
        refetchQueries: [mapping.apiName, `${mapping.apiName}_Grouped`],
        ...formatFieldIdToApiNameOptions(mapping),
    }).then((result) => {
        eventBus.dispatch(ITEM_UPDATED, _.get(result, getResultsPath(mapping, 'update')));
        return result;
    });
}

export function duplicateItem(item, records, mapping) {
    const response = buildItemFragment(mapping);
    const mutation = buildMutationQuery(mapping, 'duplicate', response);
    const client = baseApolloClient();
    return client.mutate({
        mutation,
        variables: {
            input: {
                id: item.id,
                ...records,
            },
        },
        refetchQueries: getCachedOperationNames([mapping.apiName, `${mapping.apiName}_Grouped`], client),
    }).then(_.property(getResultsPath(mapping, 'duplicate'))).then((newItem) => {
        eventBus.dispatch(ITEM_CREATED, newItem);
        return newItem;
    });
}

export function deleteItem(item, mapping) {
    const mutation = buildMutationQuery(mapping, 'delete', null);
    const client = baseApolloClient();
    return client.mutate({
        mutation,
        variables: {
            input: { id: item.id },
        },
        refetchQueries: getCachedOperationNames([mapping.apiName, `${mapping.apiName}_Grouped`], client),
        update(cache) {
            cache.evict({ id: cache.identify(item) });
            cache.gc();
        },
    }).then((result) => {
        eventBus.dispatch(ITEM_DELETED, item);
        return result;
    });
}

export function setMarker(item, marker, group, mapping) {
    const type = _.upperFirst(mapping.apiSingularName);
    const mappingGroup = _.find(mapping.markerGroups, ['id', group.info.groupId]);
    const mutation = gql`
        mutation SetMarkerOn${type}($input: SetMarkerInput!) {
            setMarker(input: $input) {
                code
                node {
                    ...on ${type}Item {
                        id
                        markers {
                            ${mappingGroup.id}: ${mappingGroup.apiName} ${tagFragment}
                        }
                    }
                }
            }
        }
    `;
    const client = baseApolloClient();
    return client.mutate({
        mutation,
        refetchQueries: getCachedOperationNames([
            mapping.apiName,
            `${mapping.apiName}_Grouped`,
        ], client),
        variables: {
            input: {
                markerId: marker.id,
                markableId: item.id,
                context: mappingGroup.id,
            },
        },
    });
}

export function removeMarker(item, marker, group, mapping) {
    const type = _.upperFirst(mapping.apiSingularName);
    const mappingGroup = _.find(mapping.markerGroups, ['id', group.info.groupId]);
    const mutation = gql`
        mutation RemoveMarkerOn${type}($input: RemoveMarkerInput!) {
            removeMarker(input: $input) {
                code
                node {
                    ...on ${type}Item {
                        id
                        markers {
                            ${mappingGroup.id}: ${mappingGroup.apiName} ${tagFragment}
                        }
                    }
                }
            }
        }
    `;
    const client = baseApolloClient();
    return client.mutate({
        mutation,
        refetchQueries: getCachedOperationNames([
            mapping.apiName,
            `${mapping.apiName}_Grouped`,
        ], client),
        variables: {
            input: {
                markerId: marker.id,
                markableId: item.id,
                context: mappingGroup.id,
            },
        },
    });
}

function getNodeId(node) {
    return node instanceof Event ? node.primaryId : node.id;
}

function sendExternalAssociationMutation(mutation, item, node, list, listKey) {
    return baseApolloClient().mutate({
        mutation,
        variables: {
            input: {
                nodeId: item.id,
                sourceId: node.account.id,
                [listKey]: list.id,
                id: getNodeId(node),
            },
        },
    });
}

function associateItemToExternalNode(node, item) {
    if (node instanceof Event) {
        return sendExternalAssociationMutation(
            ASSOCIATE_ITEM_TO_EXTERNAL_EVENT,
            item,
            node,
            node.calendar,
            'calendarId'
        );
    }
    if (node instanceof Todo) {
        return sendExternalAssociationMutation(
            ASSOCIATE_ITEM_TO_EXTERNAL_TODO,
            item,
            node,
            node.list,
            'todoListId'
        );
    }
    if (node instanceof Email) {
        return sendExternalAssociationMutation(
            ASSOCIATE_ITEM_TO_EMAIL,
            item,
            node,
            node.mailbox,
            'mailboxId'
        );
    }
    return null;
}

function removeItemFromExternalNode(node, item) {
    if (node instanceof Event) {
        return sendExternalAssociationMutation(
            DISSOCIATE_ITEM_FROM_EXTERNAL_EVENT,
            item,
            node,
            node.calendar,
            'calendarId'
        );
    }
    if (node instanceof Todo) {
        return sendExternalAssociationMutation(
            DISSOCIATE_ITEM_FROM_EXTERNAL_TODO,
            item,
            node,
            node.list,
            'listId'
        );
    }
    if (node instanceof Email) {
        return sendExternalAssociationMutation(
            DISSOCIATE_ITEM_FROM_EMAIL,
            item,
            node,
            node.mailbox,
            'mailboxId'
        );
    }
    return null;
}

function associateItemToInternalNode(node, item) {
    return baseApolloClient().mutate({
        mutation: ASSOCIATE_ITEM,
        variables: {
            input: {
                itemId: item.id,
                associatableId: getNodeId(node),
            },
        },
    });
}

export function associateItem(node, item) {
    let promise;
    if (node instanceof IntegratableListItem && node.isExternalItem()) {
        promise = associateItemToExternalNode(node, item);
    } else {
        promise = associateItemToInternalNode(node, item);
    }
    return promise?.then((result) => {
        eventBus.dispatch(ITEM_ASSOCIATED, item);
        return result;
    });
}

export function removeItemFromInternalNode(node, item) {
    return baseApolloClient().mutate({
        mutation: REMOVE_ITEM,
        variables: {
            input: {
                itemId: item.id,
                associatableId: getNodeId(node),
            },
        },
    });
}

export function removeItem(node, item) {
    let promise;
    if (node instanceof IntegratableListItem && node.isExternalItem()) {
        promise = removeItemFromExternalNode(node, item);
    } else {
        promise = removeItemFromInternalNode(node, item);
    }
    return promise?.then((result) => {
        eventBus.dispatch(ITEM_DISASSOCIATED, item);
        return result;
    });
}

function sendEmailAddressAssociationQuery(mutation, address, item, account) {
    const client = baseApolloClient();
    return client.mutate({
        mutation,
        variables: {
            input: {
                nodeId: item.id,
                address,
                sourceId: account.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            EMAIL_ADDRESS_ASSOCIATIONS,
            EMAILS,
            GROUPED_EMAILS,
            item.mapping.apiSingularName,
        ], client),
        onQueryUpdated(observableQuery) {
            if (
                (observableQuery.queryName === getOperationName(EMAIL_ADDRESS_ASSOCIATIONS)
                    && observableQuery.variables.addresses.includes(address))
                || ([getOperationName(EMAILS), getOperationName(GROUPED_EMAILS)].includes(observableQuery.queryName)
                    && observableQuery.variables.forNode === item.id)
                || (observableQuery.queryName === item.mapping.apiSingularName
                    && observableQuery.variables.id === item.id)
            ) {
                observableQuery.refetch();
            }
        },
    }).catch((error) => {
        if (isValidationError(error)) {
            const messages = error.graphQLErrors[0].extensions.validation;
            if (_.has(messages, 'limit')) {
                warningFeedback({
                    customHeaderPath: 'feedback.responses.addressLimit.header',
                    customMessagePath: 'feedback.responses.addressLimit.message',
                });
            }
        }
        return error;
    });
}

export function associateEmailAddress(address, item, account) {
    return sendEmailAddressAssociationQuery(ASSOCIATE_ITEM_TO_EMAIL_ADDRESS, address, item, account);
}

export function dissociateEmailAddress(address, item, account) {
    return sendEmailAddressAssociationQuery(DISSOCIATE_ITEM_FROM_EMAIL_ADDRESS, address, item, account);
}

function buildDeadlineQuery(mapping) {
    return buildMutationQuery(mapping, 'update', `
    id
    name
    image {
        url
    }
    deadlines {
        startAt
        dueBy
        isCompleted
        status
    }
    `);
}

export function updateDeadlines(form, mapping) {
    return form.graphql(buildDeadlineQuery(mapping));
}

export function completeItem(item, mapping, isCompleted = true) {
    return baseApolloClient().mutate({
        mutation: buildDeadlineQuery(mapping),
        variables: {
            input: {
                id: item.id,
                isCompleted,
            },
        },
    });
}

export function addRelationship(item, child, mapping, relationship) {
    const isToMany = _.endsWith(relationship.type, 'TO_MANY');
    const method = isToMany
        ? `addTo${_.upperFirst(relationship.apiName)}Relationship`
        : `set${_.upperFirst(relationship.apiName)}Relationship`;

    const inputType = isToMany ? 'AddManyRelationshipsInput' : 'AddSingleRelationshipInput';
    const client = baseApolloClient();
    return client.mutate({
        mutation: gql`mutation Add${relationship.id}Relationship($input: ${inputType}!) {
            items {
                ${mapping.apiName} {
                    ${method}(input: $input) {
                        code
                    }
                }
            }
        }`,
        variables: {
            input: {
                itemId: item.id,
                relationshipId: relationship.id,
                [isToMany ? 'ids' : 'id']: isToMany ? [child.id] : child.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            relationship.to.apiName,
            ...(!isToMany ? [relationship.to.apiSingularName] : []),
        ], client),
    }).then((result) => {
        eventBus.dispatch(RELATIONSHIP_ADDED, relationship);
        return result;
    }).catch((e) => {
        const messages = _.get(e, 'graphQLErrors.0.extensions.validation', {});
        if (messages['input.ids']) {
            validationFeedback(messages['input.ids']);
        } else {
            throw e;
        }
    });
}

export function removeRelationship(item, child, mapping, relationship) {
    const isToMany = _.endsWith(relationship.type, 'TO_MANY');
    const method = isToMany
        ? `removeFrom${_.upperFirst(relationship.apiName)}Relationship`
        : `remove${_.upperFirst(relationship.apiName)}Relationship`;

    const inputType = isToMany ? 'RemoveManyRelationshipsInput' : 'RemoveSingleRelationshipInput';

    const input = {
        itemId: item.id,
        relationshipId: relationship.id,
    };
    if (isToMany) {
        input.ids = [child.id];
    }

    const client = baseApolloClient();
    return client.mutate({
        mutation: gql`mutation Remove${relationship.id}Relationship($input: ${inputType}!) {
            items {
                ${mapping.apiName} {
                    ${method}(input: $input) {
                        code
                    }
                }
            }
        }`,
        variables: {
            input,
        },
        refetchQueries: getCachedOperationNames([
            relationship.to.apiName,
            ...(!isToMany ? [relationship.to.apiSingularName] : []),
        ], client),
    }).then((result) => {
        eventBus.dispatch(RELATIONSHIP_REMOVED, relationship);
        return result;
    });
}
