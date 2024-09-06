import IntegratableListItem from '@/core/models/IntegratableListItem.js';

function recipientAddress(recipient) {
    return recipient?.address || '';
}

function recipientName(recipient) {
    return recipient?.name || recipientAddress(recipient);
}

/**
 * @property {string} id
 * @property {string} subject
 * @property {string} from
 * @property {array} to
 * @property {array} cc
 * @property {array} bcc
 * @property {string} preview
 * @property {string} html
 * @property {string} text
 * @property {boolean} hasAttachments
 * @property {string} createdAt
 * @property {string} account.id
 * @property {string} account.provider
 * @property {string} account.accountName
 * @property {string} __typename
 */
export default class Email extends IntegratableListItem {
    fromName() {
        return recipientName(this.from);
    }

    toNames() {
        return this.to.map(recipientName);
    }

    isFromAccountOwner() {
        return recipientAddress(this.from) === this.account.accountName;
    }

    correspondentsAll() {
        return (this.from ? [this.from] : []).concat(this.to).concat(this.cc).concat(this.bcc);
    }

    correspondents() {
        return this.correspondentsAll().filter((correspondent) => {
            return correspondent.address !== this.account.accountName;
        });
    }

    correspondentNames() {
        return this.correspondents().map(recipientName);
    }

    correspondentAddresses() {
        return this.correspondents().map((person) => {
            return person.address;
        });
    }

    // eslint-disable-next-line class-methods-use-this
    isExternalItem() {
        return true;
    }

    htmlWithInlineAttachments() {
        return this.html.replaceAll(/(<img[^>]+src=")(cid:)([^"]+)/g, (match, p1, _p2, p3) => {
            const attachment = _.find(this.attachments, { contentId: p3 });
            return attachment ? `${p1}${attachment.link}` : match;
        });
    }
}
