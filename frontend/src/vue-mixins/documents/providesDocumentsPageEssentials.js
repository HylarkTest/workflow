// Requires method called inContextVariables()

import AttachmentNew from '@/components/documents/AttachmentNew.vue';

import {
    createDrive,
    createDriveFromObject,
    deleteDrive,
    initializeDrives,
    moveDrive,
    updateDrive,
} from '@/core/repositories/driveRepository.js';

import { moveDocumentToList } from '@/core/repositories/documentRepository.js';

import DRIVES from '@/graphql/documents/queries/Drives.gql';
import DOCUMENT_STATS from '@/graphql/documents/queries/DocumentStats.gql';
import DOCUMENTS from '@/graphql/documents/queries/Documents.gql';
import GROUPED_DOCUMENTS from '@/graphql/documents/queries/GroupedDocuments.gql';

import DRIVE_CREATED from '@/graphql/documents/subscriptions/DriveCreated.gql';
import DRIVE_UPDATED from '@/graphql/documents/subscriptions/DriveUpdated.gql';
import DRIVE_DELETED from '@/graphql/documents/subscriptions/DriveDeleted.gql';
import DRIVE_MOVED from '@/graphql/documents/subscriptions/DriveMoved.gql';
import DRIVE_RESTORED from '@/graphql/documents/subscriptions/DriveRestored.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const subscriptions = [DRIVE_CREATED, DRIVE_UPDATED, DRIVE_DELETED, DRIVE_MOVED, DRIVE_RESTORED];

export default {
    mixins: [
        interactsWithApolloQueries,
    ],
    components: {
        AttachmentNew,
    },
    apollo: {
        drives: {
            query: DRIVES,
            update: initializeDrives,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
        documentStats: {
            query: DOCUMENT_STATS,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            processingNew: false,
            displayedList: null,
            documentFormProps: null,
        };
    },
    computed: {
        isLoading() {
            return this.isLoadingLists || this.isLoadingStats;
        },
        isLoadingLists() {
            return this.$isLoadingQueriesFirstTime(['drives']);
        },
        isLoadingStats() {
            return this.$isLoadingQueriesFirstTime(['documentStats']);
        },
        sourceLists() {
            return this.drives?.map((drive) => {
                return {
                    ...drive.space,
                    lists: drive.lists || [],
                };
            });
        },
        defaultAssociations() {
            return null; // Set in component if there are any
        },
    },
    created() {
        this.deleteListFunction = deleteDrive;
        this.createListFromObjectFunction = createDriveFromObject;
        this.updateListFunction = updateDrive;
        this.createListFunction = createDrive;
        this.moveListFunction = moveDrive;
        this.moveItemToListFunction = moveDocumentToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [DRIVES, DOCUMENT_STATS, DOCUMENTS, GROUPED_DOCUMENTS];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [DRIVE_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
