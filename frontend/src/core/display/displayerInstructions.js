import _ from 'lodash';
import { createDocumentFromObject } from '@/core/repositories/documentRepository.js';
import { createNoteFromObject } from '@/core/repositories/noteRepository.js';
import { createLinkFromObject } from '@/core/repositories/linkRepository.js';
import { createPinFromObject } from '@/core/repositories/pinRepository.js';
import { createTodoFromObject } from '@/core/repositories/todoRepository.js';
import { createEventFromObject } from '@/core/repositories/eventRepository.js';

export const squareImageShapes = [
    'HEART',
    'HEXAGON',
    'OCTAGON',
    'FLOWER',
    'DIAMOND',
    'STAR',
];

const featureButton = {
    1: 'bg-secondary-100 hover:bg-secondary-200 text-secondary-600 py-1 px-2 rounded-md',
    2: 'bg-secondary-600 hover:bg-secondary-500 text-cm-00 py-1 px-2 rounded-md',
    3: 'border border-secondary-600 border-solid py-1 px-2 rounded-lg hover:bg-secondary-100 text-secondary-600',
    4: 'bg-primary-100 hover:bg-primary-200 text-primary-600 py-1 px-2 rounded-md',
    5: 'bg-primary-600 hover:bg-primary-500 text-cm-00 py-1 px-2 rounded-md',
    6: 'border border-primary-600 border-solid py-1 px-2 rounded-lg hover:bg-primary-100 text-primary-600',
    7: 'bg-cm-100 hover:bg-cm-200 text-cm-600 py-1 px-2 rounded-md',
    8: 'bg-cm-600 hover:bg-cm-500 text-cm-00 py-1 px-2 rounded-md',
    9: 'border border-cm-200 border-solid py-1 px-2 rounded-lg hover:bg-cm-100 text-cm-400',
};

const number = {
    1: 'font-bold',
    2: 'font-bold text-cm-400',
    3: 'centered rounded-full min-w-8 h-8 text-sm bg-cm-600 text-cm-00 font-semibold px-1',
    4: 'centered rounded-full min-w-8 h-8 text-sm bg-cm-100 font-semibold px-1',
    5: 'font-bold text-primary-600',
    6: 'centered rounded-full text-cm-00 min-w-8 h-8 text-sm bg-primary-600 font-semibold px-1',
    7: 'centered rounded-full text-primary-600 min-w-8 h-8 text-sm bg-primary-100 font-semibold px-1',
    8: 'text-secondary-600 font-semibold',
    9: 'centered rounded-full text-cm-00 min-w-8 h-8 text-sm bg-secondary-600 font-semibold px-1',
    10: 'centered rounded-full text-secondary-600 min-w-8 h-8 text-sm bg-secondary-100 font-semibold px-1',
};

const dateFormat = {
    1: {
        classes: 'rounded-xl py-0.5 px-2 bg-cm-100',
        showIcon: true,
        format: 'longFormat',
    },
    2: {
        classes: 'font-semibold',
        showIcon: true,
        format: 'longFormat',
    },
    3: {
        classes: 'font-semibold text-primary-600',
        showIcon: false,
        format: 'longFormat',
    },
    4: {
        classes: 'rounded-xl py-0.5 px-2 bg-cm-100',
        showIcon: true,
        format: 'preferenceFormat',
    },
    5: {
        classes: 'font-semibold',
        showIcon: true,
        format: 'preferenceFormat',
    },
    6: {
        classes: 'font-semibold text-primary-600',
        showIcon: false,
        format: 'preferenceFormat',
    },
};

