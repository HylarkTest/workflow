import DOCUMENTS from '@/graphql/documents/queries/Documents.gql';
import GROUPED_DOCUMENTS from '@/graphql/documents/queries/GroupedDocuments.gql';
import UPDATE_DOCUMENT from '@/graphql/documents/mutations/UpdateDocument.gql';
import CREATE_DOCUMENT from '@/graphql/documents/mutations/CreateDocument.gql';
import DELETE_DOCUMENT from '@/graphql/documents/mutations/DeleteDocument.gql';
import MOVE_DOCUMENT from '@/graphql/documents/mutations/MoveDocument.gql';
import DUPLICATE_DOCUMENT from '@/graphql/documents/mutations/DuplicateDocument.gql';
import { instantiate } from '@/core/utils.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Document from '@/core/models/Document.js';
import DRIVES from '@/graphql/documents/queries/Drives.gql';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import DOCUMENT_STATS from '@/graphql/documents/queries/DocumentStats.gql';
import { validationFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export const DOCUMENT_CREATED = Symbol('Document created');
export const DOCUMENT_UPDATED = Symbol('Document updated');
export const DOCUMENT_DELETED = Symbol('Document deleted');

export function createDocumentFromObject(obj) {
    return instantiate(obj, Document);
}

export function initializeDocuments(data) {
    return _.getFirstKey(initializeConnections(data));
}

export function moveDocumentToList(document, list) {
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_DOCUMENT,
        variables: {
            input: {
                driveId: list.id,
                id: document.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            DRIVES,
            DOCUMENTS,
        ], client),
    });
}

export function updateDocument(form) {
    return dispatchPromise(form.graphql(
        UPDATE_DOCUMENT,
        {
            formatData(data) {
                return _.omit(data, ['file']);
            },
        }
    ), DOCUMENT_UPDATED, 'data.updateDocument.document');
}

export function duplicateDocument(document, data) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DUPLICATE_DOCUMENT,
        variables: {
            input: {
                id: document.id,
                ...data,
            },
        },
        refetchQueries: getCachedOperationNames([
            DOCUMENTS,
            GROUPED_DOCUMENTS,
            DRIVES,
            DOCUMENT_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(DOCUMENT_CREATED, result.data.duplicateDocument.document);
        return result;
    });
}

export function changeDrive(document, driveId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_DOCUMENT,
        variables: {
            input: {
                driveId,
                id: document.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            DRIVES,
            DOCUMENTS,
        ], client),
    });
}

function dispatchDocument(form) {
    return dispatchPromise(form.post({
        query: CREATE_DOCUMENT,
        formatData(data) {
            return {
                ...data,
                associations: data.associations?.map((item) => item?.id || item) || [],
                assigneeGroups: (data.assigneeGroups || []).map((assigneeGroup) => ({
                    groupId: assigneeGroup.groupId,
                    assignees: _.map(assigneeGroup.assignees, 'id'),
                })),
            };
        },
        maxUpload: 10,
        refetchQueries: [
            DOCUMENTS,
            GROUPED_DOCUMENTS,
            DRIVES,
            DOCUMENT_STATS,
        ],
    }), DOCUMENT_CREATED, 'data.createDocument.document').catch((e) => {
        const errors = form.errors();
        if (errors.has('file')) {
            validationFeedback(errors.get('file'));
        }
        throw e;
    });
}

export function createDocument(form) {
    const ApolloForm = createApolloForm(
        baseApolloClient(),
        {
            driveId: form.driveId,
            file: form.file,
            associations: form.associations || [],
            assigneeGroups: form.assigneeGroups || [],
        }
    );
    return dispatchDocument(ApolloForm);
}

export function deleteDocument(document) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_DOCUMENT,
        variables: {
            input: {
                id: document.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            DOCUMENTS,
            GROUPED_DOCUMENTS,
            DRIVES,
            DOCUMENT_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(DOCUMENT_DELETED, document);
        return result;
    });
}

export function toggleFavorite(document) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_DOCUMENT,
        variables: {
            input: {
                id: document.id,
                isFavorite: !document.isFavorite,
            },
        },
        optimisticResponse: {
            updateDocument: {
                code: '200',
                document: {
                    ...document,
                    isFavorite: !document.isFavorite,
                },
            },
        },
        refetchQueries: getCachedOperationNames([
            DOCUMENT_STATS,
        ], client),
    });
}
