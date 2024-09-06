import _ from 'lodash';
import Model from '@/core/models/Model.js';
import { bases } from '@/core/repositories/baseRepository.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {string} email
 * @property {string} avatar
 * @property {boolean} verified
 * @property {boolean} finishedRegistration
 * @property {number} newNotificationsCount
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} __typename
 * @property {Array} bases
 */
export default class User extends Model {
    // eslint-disable-next-line class-methods-use-this
    allBases() {
        return bases.value.map((baseEdge) => ({
            ...baseEdge.node,
            pivot: _.omit(baseEdge, 'node'),
        }));
    }

    activeBase() {
        return this.allBases().find((base) => base.pivot.isActive);
    }

    get activeBaseMemberId() {
        return this.activeBase().pivot.id;
    }

    personalBase() {
        return this.allBases().find((base) => base.baseType === 'PERSONAL');
    }

    baseSpecificPreferences() {
        return this.activeBase().pivot.preferences;
    }

    role(base = null) {
        let baseWithPivot = this.activeBase();
        if (base) {
            baseWithPivot = this.allBases().find((b) => b.id === base.id);
        }
        return baseWithPivot.pivot.role;
    }

    isOwner(base = null) {
        return this.role(base) === 'OWNER';
    }

    isAdmin(base = null) {
        return this.role(base) === 'ADMIN';
    }

    isOwnerOrAdmin(base = null) {
        const role = this.role(base);
        return role === 'ADMIN' || role === 'OWNER';
    }
}
