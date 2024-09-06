import { instantiate } from '@/core/utils.js';
import Mailbox from '@/core/models/Mailbox.js';
import CREATE_MAILBOX from '@/graphql/mail/mutations/CreateMailbox.gql';
import UPDATE_MAILBOX from '@/graphql/mail/mutations/UpdateMailbox.gql';
import DELETE_MAILBOX from '@/graphql/mail/mutations/DeleteMailbox.gql';
import MAILBOXES from '@/graphql/mail/queries/Mailboxes.gql';
import { removeItemFromQueryOffsetCallback } from '@/core/helpers/apolloHelpers.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export function createMailboxFromObject(obj) {
    return instantiate(obj, Mailbox);
}

export function initializeMailboxes(data, originalData) {
    return {
        ...data.mailboxes,
        data: data.mailboxes.data.map((node, index) => createMailboxFromObject({
            ...(originalData?.data[index] || {}),
            ...node,
        })),
    };
}

export function createMailbox(form) {
    return form.graphql(
        CREATE_MAILBOX
    ).then((response) => response.data.createMailbox);
}

export function updateMailbox(form) {
    return form.graphql(
        UPDATE_MAILBOX
    );
}

export function deleteMailbox(mailbox) {
    return baseApolloClient().mutate({
        mutation: DELETE_MAILBOX,
        variables: {
            input: { id: mailbox.id, sourceId: mailbox.account.id },
        },
        update: removeItemFromQueryOffsetCallback(
            { query: MAILBOXES, variables: { sourceId: mailbox.account.id } },
            'mailboxes',
            mailbox.id
        ),
    });
}
