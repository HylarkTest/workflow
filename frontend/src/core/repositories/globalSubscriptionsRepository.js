import { getOperationName } from '@apollo/client/utilities';
import MAPPING_UPDATED from '@/graphql/mappings/subscriptions/MappingUpdated.gql';
import PAGE_UPDATED from '@/graphql/pages/subscriptions/PageUpdated.gql';
import SPACE_UPDATED from '@/graphql/spaces/subscriptions/SpaceUpdated.gql';
import MARKER_GROUP_UPDATED from '@/graphql/markers/subscriptions/MarkerGroupUpdated.gql';
import CATEGORY_UPDATED from '@/graphql/categories/subscriptions/CategoryUpdated.gql';
import ITEM_UPDATED from '@/graphql/items/subscriptions/ItemUpdated.gql';
import NODE_CREATED from '@/graphql/NodeCreated.gql';
import NODE_DELETED from '@/graphql/NodeDeleted.gql';
import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import PAGES from '@/graphql/pages/queries/Pages.gql';
import LINKS from '@/graphql/Links.gql';
import SPACES from '@/graphql/spaces/queries/Spaces.gql';
import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import CATEGORIES from '@/graphql/categories/queries/Categories.gql';
import { getFirstKey } from '@/core/utils.js';

// Subscriptions that will automatically update the cache by themselves
const globalUpdateSubscriptions = [
    MAPPING_UPDATED,
    PAGE_UPDATED,
    SPACE_UPDATED,
    MARKER_GROUP_UPDATED,
    CATEGORY_UPDATED,
    ITEM_UPDATED,
];

// Subscriptions that require a refetch and the queries that need to be refetched
const globalRefreshSubscriptions = {
    mappingCreated: [MAPPINGS],
    mappingDeleted: [MAPPINGS],
    pageCreated: [PAGES, LINKS],
    pageDeleted: [PAGES, LINKS],
    spaceCreated: [SPACES, LINKS],
    spaceDeleted: [SPACES, LINKS],
    markerGroupCreated: [MARKER_GROUPS],
    markerGroupDeleted: [MARKER_GROUPS],
    categoryCreated: [CATEGORIES],
    categoryDeleted: [CATEGORIES],
};

const queryListeners = {};

export function listenToQuery(event, cb) {
    if (!queryListeners[event]) {
        queryListeners[event] = [];
    }
    queryListeners[event].push(cb);
}

export function watchGlobalSubscriptions(client) {
    globalUpdateSubscriptions.forEach((query) => {
        client.subscribe({ query });
    });

    const onEvent = ({ data }) => {
        const eventInfo = getFirstKey(data);
        if (eventInfo) {
            const eventName = eventInfo.event;
            const refetch = globalRefreshSubscriptions[eventName] || [];
            queryListeners[eventInfo.event]?.forEach((cb) => cb(data));
            client.refetchQueries({
                include: refetch.map(getOperationName),
            });
        }
    };
    [NODE_CREATED, NODE_DELETED].forEach((query) => {
        client.subscribe({ query }).subscribe(onEvent);
    });
}
