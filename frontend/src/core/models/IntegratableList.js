import { has, startsWith } from 'lodash';
import List from '@/core/models/List.js';

/**
 * @property {string} account.id
 * @property {string} account.provider
 */
export default class IntegratableList extends List {
    is(model) {
        if (this.isExternalList()) {
            return super.is(model) && this.account.id === model.account.id;
        }
        return super.is(model);
    }

    isExternalList() {
        return startsWith(this.__typename, 'External');
    }

    canBeRenamed() {
        if (!super.canBeRenamed()) {
            return false;
        }
        return !this.isExternalList() || (has(this, 'isOwner') && this.isOwner);
    }

    canBeDeleted() {
        return this.canBeRenamed();
    }

    get provider() {
        return this.account?.provider;
    }

    isMicrosoftList() {
        return this.isExternalList() && this.provider === 'MICROSOFT';
    }

    isGoogleList() {
        return this.isExternalList() && this.provider === 'GOOGLE';
    }

    hasActivity() {
        return !this.isExternalList();
    }
}
