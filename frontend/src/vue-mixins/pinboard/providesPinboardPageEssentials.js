// Requires method called inContextVariables()

import {
    createPinboard,
    createPinboardFromObject,
    deletePinboard,
    initializePinboards,
    movePinboard,
    updatePinboard,
} from '@/core/repositories/pinboardRepository.js';

import { movePinToList } from '@/core/repositories/pinRepository.js';

import PINBOARDS from '@/graphql/pinboard/queries/Pinboards.gql';
import PINBOARD_STATS from '@/graphql/pinboard/queries/PinStats.gql';
import PINS from '@/graphql/pinboard/queries/Pins.gql';
import GROUPED_PINS from '@/graphql/pinboard/queries/GroupedPins.gql';

import PINBOARD_CREATED from '@/graphql/pinboard/subscriptions/PinboardCreated.gql';
import PINBOARD_UPDATED from '@/graphql/pinboard/subscriptions/PinboardUpdated.gql';
import PINBOARD_DELETED from '@/graphql/pinboard/subscriptions/PinboardDeleted.gql';
import PINBOARD_RESTORED from '@/graphql/pinboard/subscriptions/PinboardRestored.gql';
import PINBOARD_MOVED from '@/graphql/pinboard/subscriptions/PinboardMoved.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const subscriptions = [PINBOARD_CREATED, PINBOARD_UPDATED, PINBOARD_DELETED, PINBOARD_RESTORED, PINBOARD_MOVED];

export default {
    mixins: [
        interactsWithApolloQueries,
    ],
    apollo: {
        pinboards: {
            query: PINBOARDS,
            update: initializePinboards,
            variables() {
                return this.contextVariables();
            },
        },
        pinboardStats: {
            query: PINBOARD_STATS,
            variables() {
                return this.contextVariables();
            },
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
            return this.$isLoadingQueriesFirstTime(['pinboards']);
        },
        isLoadingStats() {
            return this.$isLoadingQueriesFirstTime(['pinboardStats']);
        },
        sourceLists() {
            return this.pinboards?.map((pinboard) => {
                return {
                    ...pinboard.space,
                    lists: pinboard.lists || [],
                };
            });
        },
    },
    created() {
        this.deleteListFunction = deletePinboard;
        this.createListFromObjectFunction = createPinboardFromObject;
        this.updateListFunction = updatePinboard;
        this.createListFunction = createPinboard;
        this.moveListFunction = movePinboard;
        this.moveItemToListFunction = movePinToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [PINBOARDS, PINBOARD_STATS, PINS, GROUPED_PINS];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [PINBOARD_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