export const combos = {
    // If not here, then definition in component
    NAME: {
        1: 'font-semibold',
        2: 'font-bold text-primary-600',
        3: 'font-semibold text-primary-600',
        4: 'font-semibold uppercase text-sm text-cm-400',
        5: '',
    },
    NUMBER: number,
    NUMBER_RANGE: number,
    LIST_COUNT: number,
    FEATURE_COUNT: number,
    FEATURE_NEW: featureButton,
    FEATURE_GO: featureButton,
    LINE: {
        1: '',
        2: 'font-bold',
        3: 'font-semibold',
        4: 'italic',
        5: 'text-primary-600 font-medium',
        6: 'text-cm-500 font-semibold',
    },
    IMAGE: {
        combos: {
            1: {
                classes: 'rounded-lg',
                langVal: 'ROUNDED_CORNERS',
            },
            2: {
                classes: 'rounded-full',
                langVal: 'ROUND',
            },
            3: {
                classes: 'rounded-none',
                langVal: 'POINTED_CORNERS',
            },
            4: {
                shape: 'HEART',
                langVal: 'HEART',
            },
            5: {
                shape: 'STAR',
                langVal: 'STAR',
            },
            6: {
                shape: 'FLOWER',
                langVal: 'FLOWER',
            },
            7: {
                shape: 'HEXAGON',
                langVal: 'HEXAGON',
            },
            8: {
                shape: 'OCTAGON',
                langVal: 'OCTAGON',
            },
            9: {
                shape: 'DIAMOND',
                langVal: 'DIAMOND',
            },
            11: {
                shape: 'CLOUD',
                langVal: 'CLOUD',
            },
        },
        additional: [
            'cover',
            'preserveRatio',
        ],
    },
    PARAGRAPH: {
        1: '',
        2: 'italic',
    },
    RATING: {
        1: '',
        2: '',
    },
    TAG: {
        1: '',
    },
    PIPELINE: {
        1: '',
    },
    STATUS: {
        1: {
            style: 'bold',
        },
        2: {
            style: 'light',
        },
        3: {
            style: 'border',
        },
    },
    TOGGLE: {
        1: '',
    },
    ICON_TOGGLE: {
        1: '',
        2: '',
        3: '',
        4: '',
    },
    SYSTEM_DATE: {
        1: '',
    },
    FEATURE_PIN: {
        1: '',
    },
    FEATURE_LINK: {
        1: '',
    },
    FEATURE_NOTE: {
        1: '',
    },
    FEATURE_TODO: {
        1: '',
    },
    FEATURE_EVENT: {
        1: '',
    },
    FEATURE_DOCUMENT: {
        1: '',
    },
    FAVORITES: {
        1: '',
        2: '',
        3: '',
    },
    PRIORITIES: {
        1: '',
        2: '',
        3: '',
    },
    SELECT: {
        1: '',
    },
    URL: {
        1: 'underline',
        2: 'h-5 w-5 text-cm-00 bg-primary-600 centered rounded',
        3: 'text-sky-500',
    },
    EMAIL: {
        1: '',
        2: 'font-semibold',
        3: 'underline',
        4: 'italic',
    },
    PHONE: {
        1: '',
        2: 'font-semibold',
        3: 'underline',
    },
    CHECKBOX: {
        1: '',
    },
    ADDRESS: {
        1: '',
    },
    ICON: {
        1: '',
        2: '',
    },
    DATE: dateFormat,
    DATE_RANGE: dateFormat,
    DATE_TIME: dateFormat,
    DATE_TIME_RANGE: dateFormat,
    TIME: {
        1: '',
    },
    TIME_RANGE: {
        1: '',
    },
    DURATION: {
        1: '',
    },
    DURATION_RANGE: {
        1: '',
    },
    MULTI: {
        1: '',
    },
    MONEY: {
        1: '',
    },
    MONEY_RANGE: {
        1: '',
    },
    SALARY: {
        1: '',
    },
    SALARY_RANGE: {
        1: '',
    },
    CURRENCY: {
        1: '',
    },
    CATEGORY: {
        1: '',
    },
    TOGGLE_LIST: {
        1: '',
    },
    RADIO_LIST: {
        1: '',
    },
    CHECKBOX_LIST: {
        1: '',
    },
    LOCATION: {
        1: '',
    },
    DROPDOWN: {
        1: '',
    },
    TIME_PHASE: {
        1: '',
    },
    TIME_DUE: {
        1: '',
    },
    TIME_START: {
        1: '',
    },
    RELATIONSHIP_RECORD: {
        1: '',
    },
    RELATIONSHIP_COUNT: number,
    ASSIGNEES: {
        1: '',
    },
};

