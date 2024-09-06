import axios from 'axios';
import { defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';

import ME from '@/graphql/Me.gql';
import ME_UPDATED from '@/graphql/MeUpdated.gql';
import UPDATE_USER from '@/graphql/settings/UpdateUser.gql';
import { instantiate } from '@/core/utils.js';
import User from '@/core/models/User.js';

let watchUserObserver;

// eslint-disable-next-line no-unused-vars
export default function watchUser(next, error, force = false, options = {}) {
    const client = defaultApolloClient();
    if (force && watchUserObserver) {
        watchUserObserver.refetch();
    }

    watchUserObserver = client.watchQuery({ query: ME, ...options });
    watchUserObserver.subscribe({
        next: ({ data, ...subOptions }) => {
            next({ data: { user: instantiate(data.user, User) }, ...subOptions });
        },
        error,
    });
    watchUserObserver.subscribeToMore({
        document: ME_UPDATED,
        onError: error,
    });
}

export function reloadUser() {
    if (watchUserObserver) {
        return watchUserObserver.refetch();
    }
    return Promise.resolve();
}

export function updateFullName(form) {
    return form.graphql(UPDATE_USER);
}

export function updateAvatar(form) {
    return form.post({ query: UPDATE_USER });
}

export function deleteAccount() {
    return axios.delete('/account');
}
