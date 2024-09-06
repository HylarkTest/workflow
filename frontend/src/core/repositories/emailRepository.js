import { instantiate } from '@/core/utils.js';
import Email from '@/core/models/Email.js';
import CREATE_EMAIL from '@/graphql/mail/mutations/CreateEmail.gql';
import DELETE_EMAIL from '@/graphql/mail/mutations/DeleteEmail.gql';
import UPDATE_EMAIL from '@/graphql/mail/mutations/UpdateEmail.gql';
import EMAILS from '@/graphql/mail/queries/Emails.gql';
import GROUPED_EMAILS from '@/graphql/mail/queries/GroupedEmails.gql';
import MAILBOXES_WITH_COUNTS from '@/graphql/mail/queries/MailboxesWithCounts.gql';
import {
    getCachedOperationNames,
    removeNodeFromQueryConnectionCallback,
} from '@/core/helpers/apolloHelpers.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';

function restoreInlineAttachments(body, attachments) {
    let content;
    let regex;
    const isObject = _.isObject(body);
    if (isObject) {
        content = JSON.stringify(body);
        regex = /("image":")([^"]+)/g;
    } else {
        content = body;
        regex = /(<img[^>]+src=")([^"]+)/g;
    }

    content = content.replaceAll(regex, (match, p1, p2) => {
        const attachment = _.find(attachments, { link: p2 });
        return attachment ? `${p1}cid:${attachment.contentId}` : match;
    });

    return isObject ? JSON.parse(content) : content;
}

export function createEmailFromObject(obj) {
    return instantiate(obj, Email);
}

export function initializeEmails(data) {
    return initializeConnections(data, createEmailFromObject, false, true).emails;
}

export function createEmail(form) {
    return form.graphql(
        CREATE_EMAIL,
        {
            formatData(data) {
                return {
                    ...data,
                    html: data.html ? restoreInlineAttachments(data.html, data.attachments) : null,
                    delta: data.delta ? restoreInlineAttachments(data.delta, data.attachments) : null,
                    to: _.map(data.to, 'email'),
                    cc: _.map(data.cc, 'email'),
                    bcc: _.map(data.bcc, 'email'),
                    isDraft: false,
                    associations: data.associations.map((item) => item?.id || item),
                };
            },
            refetchQueries: getCachedOperationNames([
                EMAILS,
                GROUPED_EMAILS,
                MAILBOXES_WITH_COUNTS,
            ], form._apolloClient),
        }
    );
}

export function saveDraft(form) {
    return form.graphql(
        CREATE_EMAIL,
        {
            formatData(data) {
                return {
                    ...data,
                    html: data.html ? restoreInlineAttachments(data.html, data.attachments) : null,
                    delta: data.delta ? restoreInlineAttachments(data.delta, data.attachments) : null,
                    to: _.map(data.to, 'email'),
                    cc: _.map(data.cc, 'email'),
                    bcc: _.map(data.bcc, 'email'),
                    isDraft: true,
                    associations: data.associations.map((item) => item?.id || item),
                };
            },
            refetchQueries: getCachedOperationNames([
                EMAILS,
                GROUPED_EMAILS,
                MAILBOXES_WITH_COUNTS,
            ], form._apolloClient),
        }
    ).then(_.property('data.createEmail.email'));
}

export function deleteEmail(sourceId, mailboxId, id) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_EMAIL,
        variables: {
            input: { id, mailboxId, sourceId },
        },
        update: removeNodeFromQueryConnectionCallback(
            { query: EMAILS, variables: { mailboxId, sourceId } },
            'emails',
            id
        ),
        refetchQueries: getCachedOperationNames([
            EMAILS,
            GROUPED_EMAILS,
            MAILBOXES_WITH_COUNTS,
        ], client),
    }).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        client.refetchQueries({ include: [EMAILS, GROUPED_EMAILS, MAILBOXES_WITH_COUNTS] });
        return false;
    });
}

export function toggleEmailFlag(email, isFlagged) {
    return baseApolloClient().mutate({
        mutation: UPDATE_EMAIL,
        variables: {
            input: {
                id: email.id, mailboxId: email.mailbox.id, sourceId: email.account.id, isFlagged,
            },
        },
    });
}

export function flagEmail(email) {
    return toggleEmailFlag(email, true);
}

export function unFlagEmail(email) {
    return toggleEmailFlag(email, false);
}

export function toggleEmailSeen(email, isSeen) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_EMAIL,
        variables: {
            input: {
                id: email.id, mailboxId: email.mailbox.id, sourceId: email.account.id, isSeen,
            },
        },
        refetchQueries: getCachedOperationNames([
            MAILBOXES_WITH_COUNTS,
        ], client),
    });
}

export function markEmailRead(email) {
    if (!email.isSeen) {
        return toggleEmailSeen(email, true);
    }
    return null;
}

export function markEmailUnread(email) {
    if (email.isSeen) {
        return toggleEmailSeen(email, false);
    }
    return null;
}
