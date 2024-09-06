// Type = The field type, as defined in fieldTypes and obtained with the function.
// Name = The translated string, blank by default and added in the page,
//        generated dynamically based on language and context (and nameKey)
// nameKey = The unique value used to get the name. If no nameKey, then name uses type.
//           The same field might be used in different contexts and have a different name.
// id = The unique value used to merge fields and as a unique identifier. If the same id is added
//      on multiple fields, then it will keep the first one, or the one that has they key keep true.
// dataPointer = Additional value if required to pick out an appropriate default for this field.
//               If false, no default will be picked out. If unset, the type will be used.

import { frontendFields } from '@/core/mappings/frontendFieldTypes.js';

function getFieldInfo(fieldType) {
    const fieldObj = frontendFields.find((field) => field.val === fieldType);

    return {
        type: fieldObj.type,
        meta: fieldObj.meta,
        options: fieldObj.options,
    };
}

const lineInfo = getFieldInfo('LINE');
const paragraphInfo = getFieldInfo('PARAGRAPH');

// Names
export const SYSTEM_NAME = {
    type: 'SYSTEM_NAME',
    id: 'SYSTEM_NAME',
    nameKey: 'NAME',
};

export const SYSTEM_NAME_FULL = {
    ...SYSTEM_NAME,
    nameKey: 'FULL_NAME',
};

export const FIRST_NAME = {
    ...getFieldInfo('FIRST_NAME'),
    id: 'FIRST_NAME',
};

export const LAST_NAME = {
    ...getFieldInfo('LAST_NAME'),
    id: 'LAST_NAME',
};

export const NICKNAME = {
    ...getFieldInfo('NICKNAME'),
    id: 'NICKNAME',
};

export const FULL_NAME = {
    ...getFieldInfo('FULL_NAME'),
    id: 'FULL_NAME',
};

export const PREFERRED_NAME = {
    ...getFieldInfo('PREFERRED_NAME'),
    id: 'PREFERRED_NAME',
};

const nameInfo = getFieldInfo('NAME');

export const NAME = {
    ...nameInfo,
    id: 'NAME',
};

export const CLUB_NAME = {
    ...nameInfo,
    id: 'CLUB_NAME',
};

// Basics
export const DESCRIPTION = {
    ...paragraphInfo,
    id: 'DESCRIPTION',
};

// Images
const imageInfo = getFieldInfo('IMAGE');
export const IMAGE = {
    ...imageInfo,
    id: 'IMAGE',
    exampleKey: 'IMAGE',
    options: {
        ...imageInfo.options,
        primary: true,
    },

};

export const LOGO = {
    ...IMAGE,
    id: 'LOGO',
};

// Contact info
const addressInfo = getFieldInfo('ADDRESS');

export const ADDRESSES = {
    ...addressInfo,
    options: {
        ...addressInfo.options,
        list: true,
        labeled: {
            labels: {
                1: 'Home',
                2: 'Work',
                3: 'School',
                4: 'Other',
            },
        },
    },
    id: 'ADDRESSES',
};

export const ADDRESSES_FREE_LABEL = {
    ...ADDRESSES,
    options: {
        ...ADDRESSES.options,
        labeled: {
            freeText: true,
        },
    },
    id: 'ADDRESSES_FREE_LABEL',
    nameKey: 'ADDRESSES',
};

export const VENUES = {
    ...ADDRESSES_FREE_LABEL,
    nameKey: 'VENUES',
};

export const ADDRESS = {
    ...addressInfo,
    id: 'ADDRESS',
};

const phoneInfo = getFieldInfo('PHONE');

export const PHONES = {
    ...phoneInfo,
    options: {
        ...phoneInfo.options,
        list: true,
        labeled: {
            labels: {
                1: 'Mobile',
                2: 'Personal',
                3: 'Work',
                4: 'School',
                5: 'Other',
            },
        },
    },
    id: 'PHONES',
    exampleKey: 'PHONE',
    defaultDisplayOption: 'LIST_FIRST',
};

export const PHONES_FREE_LABEL = {
    ...PHONES,
    options: {
        ...PHONES.options,
        labeled: {
            freeText: true,
        },
    },
    id: 'PHONES_FREE_LABEL',
    nameKey: 'PHONES',
};

