import Model from '@/core/models/Model.js';

/**
 * @property {string} name
 * @property {boolean} isDefault
 */
export default class List extends Model {
    canBeRenamed() {
        return !this.isDefault;
    }

    canBeDeleted() {
        return this.canBeRenamed();
    }

    // eslint-disable-next-line class-methods-use-this
    isExternalList() {
        return false;
    }
}
