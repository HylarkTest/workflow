import CLEAR_ALL_NOTIFICATIONS from '@/graphql/notifications/mutations/ClearAllNotifications.gql';
import CLEAR_NOTIFICATION from '@/graphql/notifications/mutations/ClearNotification.gql';
import UNCLEAR_NOTIFICATION from '@/graphql/notifications/mutations/UnclearNotification.gql';
import MARK_NOTIFICATIONS_AS_SEEN from '@/graphql/notifications/mutations/MarkNotificationsAsSeen.gql';
import NOTIFICATIONS from '@/graphql/notifications/queries/Notifications.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { instantiate } from '@/core/utils.js';
import { setLastSeenNotificationsToNow } from '@/core/repositories/preferencesRepository.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export function createNotificationFromObject(obj) {
    return instantiate(obj);
}

export function initializeNotifications(data) {
    return initializeConnections(data);
}

export function clearNotification(notification) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: CLEAR_NOTIFICATION,
        variables: {
            input: { id: notification.id },
        },
        refetchQueries: getCachedOperationNames([NOTIFICATIONS], client),
    });
}

export function unclearNotification(notification) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UNCLEAR_NOTIFICATION,
        variables: {
            input: { id: notification.id },
        },
        refetchQueries: getCachedOperationNames([NOTIFICATIONS], client),
    });
}

export function clearAllNotifications() {
    const client = baseApolloClient();
    return client.mutate({
        mutation: CLEAR_ALL_NOTIFICATIONS,
        refetchQueries: getCachedOperationNames([NOTIFICATIONS], client),
    });
}

export async function markNotificationsAsSeen() {
    const response = await baseApolloClient().mutate({
        mutation: MARK_NOTIFICATIONS_AS_SEEN,
    });

    setLastSeenNotificationsToNow();

    return response;
}