const emailInfo = getFieldInfo('EMAIL');

export const EMAILS = {
    ...emailInfo,
    options: {
        ...emailInfo.options,
        list: true,
        labeled: {
            labels: {
                1: 'Personal',
                2: 'Work',
                3: 'School',
                4: 'Other',
            },
        },
    },
    id: 'EMAILS',
    exampleKey: 'EMAIL',
    defaultDisplayOption: 'LIST_FIRST',
};

export const EMAILS_FREE_LABEL = {
    ...EMAILS,
    options: {
        ...EMAILS.options,
        labeled: {
            freeText: true,
        },
    },
    id: 'EMAILS_FREE_LABEL',
    nameKey: 'EMAILS',
};

const urlInfo = getFieldInfo('URL');

export const LINKS = {
    ...urlInfo,
    options: {
        ...urlInfo.options,
        list: true,
        labeled: {
            freeText: true,
        },
    },
    id: 'LINKS',
    nameKey: 'LINKS',
    exampleKey: 'LINK',
};

// Dates
const dateInfo = getFieldInfo('DATE');

export const BIRTHDAY = {
    ...dateInfo,
    id: 'BIRTHDAY',
    exampleKey: 'BIRTHDAY',
};

export const COMPLETION_DATE = {
    ...dateInfo,
    id: 'COMPLETION_DATE',
};

export const START_DATE = {
    ...dateInfo,
    id: 'START_DATE',
};

export const DATE_PURCHASED = {
    ...dateInfo,
    id: 'DATE_PURCHASED',
};

// Categories
const categoryInfo = getFieldInfo('CATEGORY');

export const INDUSTRY = {
    ...categoryInfo,
    id: 'INDUSTRY',
    options: {
        ...categoryInfo.options,
        multiSelect: true,
        category: 'INDUSTRIES_TEMP',
    },
};

// Locations

const worldwideInfo = getFieldInfo('WORLDWIDE');

export const WORLDWIDE_LOCATIONS = {
    ...worldwideInfo,
    id: 'WORLDWIDE_LOCATIONS',
    options: {
        ...worldwideInfo.options,
        multiSelect: true,
    },
    nameKey: 'LOCATION',
};

// Dropdowns

const dropdownInfo = getFieldInfo('DROPDOWN');

// export const categoryTheme = {
//     ...dropdownInfo,
//     id: 'CATEGORY_THEME',
//     options: {
//         ...dropdownInfo.options,
//         valueOptions: {
//             1: 'Personal development',
//             2: 'Travel',
//             3: 'Career',
//             4: 'Hobbies',
//             5: 'Adventure',
//         },
//     },
//     nameKey: 'THEME',
// };

export const OCCASION = {
    ...dropdownInfo,
    id: 'OCCASION',
    options: {
        ...dropdownInfo.options,
        valueOptions: {
            1: 'Birthday',
            2: 'Anniversary',
            3: 'Wedding',
            4: 'Christmas',
            5: 'Other',
        },
    },
};

export const PAYMENT_INTERVAL = {
    ...dropdownInfo,
    id: 'PAYMENT_INTERVAL',
    options: {
        ...dropdownInfo.options,
        valueOptions: {
            1: 'Weekly',
            2: 'Monthly',
            3: 'Quarterly',
            4: 'Semi-Annually',
            5: 'Yearly',
        },
    },
};

export const PAYMENT_METHOD = {
    ...dropdownInfo,
    id: 'PAYMENT_METHOD',
    options: {
        ...dropdownInfo.options,
        valueOptions: {
            1: 'Credit Card',
            2: 'Debit Card',
            3: 'PayPal',
            4: 'Bank Transfer',
            5: 'Direct debit',
            6: 'Other',
        },
    },
};

export const ORGANIZATION_TYPE = {
    ...dropdownInfo,
    id: 'ORGANIZATION_TYPE',
    options: {
        ...dropdownInfo.options,
        valueOptions: {
            1: 'Charity',
            2: 'Enterprise',
            3: 'Large',
            4: 'Medium',
            5: 'Micro',
            6: 'Not for profit',
            7: 'PE-backed',
            8: 'Private',
            9: 'Public',
            10: 'Small',
            11: 'Startup',
        },
    },
};