function getDesignKeys(val) {
    const obj = combos[val];
    if (!obj) {
        return null;
    }
    const keysArr = obj.combos ? _.keys(obj.combos) : _.keys(obj);
    const combosKeys = _.map(keysArr, (keyVal) => _.toNumber(keyVal));

    const additional = obj.additional;

    return {
        combos: combosKeys,
        additional,
    };
}

const documentValue = {
    value: createDocumentFromObject({
        filename: 'attachment.pdf',
        extension: 'pdf',
        downloadUrl: '/attachments/attachment.pdf',
    }),
};

const noteValue = {
    value: createNoteFromObject({
        name: 'Your note!',
    }),
};

const linkValue = {
    value: createLinkFromObject({
        name: 'Your link!',
    }),
};

const pinValue = {
    value: createPinFromObject({
        name: 'Your pin!',
        image: {
            url: '/images/logos/hylark_circle.png',
        },
    }),
};

const todoValue = {
    value: createTodoFromObject({
        name: 'Your to-do!',
        priority: 4,
        isCompleted: () => {
            return false;
        },
    }),
};

const eventValue = {
    value: createEventFromObject({
        name: 'Your event!',
        calendar: {
            color: '#4cd453',
        },
        date: new Date(),
    }),
};

export const mockDisplayer = {
    LINE: {
        value: 'Hello, world',
    },
    PARAGRAPH: {
        // eslint-disable-next-line
        value: '"The cosmos is within us. We are made of star-stuff. We are a way for the universe to know itself." - Carl Sagan'
    },
    RATING: {
        value: {
            stars: _.random(1, 5),
        },
    },
    NUMBER: {
        value: _.random(8, 100),
    },
    NUMBER_RANGE: {
        value: {
            from: 1,
            to: 10,
        },
    },
    TAG: {
        value: {
            name: 'Tag',
            color: '#d244a0',
        },
    },
    PIPELINE: {
        value: {
            name: 'Pipeline',
            color: '#61d9d3',
        },
    },
    STATUS: {
        value: {
            name: 'Status',
            color: '#7de09f',
        },
    },
    NAME: {
        value: 'Jane Smith',
    },
    FIRST_NAME: {
        value: 'Jane',
    },
    LAST_NAME: {
        value: 'Smith',
    },
    PREFERRED_NAME: {
        value: 'Jane',
    },
    NICKNAME: {
        value: 'Janey',
    },
    SYSTEM_NAME: {
        value: 'Jane Smith',
    },
    IMAGE: {
        value: {
            url: '/branding/UsingHylarkBirdBg.png',
        },
    },
    EMAIL: {
        value: 'hello@hylark.com',
    },
    UPDATED_AT: {
        value: new Date(),
    },
    CREATED_AT: {
        value: new Date(),
    },
    TOGGLE: {
        value: true,
    },
    ICON_TOGGLE: {
        value: true,
    },
    LIST_COUNT: {
        value: _.random(0, 12),
    },
    FEATURE_COUNT: {
        value: _.random(0, 12),
    },
    RELATIONSHIP_COUNT: {
        value: _.random(0, 12),
    },
    RELATIONSHIP_RECORD: {
        value: {
            id: _.random(0, 1000, 5),
            name: 'Example relationship',
        },
    },
    PRIORITIES: {
        value: 3,
    },
    FAVORITES: {
        value: true,
    },
    TIME_PHASE: {
        value: 'WAITING_TO_START',
    },
    TIME_DUE: {
        value: '2022-11-24',
    },
    TIME_START: {
        value: '2022-10-22',
    },
    SELECT: {
        value: 'Something',
    },
    URL: {
        value: 'https://hylark.com',
    },
    ADDRESS: {
        value: {
            line1: 'Hylark HQ',
            city: 'London',
            country: 'UK',
        },
    },
    CHECKBOX: {
        value: true,
    },
    PHONE: {
        value: '+1234567890',
    },
    ICON: {
        value: 'fa-cloud-rainbow',
    },
    DATE: {
        value: '2022-12-01',
    },
    DATE_RANGE: {
        value: {
            from: '2023-04-02',
            to: '2023-05-30',
        },
    },
    DATE_TIME: {
        value: '2022-10-02 14:50:00',
    },
    DATE_TIME_RANGE: {
        value: {
            from: '2022-10-02 14:50:00',
            to: '2023-09-01 11:00:00',
        },
    },
    TIME: {
        value: '13:30:00',
    },
    TIME_RANGE: {
        value: {
            from: '13:30:00',
            to: '15:00:00',
        },
    },
    DURATION: {
        value: {
            hours: 2,
            minutes: 30,
        },
    },
    DURATION_RANGE: {
        value: {
            from: {
                hours: 2,
                minutes: 30,
            },
            to: {
                hours: 3,
            },
        },
    },
    MULTI: {
        value: '',
    },
    MONEY: {
        value: {
            currency: 'USD',
            amount: _.random(40, 100),
        },
    },
    MONEY_RANGE: {
        value: {
            currency: 'USD',
            amount: {
                from: 200,
                to: 300,
            },
        },
    },
    SALARY: {
        value: {
            currency: 'USD',
            amount: _.random(40, 100),
            period: 'HOURLY',
        },
    },
    SALARY_RANGE: {
        value: {
            currency: 'USD',
            period: 'DAILY',
            amount: {
                from: 300,
                to: 500,
            },
        },
    },
    CURRENCY: {
        value: 'USD',
    },
    CATEGORY: {
        value: {
            id: 'TEMP',
            name: 'Category',
        },
    },
    TOGGLE_LIST: {
        value: '',
    },
    RADIO_LIST: {
        value: '',
    },
    CHECKBOX_LIST: {
        value: '',
    },
    LOCATION: {
        value: {
            id: 'TEMP',
            name: 'Location',
        },
    },
    DROPDOWN: {
        value: null,
    },
    FIRST_DOCUMENT: documentValue,
    LAST_DOCUMENT: documentValue,
    FIRST_NOTE: noteValue,
    LAST_NOTE: noteValue,
    FIRST_PIN: pinValue,
    LAST_PIN: pinValue,
    FIRST_LINK: linkValue,
    LAST_LINK: linkValue,
    FIRST_EVENT: eventValue,
    LAST_EVENT: eventValue,
    UPCOMING_EVENT: eventValue,
    FIRST_TODO: todoValue,
    LAST_TODO: todoValue,
    NEXT_TODO: todoValue,
    ASSIGNEES: {
        value: [],
    },
};

