// These are all of the basic field types available

// All have section and options
const options = {
    list: false,
    labeled: false,
};

// const list = {
//     max: 5,
// };

// const labeled = {
//     labels: {},
//     freeText: false,
// };

// END ALL

// function randomId() {
//     const time = new Date().getTime();
//     const random = _.random(0, 1000, true);
//     return `${time}${random}`;
// },

export const allFields = {
    ADDRESS: {
        type: 'ADDRESS',
        subFields: {
            LINE1: 'LINE',
            LINE2: 'LINE',
            CITY: 'LINE',
            STATE: 'LINE',
            COUNTRY: 'LINE',
            POSTCODE: 'LINE',
        },
        options,
    },
    BOOLEAN: {
        type: 'BOOLEAN',
        options,
    },
    CATEGORY: {
        type: 'CATEGORY',
        options: {
            category: null,
            multiSelect: false,
            ...options,
        },
    },
    CURRENCY: {
        type: 'CURRENCY',
        options: {
            multiSelect: false,
            only: [],
            ...options,
        },
    },
    DATE: {
        type: 'DATE',
        options,
        // options: {
        //     isRange: false,
        // },
    },
    DATE_TIME: {
        type: 'DATE_TIME',
        options,
        // options: {
        //     isRange: false,
        // },
    },
    DURATION: {
        type: 'DURATION',
        options,
    },
    EMAIL: {
        type: 'EMAIL',
        options,
    },
    // FILE: {
    //     type: 'FILE',
    //     options,
    // },
    ICON: {
        type: 'ICON',
        options: {
            ...options,
        },
    },
    IMAGE: {
        type: 'IMAGE',
        options: {
            croppable: true,
            primary: false,
            ...options,
        },
    },
    LINE: {
        type: 'LINE',
        options,
    },
    LOCATION: {
        type: 'LOCATION',
        options: {
            multiSelect: false,
            levels: null,
            ...options,
        },
    },
    MONEY: {
        type: 'MONEY',
        subFields: {
            CURRENCY: 'CURRENCY',
            AMOUNT: 'NUMBER',
        },
        options: {
            currency: 'USD',
            ...options,
        },
    },
    SALARY: {
        type: 'SALARY',
        subFields: {
            CURRENCY: 'CURRENCY',
            AMOUNT: 'NUMBER',
            PERIOD: 'SELECT',
        },
        options: {
            currency: 'USD',
            ...options,
        },
    },
    MULTI: {
        type: 'MULTI',
        options: {
            fields: [],
            ...options,
        },
    },
    NAME: {
        type: 'NAME',
        options: {
            type: null,
            ...options,
        },
    },
    NUMBER: {
        type: 'NUMBER',
        options,
    },
    PARAGRAPH: {
        type: 'PARAGRAPH',
        options,
    },
    // PERCENTAGE: {
    //     type: 'PERCENTAGE',
    //     options,
    // },
    PHONE: {
        type: 'PHONE',
        options,
    },
    RATING: {
        type: 'RATING',
        options: {
            max: 5,
            ...options,
        },
    },
    SELECT: {
        type: 'SELECT',
        options: {
            valueOptions: {},
            multiSelect: false,
            ...options,
        },
    },
    TIME: {
        type: 'TIME',
        options,
        // options: {
        //     isRange: false,
        // },
    },
    // TIMEZONE: {
    //     type: 'TIMEZONE',
    //     options,
    // },
    URL: {
        type: 'URL',
        options,
    },
};

export default { allFields };
