import dayjs from '@/core/plugins/initDayjs.js';

import TODOS from '@/graphql/todos/queries/Todos.gql';
import GROUPED_TODOS from '@/graphql/todos/queries/GroupedTodos.gql';
import TODO_LISTS from '@/graphql/todos/queries/TodoLists.gql';
import EXTERNAL_TODOS from '@/graphql/todos/queries/ExternalTodos.gql';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import MOVE_TODO from '@/graphql/todos/mutations/MoveTodo.gql';
// import TODO_LIST_FRAGMENT from '@/graphql/todos/TodoListFragment.gql';
import UPDATE_TODO from '@/graphql/todos/mutations/UpdateTodo.gql';
import UPDATE_EXTERNAL_TODO from '@/graphql/todos/mutations/UpdateExternalTodo.gql';
import CREATE_TODO from '@/graphql/todos/mutations/CreateTodo.gql';
import CREATE_EXTERNAL_TODO from '@/graphql/todos/mutations/CreateExternalTodo.gql';
import DELETE_TODO from '@/graphql/todos/mutations/DeleteTodo.gql';
import DUPLICATE_TODO from '@/graphql/todos/mutations/DuplicateTodo.gql';
import DELETE_EXTERNAL_TODO from '@/graphql/todos/mutations/DeleteExternalTodo.gql';
import TODO_STATS from '@/graphql/todos/queries/TodoStats.gql';
import { instantiate } from '@/core/utils.js';
import { getCachedOperationNames, removeTypename } from '@/core/helpers/apolloHelpers.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Todo from '@/core/models/Todo.js';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';

export const TODO_CREATED = Symbol('Todo created');
export const TODO_DELETED = Symbol('Todo deleted');
export const TODO_UPDATED = Symbol('Todo updated');

export function createTodoFromObject(obj) {
    return instantiate(obj, Todo);
}

const weekDayMap = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

export function initializeTodos(data) {
    if (_.has(data, 'externalTodos')) {
        return {
            ...data.externalTodos,
            data: data.externalTodos.data.map((node) => createTodoFromObject(node)),
        };
    }
    return _.getFirstKey(initializeConnections(data));
}

export function moveTodoToList(todo, list) {
    // const oldList = todo.list;
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_TODO,
        variables: {
            input: {
                todoListId: list.id,
                id: todo.id,
            },
        },
        // update: (store) => {
        //     if (!todo.completedAt) {
        //         store.updateFragment(
        //             {
        //                 id: store.identify(oldList),
        //                 fragment: TODO_LIST_FRAGMENT,
        //             },
        //             (data) => ({
        //                 ...data,
        //                 incompleteTodosCount: data.incompleteTodosCount - 1,
        //             })
        //         );
        //     }
        //
        //     store.modify({
        //         id: 'ROOT_QUERY',
        //         fields: {
        //             todos: (todos, options) => {
        //                 if (fieldNameHasArgs(options.fieldName, { todoListId: oldList.id })) {
        //                     return {
        //                         ...todos,
        //                         edges: arrRemoveId(todos.edges, store.identify(todo), 'node.__ref'),
        //                     };
        //                 }
        //                 if (fieldNameHasArgs(options.fieldName, { todoListId: list.id })) {
        //                     return options.DELETE;
        //                 }
        //                 return todos;
        //             },
        //         },
        //     });
        // },
        refetchQueries: getCachedOperationNames([
            TODOS,
            TODO_LISTS,
        ], client),
    });
}