export const displayerOptions = {
    // Where the type is not the same as the display type
    FIRST_DOCUMENT: {
        displayAs: 'FEATURE_DOCUMENT',
    },
    LAST_DOCUMENT: {
        displayAs: 'FEATURE_DOCUMENT',
    },
    FIRST_NOTE: {
        displayAs: 'FEATURE_NOTE',
    },
    LAST_NOTE: {
        displayAs: 'FEATURE_NOTE',
    },
    FIRST_LINK: {
        displayAs: 'FEATURE_LINK',
    },
    LAST_LINK: {
        displayAs: 'FEATURE_LINK',
    },
    FIRST_PIN: {
        displayAs: 'FEATURE_PIN',
    },
    LAST_PIN: {
        displayAs: 'FEATURE_PIN',
    },
    FIRST_EVENT: {
        displayAs: 'FEATURE_EVENT',
    },
    LAST_EVENT: {
        displayAs: 'FEATURE_EVENT',
    },
    UPCOMING_EVENT: {
        displayAs: 'FEATURE_EVENT',
    },
    FIRST_TODO: {
        displayAs: 'FEATURE_TODO',
    },
    LAST_TODO: {
        displayAs: 'FEATURE_TODO',
    },
    NEXT_TODO: {
        displayAs: 'FEATURE_TODO',
    },
    SYSTEM_NAME: {
        displayAs: 'NAME',
    },
    CREATED_AT: {
        displayAs: 'SYSTEM_DATE',
    },
    UPDATED_AT: {
        displayAs: 'SYSTEM_DATE',
    },
};

