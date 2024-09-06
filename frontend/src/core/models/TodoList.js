// import { defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';
// import TODO_LIST_FRAGMENT from '@/graphql/todos/TodoListFragment.gql';

import IntegratableList from '@/core/models/IntegratableList.js';

/**
 * @property {number} order
 * @property {number} count
 * @property {boolean} isDefault
 * @property {boolean} isOwner
 * @property {boolean} isShared
 */
export default class TodoList extends IntegratableList {
    hasTodoCount() {
        return !this.isExternalList();
    }

    canBeMoved() {
        return !this.isExternalList();
    }

    route() {
        return { name: 'todos', params: { listId: this.id, providerId: this.account?.id } };
    }

    calendarRoute() {
        return { name: 'calendar', params: { listId: this.id, providerId: this.account?.id } };
    }

    // watch() {
    //     if (!this.isExternalList()) {
    //         const client = baseApolloClient();
    //         client.cache.watch({
    //             id: `TodoList:${this.id}`,
    //             query: TODO_LIST_FRAGMENT,
    //             callback: (...args) => console.log(args),
    //         });
    //     }
    // }
}
