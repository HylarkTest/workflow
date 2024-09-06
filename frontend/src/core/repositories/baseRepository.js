import { ref, readonly } from 'vue';
import axios from 'axios';
import { isString } from 'lodash';
import Form from 'formla';
import { gql } from '@apollo/client';
import { defaultOptions, defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';
import { awaitCall } from '@/core/utils.js';
import BASES from '@/graphql/bases/Bases.gql';
import UPDATE_BASE from '@/graphql/bases/UpdateBase.gql';
import DELETE_BASE from '@/graphql/bases/DeleteBase.gql';
import LEAVE_BASE from '@/graphql/bases/LeaveBase.gql';
import MEMBER_FRAGMENT from '@/graphql/MemberFragment.gql';
import UPDATE_PROFILE from '@/graphql/bases/UpdateProfile.gql';
import BASE_UPDATED from '@/graphql/bases/BaseUpdated.gql';
import BASE_DELETED from '@/graphql/bases/BaseDeleted.gql';
import { createApolloClient } from '@/http/apollo/graphqlClient.js';
import app from '@/app.js';
import { watchGlobalSubscriptions } from '@/core/repositories/globalSubscriptionsRepository.js';

export function baseApolloClient() {
    return app.config.globalProperties.$apolloProvider.defaultClient;
}

let watchBasesObserver;
const basesRef = ref([]);

export const bases = readonly(basesRef);

export function activeBase() {
    return bases.value.find((base) => base.isActive)?.node;
}

export function isActiveBaseCollaborative() {
    return activeBase().baseType === 'COLLABORATIVE';
}

export function isActiveBasePersonal() {
    return activeBase().baseType === 'PERSONAL';
}

// eslint-disable-next-line no-unused-vars
export default function watchBases(next) {
    const client = defaultApolloClient();

    watchBasesObserver = client.watchQuery({ query: BASES });
    watchBasesObserver.subscribe({ next });

    watchBasesObserver.subscribeToMore({
        document: BASE_UPDATED,
    });
    watchBasesObserver.subscribeToMore({
        document: BASE_DELETED,
        updateQuery(_, { subscriptionData: { data } }) {
            if (data?.baseDeleted) {
                watchBasesObserver.refetch();
                if (data.baseDeleted.base.id === activeBase().id) {
                    // eslint-disable-next-line no-use-before-define
                    switchDefaultBase(data.baseDeleted.activeBase.node.id);
                }
            }
        },
    });
}

const clientsWithInitializedSubscriptions = {};

function switchDefaultBase(id) {
    const provider = app.config.globalProperties.$apolloProvider;
    const client = provider.clients[id];
    provider.defaultClient = client;
    provider.clients.default = client;
    if (!clientsWithInitializedSubscriptions[id]) {
        watchGlobalSubscriptions(client);
        clientsWithInitializedSubscriptions[id] = true;
    }
}

// When bases are loaded we want to create a new apollo client for each base
// this will mean they all have separate cache's and can be used independently
// without the need to reset every time the user wants to switch, opening up
// the potential for bugs.
// Each client is essentially the same, the only different being that we add
// a header to each request with the base id.
export function loadBases() {
    return new Promise((resolve) => {
        if (basesRef.value.length) {
            resolve();
        } else {
            watchBases((sub) => {
                basesRef.value = sub.data.bases;
                const provider = app.config.globalProperties.$apolloProvider;
                const originalClients = { ...provider.clients };
                const clients = provider.clients;
                for (const key in clients) {
                    if (key !== 'default' && key !== 'defaultClient') {
                        delete clients[key];
                    }
                }
                sub.data.bases.forEach((base) => {
                    if (originalClients[base.node.id]) {
                        clients[base.node.id] = originalClients[base.node.id];
                    } else {
                        clients[base.node.id] = createApolloClient({
                            ...defaultOptions,
                            httpLinkOptions: {
                                ...defaultOptions.httpLinkOptions,
                                fetch(url, options) {
                                    return defaultOptions.httpLinkOptions.fetch(url, {
                                        ...options,
                                        headers: {
                                            ...options.headers,
                                            'X-Base-Id': base.node.id,
                                        },
                                    });
                                },
                            },
                        }).apolloClient;
                    }
                    if (base.isActive) {
                        switchDefaultBase(base.node.id);
                    }
                });
                resolve();
            });
        }
    });
}

export function reloadBases() {
    if (watchBasesObserver) {
        return watchBasesObserver.refetch();
    }
    return loadBases();
}

const nextBaseRef = ref(null);
export const nextBase = readonly(nextBaseRef);

// This makes sure that even if the user navigates through routes for multiple
// bases in quick succession, it will avoid any race conditions with some bases
// being loaded and activated after the latest base and overriding.
export const switchToBase = awaitCall(async (base) => {
    const id = isString(base) ? base : base.id;
    if (!nextBaseRef.value && activeBase() && activeBase().id === id) {
        return;
    }

    const localSwitchingRef = Symbol('switching');
    nextBaseRef.value = localSwitchingRef;
    try {
        await axios.put(`/switch-base/${id}`);
        switchDefaultBase(id);
        await reloadBases();
    } finally {
        if (nextBaseRef.value === localSwitchingRef) {
            nextBaseRef.value = null;
        }
    }
});

export function switchingBases() {
    return !!nextBase.value;
}

export function updateBase(form) {
    return form.post({ query: UPDATE_BASE });
}

export const updateName = updateBase;
export const updateDescription = updateBase;
export const updateImage = updateBase;

export function updateAccentColor(accentColor) {
    return defaultApolloClient().mutate({
        mutation: gql`mutation($input: UpdateBaseInput!) {
            updateBase(input: $input) {
                base { node { id preferences { accentColor } } }
            }
        }`,
        variables: { input: { accentColor } },
        optimisticResponse: {
            updateBase: {
                base: {
                    node: {
                        __typename: 'Base',
                        id: activeBase().id,
                        preferences: { accentColor },
                    },
                },
            },
        },
    });
}

export async function deleteBase() {
    const response = await baseApolloClient().mutate({
        mutation: DELETE_BASE,
    });
    switchDefaultBase(response.data.deleteBase.activeBase.node.id);
    await reloadBases();
}

export async function leaveBase() {
    const response = await baseApolloClient().mutate({
        mutation: LEAVE_BASE,
    });
    switchDefaultBase(response.data.leaveBase.activeBase.node.id);
    await reloadBases();
}

export async function inviteMember(form) {
    await form.post('/member-invite');
    defaultApolloClient().refetchQueries({ include: ['BaseWithInvites'] });
}

export async function updateInvite(form) {
    await form.put(`/member-invite/${form.email}`);
    defaultApolloClient().refetchQueries({ include: ['BaseWithInvites'] });
}

export function updateRole(form) {
    return form.post({
        query: gql`mutation($input: UpdateMemberInput!) {
            updateMember(input: $input) {
                base {
                    id
                    node {
                        id
                        members {
                            id
                            role
                        }
                    }
                    role
                }
            }
        }`,
    });
}

export function removeMember(id) {
    return defaultApolloClient().mutate({
        mutation: gql`
        mutation($input: DeleteMemberInput!) {
            deleteMember(input: $input) {
                base {
                    node {
                        id
                        members {
                            ...MemberFragment
                        }
                    }
                }
            }
        }
        ${MEMBER_FRAGMENT}
        `,
        variables: { input: { id } },
    });
}

export async function removeInvite(email) {
    await axios.delete(`/member-invite/${email}`, { headers: { 'X-Base-Id': activeBase().id } });
    defaultApolloClient().refetchQueries({ include: ['BaseWithInvites'] });
}

export async function createBase(structure) {
    const response = await (new Form(structure)).post('/base');
    await reloadBases();

    const baseId = response.data.data.id;
    await switchDefaultBase(baseId);
}

export function updateProfile(form) {
    return form.post({ query: UPDATE_PROFILE }, { client: 'defaultClient' });
}

export function assigneeGroups() {
    return activeBase().assigneeGroups;
}