export const displayerDesigns = {
    NAME: {
        displayInfo: getDesignKeys('NAME'),
        component: 'DisplayerName',
        editComponent: 'DisplayerEditLine',
    },
    SYSTEM_NAME: {
        displayInfo: getDesignKeys('NAME'),
        component: 'DisplayerName',
        editComponent: 'DisplayerEditLine',
    },
    NUMBER: {
        displayInfo: getDesignKeys('NUMBER'),
        component: 'DisplayerNumber',
        editComponent: 'DisplayerEditNumber',
    },
    NUMBER_RANGE: {
        displayInfo: getDesignKeys('NUMBER_RANGE'),
        component: 'DisplayerNumberRange',
        editComponent: 'DisplayerEditNumberRange',
    },
    LINE: {
        displayInfo: getDesignKeys('LINE'),
        component: 'DisplayerLine',
        editComponent: 'DisplayerEditLine',
    },
    PARAGRAPH: {
        displayInfo: getDesignKeys('PARAGRAPH'),
        component: 'DisplayerParagraph',
        editComponent: 'DisplayerEditParagraph',
    },
    RATING: {
        displayInfo: getDesignKeys('RATING'),
        component: 'DisplayerRating',
    },
    TAG: {
        displayInfo: getDesignKeys('TAG'),
        component: 'DisplayerTag',
    },
    PIPELINE: {
        displayInfo: getDesignKeys('PIPELINE'),
        component: 'DisplayerPipeline',
    },
    STATUS: {
        displayInfo: getDesignKeys('STATUS'),
        component: 'DisplayerStatus',
    },
    IMAGE: {
        displayInfo: getDesignKeys('IMAGE'),
        component: 'DisplayerImage',
        editComponent: 'DisplayerEditImage',
    },
    SYSTEM_DATE: {
        displayInfo: getDesignKeys('SYSTEM_DATE'),
        component: 'DisplayerSystemDate',
    },
    MONEY: {
        displayInfo: getDesignKeys('MONEY'),
        component: 'DisplayerMoney',
        editComponent: 'DisplayerEditMoney',
    },
    MONEY_RANGE: {
        displayInfo: getDesignKeys('MONEY_RANGE'),
        component: 'DisplayerMoneyRange',
        editComponent: 'DisplayerEditMoneyRange',
    },
    SALARY: {
        displayInfo: getDesignKeys('SALARY'),
        component: 'DisplayerSalary',
        editComponent: 'DisplayerEditSalary',
    },
    SALARY_RANGE: {
        displayInfo: getDesignKeys('SALARY_RANGE'),
        component: 'DisplayerSalaryRange',
        editComponent: 'DisplayerEditSalaryRange',
    },
    SELECT: {
        displayInfo: getDesignKeys('SELECT'),
        component: 'DisplayerSelect',
        editComponent: 'DisplayerEditSelect',
    },
    FEATURE_EVENT: {
        displayInfo: getDesignKeys('FEATURE_EVENT'),
        component: 'DisplayerFeatureEvent',
    },
    FEATURE_DOCUMENT: {
        displayInfo: getDesignKeys('FEATURE_DOCUMENT'),
        component: 'DisplayerFeatureDocument',
    },
    FEATURE_NOTE: {
        displayInfo: getDesignKeys('FEATURE_NOTE'),
        component: 'DisplayerFeatureNote',
    },
    FEATURE_PIN: {
        displayInfo: getDesignKeys('FEATURE_PIN'),
        component: 'DisplayerFeaturePin',
    },
    FEATURE_LINK: {
        displayInfo: getDesignKeys('FEATURE_LINK'),
        component: 'DisplayerFeatureLink',
    },
    FEATURE_TODO: {
        displayInfo: getDesignKeys('FEATURE_TODO'),
        component: 'DisplayerFeatureTodo',
    },
    FAVORITES: {
        displayInfo: getDesignKeys('FAVORITES'),
        component: 'DisplayerFavorite',
    },
    PRIORITIES: {
        displayInfo: getDesignKeys('PRIORITIES'),
        component: 'DisplayerPriority',
    },
    ICON_TOGGLE: {
        displayInfo: getDesignKeys('ICON_TOGGLE'),
        component: 'DisplayerIconToggle',
    },
    TOGGLE: {
        displayInfo: getDesignKeys('TOGGLE'),
        component: 'DisplayerToggle',
    },
    URL: {
        displayInfo: getDesignKeys('URL'),
        component: 'DisplayerUrl',
        editComponent: 'DisplayerEditLine',
    },
    EMAIL: {
        displayInfo: getDesignKeys('EMAIL'),
        component: 'DisplayerEmail',
        editComponent: 'DisplayerEditLine',
    },
    CHECKBOX: {
        displayInfo: getDesignKeys('CHECKBOX'),
        component: 'DisplayerCheckbox',
    },
    ADDRESS: {
        displayInfo: getDesignKeys('ADDRESS'),
        component: 'DisplayerAddress',
        editComponent: 'DisplayerEditAddress',
    },
    PHONE: {
        displayInfo: getDesignKeys('PHONE'),
        component: 'DisplayerPhone',
        editComponent: 'DisplayerEditLine',
    },
    ICON: {
        displayInfo: getDesignKeys('ICON'),
        component: 'DisplayerIcon',
    },
    DATE: {
        displayInfo: getDesignKeys('DATE'),
        component: 'DisplayerDate',
        editComponent: 'DisplayerEditDate',
    },
    DATE_RANGE: {
        displayInfo: getDesignKeys('DATE_RANGE'),
        component: 'DisplayerDateRange',
        editComponent: 'DisplayerEditDateRange',
    },
    DATE_TIME: {
        displayInfo: getDesignKeys('DATE_TIME'),
        component: 'DisplayerDateTime',
        editComponent: 'DisplayerEditDateTime',
    },
    DATE_TIME_RANGE: {
        displayInfo: getDesignKeys('DATE_TIME_RANGE'),
        component: 'DisplayerDateTimeRange',
        editComponent: 'DisplayerEditDateTimeRange',
    },
    TIME: {
        displayInfo: getDesignKeys('TIME'),
        component: 'DisplayerTime',
        editComponent: 'DisplayerEditTime',
    },
    TIME_RANGE: {
        displayInfo: getDesignKeys('TIME_RANGE'),
        component: 'DisplayerTimeRange',
        editComponent: 'DisplayerEditTimeRange',
    },
    DURATION: {
        displayInfo: getDesignKeys('DURATION'),
        component: 'DisplayerDuration',
        editComponent: 'DisplayerEditDuration',
    },
    DURATION_RANGE: {
        displayInfo: getDesignKeys('DURATION_RANGE'),
        component: 'DisplayerDurationRange',
        editComponent: 'DisplayerEditDurationRange',
    },
    MULTI: {
        displayInfo: getDesignKeys('MULTI'),
        component: 'DisplayerMulti',
        editComponent: 'DisplayerEditMulti',
    },
    CURRENCY: {
        displayInfo: getDesignKeys('CURRENCY'),
        component: 'DisplayerCurrency',
        editComponent: 'DisplayerEditCurrency',
    },
    CATEGORY: {
        displayInfo: getDesignKeys('CATEGORY'),
        component: 'DisplayerCategory',
        editComponent: 'DisplayerEditCategory',
        saveOnEvent: true,
    },
    TOGGLE_LIST: {
        displayInfo: getDesignKeys('TOGGLE_LIST'),
        component: 'DisplayerToggleList',
    },
    RADIO_LIST: {
        displayInfo: getDesignKeys('RADIO_LIST'),
        component: 'DisplayerRadioList',
    },
    CHECKBOX_LIST: {
        displayInfo: getDesignKeys('CHECKBOX_LIST'),
        component: 'DisplayerCheckboxList',
    },
    LOCATION: {
        displayInfo: getDesignKeys('LOCATION'),
        component: 'DisplayerLocation',
        editComponent: 'DisplayerEditLocation',
        saveOnEvent: true,
    },
    DROPDOWN: {
        displayInfo: getDesignKeys('DROPDOWN'),
        component: 'DisplayerDropdown',
        editComponent: 'DisplayerEditDropdown',
        editCondition: (infoOptions) => {
            return infoOptions?.multiSelect;
        },
        saveOnEvent: true,
    },
    LIST_COUNT: {
        displayInfo: getDesignKeys('NUMBER'),
        component: 'DisplayerListCount',
    },
    TIME_PHASE: {
        displayInfo: getDesignKeys('TIME_PHASE'),
        component: 'DisplayerTimekeeperPhase',
    },
    TIME_DUE: {
        displayInfo: getDesignKeys('TIME_DUE'),
        component: 'DisplayerTimekeeperDue',
    },
    TIME_START: {
        displayInfo: getDesignKeys('TIME_START'),
        component: 'DisplayerTimekeeperStart',
    },
    RELATIONSHIP_RECORD: {
        displayInfo: getDesignKeys('RELATIONSHIP_RECORD'),
        component: 'DisplayerRelationshipRecord',
    },
    RELATIONSHIP_COUNT: {
        displayInfo: getDesignKeys('RELATIONSHIP_COUNT'),
        component: 'DisplayerRelationshipCount',
    },
    FEATURE_COUNT: {
        displayInfo: getDesignKeys('NUMBER'),
        component: 'DisplayerFeatureCount',
    },
    FEATURE_NEW: {
        displayInfo: getDesignKeys('FEATURE_NEW'),
        component: 'DisplayerFeatureNew',
    },
    FEATURE_GO: {
        displayInfo: getDesignKeys('FEATURE_GO'),
        component: 'DisplayerFeatureGo',
    },
    ASSIGNEES: {
        displayInfo: getDesignKeys('ASSIGNEES'),
        component: 'DisplayerAssignees',
        saveOnEvent: true,
    },
};

