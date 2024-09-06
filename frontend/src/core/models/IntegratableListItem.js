import { startsWith } from 'lodash';
import ListItem from '@/core/models/ListItem.js';

export default class IntegratableListItem extends ListItem {
    isExternalItem() {
        return startsWith(this.__typename, 'External');
    }

    get provider() {
        return this.account?.provider;
    }

    isMicrosoftItem() {
        return this.isExternalItem() && this.provider === 'MICROSOFT';
    }

    isGoogleItem() {
        return this.isExternalItem() && this.provider === 'GOOGLE';
    }

    hasActivity() {
        return !this.isExternalItem();
    }
}