export function moveTodo(todo, previousTask) {
    // const previousId = to === 0 ? null : this.allTodos[to < from ? to - 1 : to].id;
    const previousId = previousTask?.id || null;
    // const oldOrder = todo.order;
    // const newOrder = previousTask ? previousTask.order : 1;

    // const [maxOrder, minOrder] = oldOrder > newOrder ? [oldOrder, newOrder] : [newOrder, oldOrder];
    // const shift = newOrder > oldOrder ? -1 : 1;
    //
    // const modified = [todo.id];

    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_TODO,
        variables: {
            input: {
                todoListId: todo.list.id,
                id: todo.id,
                previousId,
            },
        },
        refetchQueries: getCachedOperationNames([
            TODOS,
        ], client),
        // update: (store) => {
        //     store.modify({
        //         id: 'ROOT_QUERY',
        //         fields: {
        //             todos: (todos, options) => {
        //                 if (fieldNameHasArgs(options.storeFieldName, { todoListId: todo.list.id })) {
        //                     todos.edges.forEach((edge) => {
        //                         const todoId = options.readField('id', edge.node);
        //                         const todoOrder = options.readField('order', edge.node);
        //                         if (!modified.includes(todoId)) {
        //                             modified.push(todoId);
        //                             if (todoOrder < maxOrder && todoOrder > minOrder) {
        //                                 console.log('updating order');
        //                                 store.modify({
        //                                     id: store.identify(todoId),
        //                                     fields: {
        //                                         order: (previousOrder) => previousOrder + shift,
        //                                     },
        //                                 });
        //                             }
        //                         }
        //                     });
        //
        //                     console.log(todos.edges);
        //                     console.log(updateOrder(
        //                         todos.edges,
        //                         options.storeFieldName,
        //                         options.readField,
        //                         [{ field: 'MANUAL', direction: 'DESC' }],
        //                         { MANUAL: 'order' }
        //                     ));
        //
        //                     return {
        //                         ...todos,
        //                         edges: updateOrder(
        //                             todos.edges,
        //                             options.storeFieldName,
        //                             options.readField,
        //                             [{ field: 'MANUAL', direction: 'DESC' }],
        //                             { MANUAL: 'order' }
        //                         ),
        //                     };
        //                 }
        //                 return todos;
        //             },
        //         },
        //     });
        // },
        // optimisticResponse: {
        //     __typename: 'Mutation',
        //     moveTodo: {
        //         code: '200',
        //         success: true,
        //         todo: {
        //             ...todo,
        //             order: newOrder,
        //         },
        //     },
        // },
    });
}

function formatRecurrence(data) {
    if (_.has(data, 'recurrence')) {
        if (!data.recurrence?.frequency) {
            return {
                ...data,
                recurrence: null,
            };
        }

        if (data.recurrence.byDay?.length) {
            return {
                ...data,
                recurrence: {
                    ...data.recurrence,
                    byDay: data.recurrence.byDay.map((index) => weekDayMap[index]),
                },
            };
        }
    }

    return data;
}

export function updateInternalTodo(form) {
    return dispatchPromise(form.graphql(
        UPDATE_TODO,
        {
            refetchQueries: [
                TODO_STATS,
                // TODO_LISTS,
                TODOS,
                GROUPED_TODOS,
            ],
            formatData(dataWithTypename) {
                const data = removeTypename(dataWithTypename);
                if (_.has(data, 'recurrence')) {
                    if (!data.recurrence?.frequency) {
                        data.recurrence = null;
                    } else if (data.recurrence.byDay?.length) {
                        data.recurrence.byDay = data.recurrence.byDay.map((index) => weekDayMap[index]);
                    }
                }
                return data;
            },
        }
    ), TODO_UPDATED, 'data.updateTodo.todo');
}

function handleErrors(error) {
    if (!checkAndHandleMissingError(error, false)) {
        throw error;
    }
    baseApolloClient().refetchQueries({ include: [EXTERNAL_TODO_LISTS, EXTERNAL_TODOS] });
    return false;
}

export function updateExternalTodo(form) {
    return dispatchPromise(form.graphql(
        UPDATE_EXTERNAL_TODO,
        {
            formatData(dataWithTypename) {
                const data = removeTypename(dataWithTypename);
                return formatRecurrence(data);
            },
            refetchQueries: [
                EXTERNAL_TODO_LISTS,
                EXTERNAL_TODOS,
            ],
        }
    ), TODO_UPDATED, 'data.updateExternalTodo.todo').catch((error) => {
        return handleErrors(error);
    });
}

export function updateTodo(form) {
    if (form.sourceId) {
        return updateExternalTodo(form);
    }
    return updateInternalTodo(form);
}

export function duplicateTodo(todo, data) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DUPLICATE_TODO,
        variables: {
            input: {
                id: todo.id,
                ...data,
            },
        },
        refetchQueries: getCachedOperationNames([
            TODOS,
            GROUPED_TODOS,
            TODO_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(TODO_CREATED, result.data.duplicateTodo.todo);
        return result;
    });
}

