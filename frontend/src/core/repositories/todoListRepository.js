import dayjs from '@/core/plugins/initDayjs.js';

import UPDATE_TODO_LIST from '@/graphql/todos/mutations/UpdateTodoList.gql';
import UPDATE_EXTERNAL_TODO_LIST from '@/graphql/todos/mutations/UpdateExternalTodoList.gql';
import DELETE_TODO_LIST from '@/graphql/todos/mutations/DeleteTodoList.gql';
import DELETE_EXTERNAL_TODO_LIST from '@/graphql/todos/mutations/DeleteExternalTodoList.gql';
import TODO_LISTS from '@/graphql/todos/queries/TodoLists.gql';
import TODO_STATS from '@/graphql/todos/queries/TodoStats.gql';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import MOVE_TODO_LIST from '@/graphql/todos/mutations/MoveTodoList.gql';
import CREATE_TODO_LIST from '@/graphql/todos/mutations/CreateTodoList.gql';
import CREATE_EXTERNAL_TODO_LIST from '@/graphql/todos/mutations/CreateExternalTodoList.gql';
import TodoList from '@/core/models/TodoList.js';
import {
    addToQueryOffsetCallback,
    createOptimisticMutationResponse,
    removeItemFromQueryOffsetCallback,
} from '@/core/helpers/apolloHelpers.js';
import { instantiate } from '@/core/utils.js';
import {
    createList,
    deleteList,
    initializeLists, moveList,
    updateList,
} from '@/core/repositories/listRepositoryHelpers.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';

export function createTodoListFromObject(obj) {
    return instantiate(obj, TodoList);
}

export function initializeTodoLists(data) {
    if (_.has(data, 'externalTodoLists')) {
        return {
            ...data.externalTodoLists,
            data: data.externalTodoLists.data.map((node) => createTodoListFromObject(node)),
        };
    }
    return initializeLists(data, createTodoListFromObject);
}

export function createInternalTodoList(form) {
    return createList(form, CREATE_TODO_LIST, TODO_LISTS, createTodoListFromObject);
}

export function createExternalTodoList(form) {
    return form.graphql(
        CREATE_EXTERNAL_TODO_LIST,
        {
            formatData(data) {
                return _.omit(data, 'color');
            },
            update: addToQueryOffsetCallback(
                { query: EXTERNAL_TODO_LISTS, variables: { sourceId: form.sourceId } },
                'createExternalTodoList.todoList',
                'externalTodoLists'
            ),
            optimisticResponse: createOptimisticMutationResponse(
                'createExternalTodoList',
                {
                    todoList: {
                        __typename: 'ExternalTodoList',
                        id: null,
                        name: form.name,
                        isDefault: false,
                        isShared: false,
                        isOwner: true,
                        updatedAt: dayjs().toISOString(),
                        account: {
                            id: -1,
                            provider: '',
                        },
                    },
                }
            ),
        }
    ).then((response) => response.data.createExternalTodoList.todoList);
}

export function createTodoList(form, space) {
    if (form.sourceId) {
        return createExternalTodoList(form);
    }
    return createInternalTodoList(form, space);
}

export function updateInternalTodoList(form, list) {
    return updateList(form, list, UPDATE_TODO_LIST);
}

export function updateExternalTodoList(form) {
    return form.graphql(
        UPDATE_EXTERNAL_TODO_LIST,
        {
            formatData(data) {
                return _.omit(data, 'color');
            },
        }
    ).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        baseApolloClient().refetchQueries({ include: [EXTERNAL_TODO_LISTS] });
        return false;
    });
}

export function updateTodoList(form, list) {
    if (form.sourceId) {
        return updateExternalTodoList(form, list);
    }
    return updateInternalTodoList(form, list);
}

export function deleteInternalTodoList(list) {
    return deleteList(list, DELETE_TODO_LIST, TODO_LISTS, TODO_STATS);
}

export function deleteExternalTodoList(list) {
    return baseApolloClient().mutate({
        mutation: DELETE_EXTERNAL_TODO_LIST,
        variables: {
            input: { id: list.id, sourceId: list.account.id },
        },
        update: removeItemFromQueryOffsetCallback(
            { query: EXTERNAL_TODO_LISTS, variables: { sourceId: list.account.id } },
            'externalTodoLists',
            list.id
        ),
    }).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        baseApolloClient().refetchQueries({ include: [EXTERNAL_TODO_LISTS] });
        return false;
    });
}

export function deleteTodoList(list) {
    if (list.account) {
        return deleteExternalTodoList(list);
    }
    return deleteInternalTodoList(list);
}

export function moveTodoList(list, previousList = null) {
    return moveList(list, previousList, MOVE_TODO_LIST, TODO_LISTS);
}
