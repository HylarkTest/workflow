// Requires method called inContextVariables()

import {
    createLinkList,
    createLinkListFromObject,
    deleteLinkList,
    initializeLinkLists,
    moveLinkList,
    updateLinkList,
} from '@/core/repositories/linkListRepository.js';

import { moveLinkToList } from '@/core/repositories/linkRepository.js';

import LISTS from '@/graphql/links/queries/LinkLists.gql';
import LINK_STATS from '@/graphql/links/queries/LinkStats.gql';
import LINKS from '@/graphql/links/queries/Links.gql';
import GROUPED_LINKS from '@/graphql/links/queries/GroupedLinks.gql';

import LINKLIST_CREATED from '@/graphql/links/subscriptions/LinkListCreated.gql';
import LINKLIST_UPDATED from '@/graphql/links/subscriptions/LinkListUpdated.gql';
import LINKLIST_DELETED from '@/graphql/links/subscriptions/LinkListDeleted.gql';
import LINKLIST_MOVED from '@/graphql/links/subscriptions/LinkListMoved.gql';
import LINKLIST_RESTORED from '@/graphql/links/subscriptions/LinkListRestored.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const subscriptions = [LINKLIST_CREATED, LINKLIST_UPDATED, LINKLIST_DELETED, LINKLIST_MOVED, LINKLIST_RESTORED];

export default {
    mixins: [
        interactsWithApolloQueries,
    ],
    apollo: {
        lists: {
            query: LISTS,
            update: initializeLinkLists,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
        linkStats: {
            query: LINK_STATS,
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
            return this.$isLoadingQueriesFirstTime(['lists']);
        },
        isLoadingStats() {
            return this.$isLoadingQueriesFirstTime(['linkStats']);
        },
        sourceLists() {
            return this.lists?.map((list) => {
                return {
                    ...list.space,
                    lists: list.lists || [],
                };
            });
        },
    },
    created() {
        this.deleteListFunction = deleteLinkList;
        this.createListFromObjectFunction = createLinkListFromObject;
        this.updateListFunction = updateLinkList;
        this.createListFunction = createLinkList;
        this.moveListFunction = moveLinkList;
        this.moveItemToListFunction = moveLinkToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [LISTS, LINK_STATS, LINKS, GROUPED_LINKS];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [LINKLIST_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
