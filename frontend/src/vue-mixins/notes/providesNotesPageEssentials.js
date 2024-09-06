// Requires method called inContextVariables()

import {
    createNotebook,
    createNotebookFromObject,
    deleteNotebook,
    initializeNotebooks,
    moveNotebook,
    updateNotebook,
} from '@/core/repositories/notebookRepository.js';

import { moveNoteToList } from '@/core/repositories/noteRepository.js';

import NOTEBOOKS from '@/graphql/notes/queries/Notebooks.gql';
import NOTE_STATS from '@/graphql/notes/queries/NoteStats.gql';
import NOTES from '@/graphql/notes/queries/Notes.gql';
import GROUPED_NOTES from '@/graphql/notes/queries/GroupedNotes.gql';

import NOTEBOOK_CREATED from '@/graphql/notes/subscriptions/NotebookCreated.gql';
import NOTEBOOK_UPDATED from '@/graphql/notes/subscriptions/NotebookUpdated.gql';
import NOTEBOOK_DELETED from '@/graphql/notes/subscriptions/NotebookDeleted.gql';
import NOTEBOOK_MOVED from '@/graphql/notes/subscriptions/NotebookMoved.gql';
import NOTEBOOK_RESTORED from '@/graphql/notes/subscriptions/NotebookRestored.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const subscriptions = [
    NOTEBOOK_CREATED,
    NOTEBOOK_UPDATED,
    NOTEBOOK_DELETED,
    NOTEBOOK_MOVED,
    NOTEBOOK_RESTORED,
];

export default {
    mixins: [
        interactsWithApolloQueries,
    ],
    apollo: {
        notebooks: {
            query: NOTEBOOKS,
            update: initializeNotebooks,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
        noteStats: {
            query: NOTE_STATS,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
        };
    },
    computed: {
        isLoading() {
            return this.isLoadingLists || this.isLoadingStats;
        },
        isLoadingLists() {
            return this.$isLoadingQueriesFirstTime(['notebooks']);
        },
        isLoadingStats() {
            return this.$isLoadingQueriesFirstTime(['noteStats']);
        },
        sourceLists() {
            return this.notebooks?.map((notebook) => {
                return {
                    ...notebook.space,
                    lists: notebook.lists || [],
                };
            });
        },
    },
    created() {
        this.deleteListFunction = deleteNotebook;
        this.createListFromObjectFunction = createNotebookFromObject;
        this.updateListFunction = updateNotebook;
        this.createListFunction = createNotebook;
        this.moveListFunction = moveNotebook;
        this.moveItemToListFunction = moveNoteToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [NOTEBOOKS, NOTE_STATS, NOTES, GROUPED_NOTES];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [NOTEBOOK_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
