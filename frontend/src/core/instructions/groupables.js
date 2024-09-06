const defaultGroupables = [
    'LIST',
    '{MARKERS}',
];

const favoritedGroupables = [
    'FAVORITES',
    ...defaultGroupables,
];

export default {
    TODOS: [
        'PRIORITY',
        ...defaultGroupables,
    ],
    NOTES: favoritedGroupables,
    LINKS: favoritedGroupables,
    PINBOARD: favoritedGroupables,
    DOCUMENTS: [
        'EXTENSION',
        ...favoritedGroupables,
    ],
};
