import {
    isArray, isObject, mapValues,
} from 'lodash';

import { instantiate } from '@/core/utils.js';
import Calendar from '@/core/models/Calendar.js';
import Email from '@/core/models/Email.js';
import Event from '@/core/models/Event.js';
import Mailbox from '@/core/models/Mailbox.js';
import Note from '@/core/models/Note.js';
import Notebook from '@/core/models/Notebook.js';
import Page from '@/core/models/Page.js';
import Todo from '@/core/models/Todo.js';
import TodoList from '@/core/models/TodoList.js';
import User from '@/core/models/User.js';
import Notification from '@/core/models/Notification.js';
import SavedFilter from '@/core/models/SavedFilter.js';

let instances = [];

const typeMap = {
    Calendar,
    ExternalCalendar: Calendar,
    EmailMessage: Email,
    Event,
    ExternalEvent: Event,
    Mailbox,
    Note,
    Notebook,
    Notification,
    Page,
    EntityPage: Page,
    EntitiesPage: Page,
    ListPage: Page,
    Todo,
    ExternalTodo: Todo,
    TodoList,
    ExternalTodoList: TodoList,
    User,
    SavedFilter,
};

export function instantiateNode(edge, instantiateFn, withFunction = false) {
    const nodeClass = typeMap[edge.node.__typename];
    if (instantiateFn || nodeClass) {
        const instance = withFunction
            ? instantiateFn(edge.node, edge)
            : instantiate(edge.node, instantiateFn || nodeClass);
        instances.push(instance);
        return instance;
    }
    return edge.node;
}

function unpackConnection(connection, instantiateFn, withFunction = false) {
    const result = connection.edges.map((edge) => {
        return instantiateNode(edge, instantiateFn, withFunction);
    });
    result[`__${connection.__typename}`] = connection;
    return result;
}

export default function initializeConnections(data, instantiateFn, nested = false, withFunction = false) {
    if (!nested) {
        instances = [];
    }
    if (!isObject(data) || (instances.includes(data))) {
        return data;
    }
    if (data.__typename?.endsWith('Connection')) {
        return unpackConnection(data, instantiateFn, withFunction);
    }
    return {
        ...mapValues(data, (value) => {
            if (isObject(value)) {
                let result = value;
                let connectionTypename;
                if (result.__typename?.endsWith('Connection')) {
                    result = unpackConnection(value, instantiateFn, withFunction);
                    connectionTypename = `__${value.__typename}`;
                }
                if (isArray(result)) {
                    const newResults = result.map((item) => initializeConnections(
                        item, instantiateFn, true, withFunction)
                    );
                    if (connectionTypename) {
                        newResults[connectionTypename] = result[connectionTypename];
                    }
                    return newResults;
                }
                return initializeConnections(value, instantiateFn, true, withFunction);
            }
            return value;
        }),
    };
}
