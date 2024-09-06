import { getEmailIcon } from '@/core/display/emailFolderIcons.js';
import IntegratableList from '@/core/models/IntegratableList.js';

/**
 * @property {int} unseenCount
 * @property {int} total
 * @property {boolean} isCollapsed
 * @property {boolean} isDefault
 */
export default class Mailbox extends IntegratableList {
    // eslint-disable-next-line class-methods-use-this
    isExternalList() {
        return true;
    }

    route() {
        return { name: 'emails', params: { providerId: this.account.id, listId: this.id } };
    }

    get icon() {
        return getEmailIcon(this.name);
    }

    get count() {
        return /\bdrafts?\b/i.test(this.name)
            ? this.total
            : this.unseenCount;
    }
}