// function getDisplayInfo(typeKey) {
//     const displayerObj = displayerDesigns[typeKey];
//     const displayInfo = displayerObj?.displayInfo;
//     if (displayInfo) {
//         return displayInfo;
//     }
//     return getDesignKeys(typeKey);
// }

export function getTypeKey(data) {
    if (data) {
        const displayOption = data.displayOption;
        const subType = data.info?.subType;
        const descriptorTypes = ['LIST_FIRST', 'LIST_MAIN'];
        if (descriptorTypes.includes(displayOption)) {
            return subType;
        }
        return displayOption || subType;
    }
    return null;
}

export function getCombo(typeKey, combo) {
    const pointer = displayerOptions[typeKey]?.displayAs;
    const type = pointer || typeKey;
    if (combos[type]) {
        const combosBase = combos[type].combos || combos[type];
        return combosBase[combo];
    }
    return 1;
}

// export function getAdditional(typeKey, additionalVal) {
//     if (additionalVal) {
//         const pointer = displayerOptions[typeKey]?.displayAs;
//         const type = pointer || typeKey;
//         if (combos[type] && combos[type].additional) {
//             return combos[type].additional[additionalVal];
//         }
//         return null;
//     }
//     return null;
// }

export function getDesignInfo(data) {
    const typeKey = getTypeKey(data);
    const pointer = displayerOptions[typeKey]?.displayAs;
    const type = pointer || typeKey;
    return displayerDesigns[type];
}

