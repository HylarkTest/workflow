const homeTab = () => ({
    name: 'Home',
    value: 'home',
    icon: 'fal fa-home',
    selected: {
        elements: [],
        instructions: {},
    },
});

export const featureTabsDefaults = {
    PLANNER: {
        name: 'Planner',
        value: 'planner',
        icon: 'fal fa-calendar-alt',
    },
    COLLABORATION: {
        name: 'Collaboration',
        value: 'collaboration',
        icon: 'fal fa-user-friends',
    },
    NOTES: {
        name: 'Notes',
        value: 'notes',
        icon: 'fal fa-sticky-note',
    },
    DOCUMENTS: {
        name: 'Documents',
        value: 'attachments',
        icon: 'fal fa-paperclip',
    },
    COMMENTS: {
        name: 'Comments',
        value: 'comments',
        icon: 'fal fa-comments-alt',
    },
};

export function getFullDefault() {
    return {
        top: {},
        tabStyle: 'IconTextVertical',
        customTabs: {
            home: homeTab(),
        },
        relationships: [],
        tabOrder: [],
    };
}

export default { getFullDefault, featureTabsDefaults };
