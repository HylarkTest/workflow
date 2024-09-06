// Provides the icons for email folders

export const emailIcons = {
    BIN: 'fa-trash-can-list',
    FOLDER: 'fa-folder-closed',
    DRAFT: 'fa-pencil',
    STARRED: 'fa-star',
    INBOX: 'fa-inbox-full',
    SENT: 'fa-paper-plane-top',
    IMPORTANT: 'fa-bookmark fa-rotate-90',
    ARCHIVE: 'fa-box-archive',
    SPAM: 'fa-ban',
};

function getKeyWord(id) {
    const val = id.toLowerCase();
    switch (true) {
    case /\b(spam|junk)\b/i.test(val):
        return 'SPAM';
    case /\binbox\b/i.test(val):
        return 'INBOX';
    case /\b(deleted?|bin|trash)\b/i.test(val):
        return 'BIN';
    case /\bsent\b/i.test(val):
        return 'SENT';
    case /\bimportant\b/i.test(val):
        return 'IMPORTANT';
    case /\barchive\b/i.test(val):
        return 'ARCHIVE';
    case /\bstarred\b/i.test(val):
        return 'STARRED';
    case /\bdrafts?\b/i.test(val):
        return 'DRAFT';
    default:
        return 'FOLDER';
    }
}

export function getEmailIcon(id) {
    const keyWord = getKeyWord(id);
    return emailIcons[keyWord];
}

export default { emailIcons, getEmailIcon };