export function getDefaultAdditional(data) {
    const design = getDesignInfo(data);
    if (!design.displayInfo) {
        return null;
    }
    if (design.displayInfo.additional) {
        return design.displayInfo.additional[0];
    }
    return null;
}

export function getDefaultCombo(data) {
    const design = getDesignInfo(data);
    if (!design.displayInfo) {
        return null;
    }
    if (design.displayInfo.combos) {
        return design.displayInfo.combos[0];
    }
    return design.displayInfo[0];
}

export function getEditComponent(data, customKey) {
    const design = customKey ? displayerDesigns[customKey] : getDesignInfo(data);
    return design?.editComponent || design?.component;
}

export function getMockData(data) {
    const typeKey = getTypeKey(data);
    if (typeKey === 'LINE') {
        return data.name;
    }
    if (typeKey === 'PARAGRAPH') {
        return `Your "${data.name}" paragraph`;
    }
    return mockDisplayer[typeKey]?.value;
}

export function getFormattedMockData(item, omitParent) {
    let data = item.info?.subType === 'LINE' ? item.name : getMockData(item);
    const dataType = item.dataType;
    const formatted = _.camelCase(item.formattedId);
    const displayOption = item.displayOption;

    if (dataType === 'SYSTEM') {
        return { [formatted]: data };
    }
    if (displayOption === 'TIME_PHASE') {
        return { deadlines: { status: data } };
    }
    if (displayOption === 'TIME_DUE') {
        return { deadlines: { dueBy: data } };
    }
    if (displayOption === 'TIME_START') {
        return { deadlines: { startAt: data } };
    }
    if (displayOption === 'PRIORITIES') {
        return { priority: data };
    }
    if (displayOption === 'FAVORITES') {
        return { isFavorite: data };
    }
    if (dataType === 'FEATURES') {
        return { features: { [item.formattedId]: data } };
    }
    if (dataType === 'MARKERS') {
        const defaultData = item.info.subType === 'STATUS' ? data : [data];
        return { markers: { [item.info.groupId]: defaultData } };
    }

    if (dataType === 'RELATIONSHIPS') {
        if (displayOption === 'RELATIONSHIP_COUNT') {
            return {
                relations: {
                    [item.id]: {
                        pageInfo: {
                            total: mockDisplayer.NUMBER.value,
                        },
                    },
                },
            };
        }
        if (displayOption === 'RELATIONSHIP_RECORD') {
            return { relations: { [item.id]: { node: data } } };
        }
        return null;
    }

    // Fields from here on
    const infoOptions = item.info?.options;
    let dataVal;

    const isMulti = item.info?.subType === 'MULTI';
    if (isMulti) {
        const multiData = _(infoOptions.subFields).map((subField) => {
            const formattedMock = getFormattedMockData(subField, true);
            const formattedData = formattedMock.data[subField.id];

            return [
                subField.id,
                formattedData,
            ];
        }).fromPairs().value();

        data = multiData;
    }

    // Labeled vs not labeled
    if (infoOptions?.labeled) {
        dataVal = {
            fieldValue: data,
            label: 'Label',
        };
    } else {
        dataVal = { fieldValue: data };
    }

    // List adjustments
    if (infoOptions?.list) {
        dataVal.main = true;
        dataVal = { listValue: (new Array(dataVal)) };
    }

    const parent = item.info?.parent;

    const dataObj = {
        [item.id]: dataVal,
    };

    // Has parent adjustments (children of multi)
    if (parent && !omitParent) {
        const parentId = parent.id;
        const parentOptions = parent.info?.options;
        let parentVal;

        if (infoOptions?.labeled) {
            parentVal = {
                fieldValue: dataObj,
                label: 'Label',
            };
        } else {
            parentVal = { fieldValue: dataObj };
        }

        if (parentOptions?.list) {
            parentVal.main = true;
            parentVal = { listValue: (new Array(parentVal)) };
        }

        return {
            data: {
                [parentId]: parentVal,
            },
        };
    }

    return {
        data: dataObj,
    };
}

export function getMockOfFieldsArr(fieldsArr) {
    return _(fieldsArr).map((field) => {
        const baseValue = getFormattedMockData(field);

        const mockValue = baseValue.data[field.id];

        return [
            field.id,
            mockValue,

        ];
    }).fromPairs().value();
}

export default {
    displayerOptions,
    getMockData,
    getDefaultCombo,
    getDefaultAdditional,
    getCombo,
    getTypeKey,
};
