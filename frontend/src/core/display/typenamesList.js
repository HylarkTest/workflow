import _ from 'lodash';

export const symbols = {
    DOCUMENTS: 'fa-paperclip',
    NOTES: 'fa-note',
    TODOS: 'fa-square-check',
    CALENDAR: 'fa-calendar-alt',
    PINBOARD: 'fa-map-pin',
    LINKS: 'fa-link',
    EMAILS: 'fa-envelope',
    TIMEKEEPER: 'fa-hourglass-clock',
    PRIORITIES: 'fa-flag',
    EVENTS: 'fa-calendar-star',
    FAVORITES: 'fa-heart',
    TAG: 'fa-tag',
    PIPELINE: 'fa-diagram-next',
    STATUS: 'fa-chart-simple-horizontal',
    ENTITIES: 'fa-table-list',
    ENTITY: 'fa-browser',
    LIST_PAGE: 'fa-thumbtack',
    BLUEPRINT: 'fa-drafting-compass',
};

const allFeaturesList = {
    DOCUMENTS: {
        symbol: symbols.DOCUMENTS,
        val: 'DOCUMENTS',
        singular: 'DOCUMENT',
        listName: 'DRIVE',
        color: '',
        __typename: 'Document',
    },
    NOTES: {
        symbol: symbols.NOTES,
        val: 'NOTES',
        singular: 'NOTE',
        listName: 'NOTEBOOK',
        color: '',
        __typename: 'Note',
    },
    TODOS: {
        symbol: symbols.TODOS,
        val: 'TODOS',
        singular: 'TODO',
        listName: 'LIST',
        color: '',
        __typename: 'Todo',
    },
    CALENDAR: {
        symbol: symbols.CALENDAR,
        val: 'CALENDAR',
        listName: 'CALENDAR',
        color: '',
        __typename: 'Event',
    },
    PINBOARD: {
        symbol: symbols.PINBOARD,
        val: 'PINBOARD',
        singular: 'PIN',
        listName: 'PINBOARD',
        color: '',
        __typename: 'Pin',
    },
    LINKS: {
        symbol: symbols.LINKS,
        val: 'LINKS',
        singular: 'LINK',
        listName: 'LINK_LIST',
        color: '',
        __typename: 'Link',
    },
    EMAILS: {
        symbol: symbols.EMAILS,
        val: 'EMAILS',
        singular: 'EMAIL',
        color: '',
        __typename: 'Email',
    },
    TIMEKEEPER: {
        symbol: symbols.TIMEKEEPER,
        val: 'TIMEKEEPER',
        color: '',
        __typename: 'Timekeeper',
    },
    PRIORITIES: {
        symbol: symbols.PRIORITIES,
        val: 'PRIORITIES',
        color: '',
        __typename: 'Priority',
    },
    EVENTS: {
        symbol: symbols.EVENTS,
        val: 'EVENTS',
        singular: 'EVENT',
        color: '',
        __typename: '',
    },
    FAVORITES: {
        symbol: symbols.FAVORITES,
        val: 'FAVORITES',
        color: '',
        __typename: '',
    },
};

// Types with their symbols

export const featurePages = {
    DOCUMENTS: allFeaturesList.DOCUMENTS,
    NOTES: allFeaturesList.NOTES,
    TODOS: allFeaturesList.TODOS,
    CALENDAR: allFeaturesList.CALENDAR,
    PINBOARD: allFeaturesList.PINBOARD,
    LINKS: allFeaturesList.LINKS,
    EMAILS: allFeaturesList.EMAILS,
    TIMEKEEPER: allFeaturesList.TIMEKEEPER,
};

export const featureTypes = {
    DOCUMENTS: allFeaturesList.DOCUMENTS,
    NOTES: allFeaturesList.NOTES,
    TODOS: allFeaturesList.TODOS,
    EVENTS: allFeaturesList.EVENTS,
    PINBOARD: allFeaturesList.PINBOARD,
    LINKS: allFeaturesList.LINKS,
    EMAILS: allFeaturesList.EMAILS,
    TIMEKEEPER: allFeaturesList.TIMEKEEPER,
};

// const otherFeatures = {
//     PRIORITIES: allFeaturesList.PRIORITIES,
//     EVENTS: allFeaturesList.EVENTS,
//     FAVORITES: allFeaturesList.FAVORITES,
// };

export const entityPagesList = {
    ENTITIES: {
        symbol: symbols.ENTITIES,
        val: 'ENTITIES',
        color: '',
        __typename: 'EntitiesPage',
    },
    ENTITY: {
        symbol: symbols.ENTITY,
        val: 'ENTITY',
        color: '',
        __typename: 'EntityPage',
    },
};

export const pageTypesList = {
    LIST_PAGE: {
        val: 'LIST_PAGE',
        __typename: 'ListPage',
        symbol: symbols.LIST_PAGE,
    },
};

export const aspectTypesList = {
    TAG: {
        val: 'TAG',
        __typename: 'Tag',
        symbol: symbols.TAG,
    },
    PIPELINE: {
        val: 'PIPELINE',
        __typename: 'Pipeline',
        symbol: symbols.PIPELINE,
    },
    STATUS: {
        val: 'STATUS',
        __typename: 'Status',
        symbol: symbols.STATUS,
    },
    TODOS_LIST: {
        val: 'TODOS_LIST',
        __typename: 'TodoList',
        symbol: symbols.TODOS,
        color: 'turquoise',
    },
    LINKS_LIST: {
        val: 'LINKS_LIST',
        __typename: 'LinkList',
        symbol: symbols.LINKS,
        color: 'turquoise',
    },
    CALENDAR_LIST: {
        val: 'CALENDAR_LIST',
        __typename: 'Calendar',
        symbol: symbols.CALENDAR,
        color: 'turquoise',
    },
    PINBOARD_LIST: {
        val: 'PINBOARD_LIST',
        __typename: 'Pinboard',
        symbol: symbols.PINBOARD,
        color: 'turquoise',
    },
    NOTES_LIST: {
        val: 'NOTES_LIST',
        __typename: 'Notebook',
        symbol: symbols.NOTES,
        color: 'turquoise',
    },
    DOCUMENTS_LIST: {
        val: 'DOCUMENTS_LIST',
        __typename: 'Drive',
        symbol: symbols.DOCUMENTS,
        color: 'turquoise',
    },
};

export function isEntity(item) {
    return item.__typename.endsWith('Item');
}

export function getIcon(feature) {
    return aspectTypesList[feature].symbol;
}

function getTypenames(arr) {
    return _(arr).map((item) => {
        return item.__typename;
    }).value();
}

export const allFeatures = allFeaturesList;

// export const pageTypenames = getTypenames(pageTypesList);

export const aspectTypeNames = getTypenames(aspectTypesList);

export const featureTypenames = getTypenames(featurePages);

export default {
    symbols,
    featurePages,
    featureTypes,
    featureTypenames,
    aspectTypesList,
    pageTypesList,
    entityPagesList,
    allFeatures,
};
