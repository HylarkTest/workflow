export const featuresList = {
    NOTES: {
        val: 'NOTES',
        relatesTo: '', // all
        options: {
            formatted: true,
        },
    },
    TODOS: {
        val: 'TODOS',
        relatesTo: '', // all
    },
    EVENTS: {
        val: 'EVENTS',
        relatesTo: '', // all
    },
    DOCUMENTS: {
        val: 'DOCUMENTS',
        relatesTo: '', // all
    },
    TIMEKEEPER: {
        val: 'TIMEKEEPER',
        relatesTo: '', // all
    },
    EMAILS: {
        val: 'EMAILS',
        relatesTo: '', // all
    },
    LINKS: {
        val: 'LINKS',
        relatesTo: '', // all
    },
    PINBOARD: {
        val: 'PINBOARD',
        relatesTo: '', // all
    },
    PRIORITIES: {
        val: 'PRIORITIES',
        relatesTo: '', // all
    },
    FAVORITES: {
        val: 'FAVORITES',
        relatesTo: '', // all
    },
};

export const prioritiesFavorites = [
    featuresList.PRIORITIES,
    featuresList.FAVORITES,
];

export const mainFeatures = [
    featuresList.EVENTS,
    featuresList.TODOS,
    featuresList.NOTES,
    featuresList.DOCUMENTS,
];

export const infoFeatures = mainFeatures.concat([
    featuresList.LINKS,
    featuresList.PINBOARD,
    ...prioritiesFavorites,
]);

export const mainWithPrioritiesFavorites = mainFeatures.concat(prioritiesFavorites);

export const mainWithEmail = mainFeatures.concat([
    featuresList.EMAILS,
]);

export const allFeatures = mainFeatures.concat([
    featuresList.EMAILS,
    featuresList.TIMEKEEPER,
    featuresList.LINKS,
    featuresList.PINBOARD,
    ...prioritiesFavorites,
]);

export function getFeatures(defaultList, customArr) {
    const arr = customArr.map((feature) => {
        return featuresList[feature];
    });
    return _.concat(defaultList, arr);
}
