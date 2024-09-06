import NOTEBOOKS from '@/graphql/notes/queries/Notebooks.gql';
import TODO_LISTS from '@/graphql/todos/queries/TodoLists.gql';
import CALENDARS from '@/graphql/calendar/queries/Calendars.gql';
import LINK_LISTS from '@/graphql/links/queries/LinkLists.gql';
import DRIVES from '@/graphql/documents/queries/Drives.gql';
import PINBOARDS from '@/graphql/pinboard/queries/Pinboards.gql';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import MAILBOXES from '@/graphql/mail/queries/Mailboxes.gql';

const listMap = {
    NOTES: NOTEBOOKS,
    TODOS: TODO_LISTS,
    EVENTS: CALENDARS,
    LINKS: LINK_LISTS,
    DOCUMENTS: DRIVES,
    PINBOARD: PINBOARDS,
    CALENDAR: CALENDARS,
};

const externalListMap = {
    TODOS: EXTERNAL_TODO_LISTS,
    EVENTS: EXTERNAL_CALENDARS,
    CALENDAR: EXTERNAL_CALENDARS,
    EMAILS: MAILBOXES,
};

export default {
    methods: {
        getListQuery(pageType) {
            return listMap[pageType];
        },
        getExternalListQuery(pageType) {
            return externalListMap[pageType];
        },
        hasExternalListQuery(pageType) {
            return externalListMap[pageType] !== undefined;
        },
    },
    created() {
        this.listMap = listMap;
    },
};
