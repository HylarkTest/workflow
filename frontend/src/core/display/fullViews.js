// Describes the dashboard and bird's-eye views that each type of page has

import _ from 'lodash';
import { allFeatures } from '@/core/display/typenamesList.js';
// import { $t } from '@/i18n.js';

const birdseyeList = [
    'EVENTS',
    'TODOS',
    // 'TIMEKEEPER',
    'NOTES',
    'DOCUMENTS',
    'PINBOARD',
    'LINKS',
];

// If there is data to do with the page that they technically have access to
const viewsAccess = {
    TODOS: {
        dashboardViews: [
            'LINE',
            'KANBAN',
        ],
    },
    CALENDAR: {

    },
    PINBOARD: {

    },
    NOTES: {

    },
    LINKS: {

    },
    TIMEKEEPER: {

    },
    DOCUMENTS: {

    },
    ENTITY: null,
    ENTITIES: {
        dashboardViews: [
            'LINE',
            'TILE',
            'KANBAN',
            'SPREADSHEET',
        ],
        birdseyeViews: birdseyeList,
    },
};

export const dashboardViews = {
    LINE: {
        symbol: 'fa-rows',
        categoryType: 'DASHBOARD',
        viewType: 'LINE',
        id: 'LINE',
        name: 'Line',
    },
    TILE: {
        symbol: 'fa-table-cells-large',
        categoryType: 'DASHBOARD',
        viewType: 'TILE',
        id: 'TILE',
        name: 'Tile',
    },
    KANBAN: {
        symbol: 'fa-square-kanban',
        categoryType: 'DASHBOARD',
        viewType: 'KANBAN',
        id: 'KANBAN',
        name: 'Kanban',
    },
    SPREADSHEET: {
        symbol: 'fa-table-list',
        categoryType: 'DASHBOARD',
        viewType: 'SPREADSHEET',
        id: 'SPREADSHEET',
        name: 'Spreadsheet',
    },

};

export const birdseyeViews = _(birdseyeList).map((item) => {
    const full = allFeatures[item];
    const data = {
        ..._.omit(full, 'val'),
        categoryType: 'BIRDSEYE',
        viewType: full.val,
        id: full.val,
    };
    return [
        item,
        data,
    ];
}).fromPairs().value();

export const allViews = {
    ...dashboardViews,
    ...birdseyeViews,
};

function getRelevantList(pageType, source, usedFeatures = null) {
    const pageInfo = viewsAccess[pageType];
    let arr = [];
    if (['allViews', 'birdseyeViews'].includes(source)) {
        const listOfViews = pageInfo.birdseyeViews;

        if (listOfViews) {
            let filtered;

            if (usedFeatures) {
                filtered = listOfViews.filter((item) => {
                    return usedFeatures.includes(item);
                });
            } else {
                filtered = listOfViews;
            }

            const listOfObjects = !filtered ? [] : filtered.map((item) => {
                return [
                    item,
                    birdseyeViews[item],
                ];
            });
            arr = arr.concat(listOfObjects);
        }
    }
    if (['allViews', 'dashboardViews'].includes(source)) {
        const listOfViews = pageInfo.dashboardViews;
        if (listOfViews) {
            const listOfObjects = listOfViews.map((item) => {
                return [
                    item,
                    dashboardViews[item],
                ];
            });
            arr = arr.concat(listOfObjects);
        }
    }
    return _.fromPairs(arr);
}

export function getAllList(pageType, usedFeatures = null) {
    return getRelevantList(pageType, 'allViews', usedFeatures);
}

export function getBirdseyeObj(page, usedFeatures = null) {
    const views = getRelevantList(page.type, 'birdseyeViews', usedFeatures);
    return {
        BIRDSEYE: {
            ...views,
            ..._(page.design?.views).filter((view) => _.keys(birdseyeViews).includes(view.viewType))
                .keyBy('id')
                .value(),
        },
    };
}

export function getBirdseyeList(pageType, usedFeatures = null) {
    return getRelevantList(pageType, 'birdseyeViews', usedFeatures);
}

export function getDashboardObj(page) {
    const views = getRelevantList(page.type, 'dashboardViews');
    (page.design?.deletedViews || []).forEach((id) => delete views[id]);
    return {
        DASHBOARD: {
            ...views,
            ..._(page.design?.views).filter((view) => _.keys(dashboardViews).includes(view.viewType))
                .keyBy('id')
                .value(),
        },
    };
}

export function getDashboardList(pageType) {
    return getRelevantList(pageType, 'dashboardViews');
}

export default {
    dashboardViews,
    allViews,
    birdseyeViews,
    getAllList,
    getBirdseyeObj,
    getDashboardObj,
};