export const POSITION_TYPE = {
    ...dropdownInfo,
    id: 'POSITION_TYPE',
    options: {
        ...dropdownInfo.options,
        valueOptions: {
            1: 'Full-time',
            2: 'Part-time',
            3: 'Contract',
            4: 'Freelance',
        },
    },
};

// Radios
const radioListInfo = getFieldInfo('RADIO_LIST');

export const ATTENDANCE_TYPE = {
    ...radioListInfo,
    id: 'ATTENDANCE_TYPE',
    options: {
        ...radioListInfo.options,
        valueOptions: {
            1: 'Hybrid',
            2: 'Remote',
            3: 'In person',
        },
    },
    nameKey: 'ATTENDANCE',
};

// Ratings
const ratingInfo = getFieldInfo('RATING');

export const RATING = {
    ...ratingInfo,
    id: 'RATING',
};

export const DIFFICULTY = {
    ...ratingInfo,
    id: 'DIFFICULTY',
};

// Duration
const durationInfo = getFieldInfo('DURATION');
const durationRangeInfo = getFieldInfo('DURATION_RANGE');

export const COMMUTE_DURATION = {
    ...durationRangeInfo,
    id: 'COMMUTE_DURATION',
};

export const DURATION = {
    ...durationInfo,
    id: 'DURATION',
};

export const NOTICE = {
    ...durationInfo,
    id: 'NOTICE',
};

export const TRIP_DURATION_RANGE = {
    ...durationRangeInfo,
    id: 'TRIP_DURATION_RANGE',
};

// Money
const moneyRangeInfo = getFieldInfo('MONEY_RANGE');
const moneyInfo = getFieldInfo('MONEY');

export const ESTIMATED_COST = {
    ...moneyRangeInfo,
    id: 'ESTIMATED_COST',
    options: {
        ...moneyRangeInfo.options,
        currency: null,
    },
};

export const COST = {
    ...moneyInfo,
    id: 'COST',
    options: {
        ...moneyInfo.options,
        currency: null,
    },
};

export const VALUE = {
    ...COST,
    id: 'VALUE',
};

export const SALARY_RANGE = {
    ...moneyRangeInfo,
    id: 'SALARY_RANGE',
    options: {
        ...moneyRangeInfo.options,
        currency: null,
    },
};

export const SALARY_EXPECTATION = {
    ...SALARY_RANGE,
    id: 'SALARY_EXPECTATION',
};

// Number
const numberInfo = getFieldInfo('NUMBER');
export const CREDITS = {
    ...numberInfo,
    id: 'CREDITS',
};

// Checkboxes

const checkboxInfo = getFieldInfo('CHECKBOX');

export const IS_AUTO_RENEW = {
    ...checkboxInfo,
    id: 'IS_AUTO_RENEW',
};

// Booleans
const toggleInfo = getFieldInfo('TOGGLE');

export const IS_CURRENT = {
    ...toggleInfo,
    id: 'IS_CURRENT',
};

export const DO_I_HAVE_IT_ALREADY = {
    ...toggleInfo,
    id: 'DO_I_HAVE_IT_ALREADY',
};

export const IVE_DONE_IT = {
    ...toggleInfo,
    id: 'IVE_DONE_IT',
};

export const IS_WEDDING_GUEST = {
    ...toggleInfo,
    id: 'IS_WEDDING_GUEST',
};

// Misc lines
export const TIMEFRAME = {
    ...lineInfo,
    id: 'TIMEFRAME',
};

export const BONUS = {
    ...lineInfo,
    id: 'BONUS',
};

export const ROLE = {
    ...lineInfo,
    id: 'ROLE',
};

export const ORGANIZATION_NAME = {
    ...lineInfo,
    id: 'ORGANIZATION_NAME',
};

export const LOCATION_LINE = {
    ...lineInfo,
    id: 'LOCATION_LINE',
};

export const SERVICE_PROVIDER = {
    ...lineInfo,
    id: 'SERVICE_PROVIDER',
};

export const PROVIDER = {
    ...lineInfo,
    id: 'PROVIDER',
};