export function createInternalTodo(form) {
    return dispatchPromise(form.graphql(CREATE_TODO, {
        formatData(dataWithTypename) {
            const data = removeTypename(dataWithTypename);
            return {
                ..._.omit(formatRecurrence(data), 'sourceId'),
                associations: data.associations?.map((item) => item?.id || item),
                assigneeGroups: (data.assigneeGroups || []).map((assigneeGroup) => ({
                    groupId: assigneeGroup.groupId,
                    assignees: _.map(assigneeGroup.assignees, 'id'),
                })),
            };
        },
        refetchQueries: [
            TODOS,
            GROUPED_TODOS,
            TODO_STATS,
        ],
    }), TODO_CREATED, 'data.createTodo.todo');
}

export function createExternalTodo(form) {
    return dispatchPromise(form.graphql(CREATE_EXTERNAL_TODO, {
        formatData: (data) => {
            return {
                ..._.omit(data, 'markers'),
                associations: data.associations?.map((item) => item?.id || item),
            };
        },
        refetchQueries: [
            EXTERNAL_TODOS,
            EXTERNAL_TODO_LISTS,
        ],
    }), TODO_CREATED, 'data.createExternalTodo.todo').catch((error) => {
        return handleErrors(error);
    });
}

export function createTodo(form) {
    if (form.sourceId) {
        return createExternalTodo(form);
    }
    return createInternalTodo(form);
}

export function deleteInternalTodo(todo) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_TODO,
        variables: {
            input: {
                id: todo.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            TODOS,
            GROUPED_TODOS,
            TODO_LISTS,
            TODO_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(TODO_DELETED, todo);
        return result;
    });
}

export function deleteExternalTodo(todo) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_EXTERNAL_TODO,
        variables: {
            input: {
                sourceId: todo.account.id,
                todoListId: todo.list.id,
                id: todo.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            EXTERNAL_TODOS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(TODO_DELETED, todo);
        return result;
    }).catch((error) => {
        return handleErrors(error);
    });
}

export function toggleCompletion(todo, isCompleted) {
    const newCompletion = isCompleted ? dayjs().toISOString() : null;
    const data = {
        todoListId: todo.list.id,
        id: todo.id,
        completedAt: newCompletion,
    };
    if (todo.isExternalItem()) {
        data.sourceId = todo.account.id;
    }
    return updateTodo(createApolloForm(baseApolloClient(), data));
}

export function setPriority(todo, priority) {
    const data = {
        todoListId: todo.list.id,
        id: todo.id,
        priority,
    };
    if (todo.isExternalItem()) {
        data.sourceId = todo.account.id;
    }
    return updateTodo(createApolloForm(baseApolloClient(), data));
}

export function deleteTodo(todo) {
    if (todo.account) {
        return deleteExternalTodo(todo);
    }
    return deleteInternalTodo(todo);
}

export function changeTodoList(todo, todoListId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_TODO,
        variables: {
            input: {
                id: todo.id,
                todoListId,
            },
        },
        refetchQueries: getCachedOperationNames([
            TODOS,
            TODO_LISTS,
        ], client),
    });
}

export function buildPriorityGroupQuery() {
    const priorities = [0, 1, 3, 5, 9];
    const todosQuery = TODOS;
    const operation = todosQuery.definitions[0];
    const operationArgs = operation.selectionSet.selections[0].arguments;
    const newOperation = {
        ...operation,
        selectionSet: {
            kind: 'SelectionSet',
            selections: priorities.map((priority) => ({
                kind: 'Field',
                name: { kind: 'Name', value: 'todos' },
                alias: { kind: 'Name', value: `_${priority}` },
                arguments: [
                    ...operationArgs,
                    {
                        kind: 'Argument',
                        name: { kind: 'Name', value: 'priority' },
                        value: { kind: 'IntValue', value: priority },
                    },
                ],
                selectionSet: {
                    kind: 'SelectionSet',
                    selections: [{
                        kind: 'FragmentSpread',
                        name: { kind: 'Name', value: 'Todos' },
                    }],
                },
            })),
        },
    };
    const todosFragment = {
        kind: 'FragmentDefinition',
        name: { kind: 'Name', value: 'Todos' },
        typeCondition: { kind: 'NamedType', name: { kind: 'Name', value: 'TodoConnection' } },
        selectionSet: operation.selectionSet.selections[0].selectionSet,
    };

    return {
        ...todosQuery,
        definitions: [
            newOperation,
            todosFragment,
            ...todosQuery.definitions.slice(1),
        ],
    };
}
