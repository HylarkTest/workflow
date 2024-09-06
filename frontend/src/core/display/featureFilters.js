// Provides the feature pages' filters
import _ from 'lodash';

export const basicFeatureFilters = [
    {
        id: 'today',
        icon: 'fa-sun',
        color: 'gold',
    },
    {
        id: 'scheduled',
        icon: 'fa-calendar-check',
        color: 'azure',
    },
    {
        id: 'highPriority',
        icon: 'fa-flag',
        color: 'peach',
    },
    {
        id: 'overdue',
        icon: 'fa-exclamation',
        color: 'rose',
    },
    {
        id: 'all',
        icon: 'fa-box-check',
        color: 'turquoise',
    },
    {
        id: 'favorites',
        icon: 'fa-heart',
        color: 'violet',
    },
];

export function getFeatureFilters(ids) {
    return ids.map((id) => {
        return {
            ..._.find(basicFeatureFilters, { id }),
            textPath: `labels.basicFilters.${id}`,
        };
    });
}

export const allTodosFilters = getFeatureFilters([
    'today',
    'scheduled',
    'highPriority',
    'overdue',
    'all',
]);

export const allNotesFilters = getFeatureFilters([
    'favorites',
    'all',
]);
export const allAttachmentsFilters = getFeatureFilters([
    'favorites',
    'all',
]);
export const allPinboardFilters = getFeatureFilters([
    'favorites',
    'all',
]);
export const allLinksFilters = getFeatureFilters([
    'favorites',
    'all',
]);
export const lineEventsFilters = getFeatureFilters([
    'today',
    'all',
]);
export const calendarEventsFilters = getFeatureFilters([
    'all',
]);

export const featureFiltersObj = {
    TODOS: allTodosFilters,
    LINKS: allLinksFilters,
    DOCUMENTS: allAttachmentsFilters,
    NOTES: allNotesFilters,
    PINBOARD: allPinboardFilters,
    EVENTS: {
        CALENDAR: calendarEventsFilters,
        LINE: lineEventsFilters,
    },
};