export const INSURANCE_PROVIDER = {
    ...lineInfo,
    id: 'INSURANCE_PROVIDER',
};

export const POLICY_NUMBER = {
    ...lineInfo,
    id: 'POLICY_NUMBER',
};

export const CREDITS_LINE = {
    ...lineInfo,
    id: 'CREDITS_LINE',
};

export const GRADE = {
    ...lineInfo,
    id: 'GRADE',
};

export const SOURCE = {
    ...lineInfo,
    id: 'SOURCE',
};

export const CUISINE = {
    ...lineInfo,
    id: 'CUISINE',
};

export const QUANTITY_LINE = {
    ...lineInfo,
    id: 'QUANTITY_LINE',
    name: 'QUANTITY',
};

export const GOALS = {
    ...lineInfo,
    options: {
        ...lineInfo.options,
        list: true,
    },
    id: 'GOALS',
};

export const AMENITIES = {
    ...lineInfo,
    options: {
        ...lineInfo.options,
        list: true,
    },
    id: 'AMENITIES',
};

export const VIN = {
    ...lineInfo,
    id: 'VIN',
};
export const MAKE = {
    ...lineInfo,
    id: 'MAKE',
};
export const MODEL = {
    ...lineInfo,
    id: 'MODEL',
};
export const YEAR = {
    ...lineInfo,
    id: 'YEAR',
};
export const COLOR = {
    ...lineInfo,
    id: 'COLOR',
};
export const LICENSE_PLATE = {
    ...lineInfo,
    id: 'LICENSE_PLATE',
};

export const CHIP_ID = {
    ...lineInfo,
    id: 'CHIP_ID',
};

// Misc paragraphs
export const BENEFITS = {
    ...paragraphInfo,
    id: 'BENEFITS',
};

export const REQUIREMENTS = {
    ...paragraphInfo,
    id: 'REQUIREMENTS',
};

export const SCHEDULE = {
    ...paragraphInfo,
    id: 'SCHEDULE',
};

export const EXTRACURRICULARS = {
    ...paragraphInfo,
    id: 'EXTRACURRICULARS',
};

export const CAMPUS_LIFE = {
    ...paragraphInfo,
    id: 'CAMPUS_LIFE',
};

export const PLAN = {
    ...paragraphInfo,
    id: 'PLAN',
};

export const WHAT_I_LIKE = {
    ...paragraphInfo,
    id: 'WHAT_I_LIKE',
};

export const PREP_AHEAD = {
    ...paragraphInfo,
    id: 'PREP_AHEAD',
};

export const MANUSCRIPT_REQUIREMENTS = {
    ...paragraphInfo,
    id: 'MANUSCRIPT_REQUIREMENTS',
};

export const COVERAGE_DETAILS = {
    ...paragraphInfo,
    id: 'COVERAGE_DETAILS',
};

export const PET_NEEDS = {
    ...paragraphInfo,
    id: 'PET_NEEDS',
};

export const DIET_INFO = {
    ...paragraphInfo,
    id: 'DIET_INFO',
};

export const MEDICAL_INFO = {
    ...paragraphInfo,
    id: 'MEDICAL_INFO',
};

// Multifield 😰
const multiInfo = getFieldInfo('MULTI');

export const BASIC_POSITIONS = {
    ...multiInfo,
    id: 'BASIC_POSITIONS',
    options: {
        ...multiInfo.options,
        list: true,
        fields: [
            ROLE,
            ORGANIZATION_NAME,
            DESCRIPTION,
            IS_CURRENT,
            POSITION_TYPE,
        ],
    },
    nameKey: 'POSITION',
};

export const EQUIPMENT = {
    ...multiInfo,
    id: 'EQUIPMENT',
    options: {
        ...multiInfo.options,
        list: true,
        fields: [
            NAME,
            QUANTITY_LINE,
            DO_I_HAVE_IT_ALREADY,
        ],
    },
};

export const INSURANCE_INFO = {
    ...multiInfo,
    id: 'INSURANCE_INFO',
    options: {
        ...multiInfo.options,
        fields: [
            INSURANCE_PROVIDER,
            POLICY_NUMBER,
            COVERAGE_DETAILS,
            START_DATE,
        ],
    },
};
