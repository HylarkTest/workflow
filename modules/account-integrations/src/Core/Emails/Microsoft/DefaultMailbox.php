<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Microsoft;

// The commented mailboxes are the ones listed in the docs but return 404s when
// trying to access them.
enum DefaultMailbox: string
{
    case ARCHIVE = 'archive';
    case CLUTTER = 'clutter';
    //    case CONFLICTS = 'conflicts';
    case CONVERSATION_HISTORY = 'conversationhistory';
    case DELETED_ITEMS = 'deleteditems';
    case DRAFTS = 'drafts';
    case INBOX = 'inbox';
    case JUNK_EMAIL = 'junkemail';
    //    case LOCAL_FAILURES = 'localfailures';
    case MSG_FOLDER_ROOT = 'msgfolderroot';
    case OUTBOX = 'outbox';
    case RECOVERABLE_ITEMS_DELETIONS = 'recoverableitemsdeletions';
    case SCHEDULED = 'scheduled';
    case SEARCH_FOLDERS = 'searchfolders';
    case SENT_ITEMS = 'sentitems';
    //    case SERVER_FAILURES = 'serverfailures';
    //    case SYNC_ISSUES = 'syncissues';

    public static function commonMailboxes(): array
    {
        return [
            self::INBOX,
            self::DRAFTS,
            self::SENT_ITEMS,
            self::ARCHIVE,
            self::DELETED_ITEMS,
            self::JUNK_EMAIL,
        ];
    }
}
