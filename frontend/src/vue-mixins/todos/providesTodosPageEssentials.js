// Requires method called inContextVariables()

import interactsWithIntegratedData from '@/vue-mixins/interactsWithIntegratedData.js';

import {
    createTodoList,
    createTodoListFromObject,
    deleteTodoList,
    initializeTodoLists,
    moveTodoList,
    updateTodoList,
} from '@/core/repositories/todoListRepository.js';

import { moveTodoToList } from '@/core/repositories/todoRepository.js';

import TODO_LISTS from '@/graphql/todos/queries/TodoLists.gql';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import TODO_STATS from '@/graphql/todos/queries/TodoStats.gql';
import TODOS from '@/graphql/todos/queries/Todos.gql';
import GROUPED_TODOS from '@/graphql/todos/queries/GroupedTodos.gql';

import TODOLIST_CREATED from '@/graphql/todos/subscriptions/TodoListCreated.gql';
import TODOLIST_UPDATED from '@/graphql/todos/subscriptions/TodoListUpdated.gql';
import TODOLIST_DELETED from '@/graphql/todos/subscriptions/TodoListDeleted.gql';
import TODOLIST_RESTORED from '@/graphql/todos/subscriptions/TodoListRestored.gql';
import TODOLIST_MOVED from '@/graphql/todos/subscriptions/TodoListMoved.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';

const subscriptions = [TODOLIST_CREATED, TODOLIST_UPDATED, TODOLIST_DELETED, TODOLIST_RESTORED, TODOLIST_MOVED];

export default {
    mixins: [
        interactsWithIntegratedData,
    ],
    apollo: {
        todoLists: {
            query: TODO_LISTS,
            update: initializeTodoLists,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
        todoStats: {
            query: TODO_STATS,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            currentView: {
                id: 'LINE',
                viewType: 'LINE',
                categoryType: 'DASHBOARD',
            },
            scope: 'TODOS',
        };
    },
    computed: {
        isLoading() {
            return this.isLoadingLists || this.isLoadingStats;
        },
        isLoadingLists() {
            // Ideally we don't want to have to wait for integrations to load
            // as they may take a while, but in order for the router to work
            // if they are following a link to an integrated list, then we need
            // to wait. If setDisplayedList can be updated we may be able to
            // remove this.
            return this.$isLoadingQueriesFirstTime(['todoLists'])
               || this.isLoadingInitialIntegrations;
        },
        isLoadingStats() {
            return this.$isLoadingQueriesFirstTime(['todoStats']);
        },
        sourceLists() {
            return this.todoLists?.map((list) => {
                return {
                    ...list.space,
                    lists: list.lists || [],
                };
            });
        },
        sources() {
            return {
                spaces: this.sourceLists,
                integrations: (this.integrationsForScope || []).map((integration) => {
                    return {
                        name: integration.accountName,
                        id: integration.id,
                        provider: integration.provider,
                        renewalUrl: this.renewals[integration.id] || null,
                        lists: this.integrationLists[integration.id]?.data || [],
                    };
                }),
            };
        },
    },
    methods: {
        // When a todo is dragged into a list the list emits an event and we add
        // it to the TodoMain component, this is just temporary as it will be
        // handled by apollo when the back end is hooked up. For now the count
        // shows the wrong number when moving an item into the same list.
        // moveTodo(event) {
        //     return moveTodoToList(event.todo, event.list);
        // },
    },
    watch: {
        integrationsForScope() {
            this.createIntegrationSmartQueries(
                EXTERNAL_TODO_LISTS,
                initializeTodoLists
            );
        },
    },
    created() {
        this.deleteListFunction = deleteTodoList;
        this.createListFromObjectFunction = createTodoListFromObject;
        this.updateListFunction = updateTodoList;
        this.createListFunction = createTodoList;
        this.moveListFunction = moveTodoList;
        this.moveItemToListFunction = moveTodoToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [TODO_LISTS, TODO_STATS, TODOS, GROUPED_TODOS];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [TODOLIST_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
