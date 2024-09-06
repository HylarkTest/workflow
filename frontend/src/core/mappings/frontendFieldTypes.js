// Frontend fields are based off the backend fields, but to users
// we want to avoid the feeling they need to customize a lot of stuff.

// So though some of the fields have the same specific type,
// various options make them seem different, and hopefully more accessible, to users.

import _ from 'lodash';
import { allFields } from '@/core/mappings/fieldTypes.js';

export const frontendFields = [
    {
        val: 'ADDRESS',
        fieldCategory: 'CONNECTION',
        ...allFields.ADDRESS,
    },
    {
        val: 'TOGGLE',
        fieldCategory: 'TRUE_FALSE',
        ...allFields.BOOLEAN,
        meta: {
            display: 'TOGGLE',
        },
    },
    {
        val: 'CHECKBOX',
        fieldCategory: 'TRUE_FALSE',
        ...allFields.BOOLEAN,
        meta: {
            display: 'CHECKBOX',
        },
    },
    {
        val: 'ICON_TOGGLE',
        fieldCategory: 'TRUE_FALSE',
        ...allFields.BOOLEAN,
        meta: {
            display: 'ICON_TOGGLE',
            symbol: 'fa-star-sharp-half-stroke',
        },
    },
    {
        val: 'CATEGORY',
        fieldCategory: 'SELECTIONS',
        ...allFields.CATEGORY,
        options: {
            ...allFields.CATEGORY.options,
        },
    },
    {
        val: 'CURRENCY',
        fieldCategory: 'MONEY',
        ...allFields.CURRENCY,
    },
    {
        val: 'DATE',
        fieldCategory: 'DATES_TIME',
        ...allFields.DATE,
    },
    {
        val: 'DATE_RANGE',
        fieldCategory: 'DATES_TIME',
        ...allFields.DATE,
        options: {
            ...allFields.DATE.options,
            isRange: true,
        },
        meta: {
            display: 'DATE_RANGE',
        },
    },
    {
        val: 'DATE_TIME',
        fieldCategory: 'DATES_TIME',
        ...allFields.DATE_TIME,
    },
    {
        val: 'DATE_TIME_RANGE',
        fieldCategory: 'DATES_TIME',
        ...allFields.DATE_TIME,
        options: {
            ...allFields.DATE_TIME.options,
            isRange: true,
        },
        meta: {
            display: 'DATE_TIME_RANGE',
        },
    },
    {
        val: 'DURATION',
        fieldCategory: 'DATES_TIME',
        ...allFields.DURATION,
    },
    {
        val: 'DURATION_RANGE',
        fieldCategory: 'DATES_TIME',
        ...allFields.DURATION,
        options: {
            ...allFields.DURATION.options,
            isRange: true,
        },
        meta: {
            display: 'DURATION_RANGE',
        },
    },
    {
        val: 'EMAIL',
        fieldCategory: 'CONNECTION',
        ...allFields.EMAIL,
    },
    // {
    //     val: 'FILE',
    //     fieldCategory: 'MAIN',
    //     ...allFields.FILE,
    // },
    {
        val: 'ICON',
        fieldCategory: 'SELECTIONS',
        ...allFields.ICON,
    },
    {
        val: 'IMAGE',
        fieldCategory: 'MAIN',
        ...allFields.IMAGE,
    },
    {
        val: 'LINE',
        fieldCategory: 'MAIN',
        ...allFields.LINE,
    },
    {
        val: 'CITY',
        fieldCategory: 'LOCATION',
        ...allFields.LOCATION,
        options: {
            ...allFields.LOCATION.options,
            levels: ['CITY'],
        },
    },
    {
        val: 'COUNTRY',
        fieldCategory: 'LOCATION',
        ...allFields.LOCATION,
        options: {
            ...allFields.LOCATION.options,
            levels: ['COUNTRY'],
        },
    },
    {
        val: 'CONTINENT',
        fieldCategory: 'LOCATION',
        ...allFields.LOCATION,
        options: {
            ...allFields.LOCATION.options,
            levels: ['CONTINENT'],
        },
    },
    {
        val: 'WORLDWIDE',
        fieldCategory: 'LOCATION',
        ...allFields.LOCATION,
        options: {
            ...allFields.LOCATION.options,
            levels: ['CITY', 'STATE', 'COUNTRY', 'CONTINENT'],
        },
    },
    {
        val: 'MONEY',
        fieldCategory: 'MONEY',
        ...allFields.MONEY,
    },
    {
        val: 'MONEY_RANGE',
        fieldCategory: 'MONEY',
        ...allFields.MONEY,
        options: {
            ...allFields.MONEY.options,
            isRange: true,
        },
        meta: {
            display: 'MONEY_RANGE',
        },
    },
    {
        val: 'SALARY',
        fieldCategory: 'MONEY',
        ...allFields.SALARY,
    },
    {
        val: 'SALARY_RANGE',
        fieldCategory: 'MONEY',
        ...allFields.SALARY,
        options: {
            ...allFields.SALARY.options,
            isRange: true,
        },
        meta: {
            display: 'SALARY_RANGE',
        },
    },
    {
        val: 'MULTI',
        fieldCategory: 'OTHER',
        ...allFields.MULTI,
    },
    {
        val: 'FULL_NAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'FULL_NAME',
        },
    },
    {
        val: 'FIRST_NAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'FIRST_NAME',
        },
    },
    {
        val: 'LAST_NAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'LAST_NAME',
        },
    },
    {
        val: 'PREFERRED_NAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'PREFERRED_NAME',
        },
    },
    {
        val: 'NICKNAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'NICKNAME',
        },
    },
    {
        val: 'NAME',
        fieldCategory: 'NAMES',
        ...allFields.NAME,
        options: {
            ...allFields.NAME.options,
            type: 'NAME',
        },
    },
    {
        val: 'NUMBER',
        fieldCategory: 'MAIN',
        ...allFields.NUMBER,
    },
    {
        val: 'NUMBER_RANGE',
        fieldCategory: 'MAIN',
        ...allFields.NUMBER,
        options: {
            ...allFields.NUMBER.options,
            isRange: true,
        },
        meta: {
            display: 'NUMBER_RANGE',
        },
    },
    {
        val: 'PARAGRAPH',
        fieldCategory: 'MAIN',
        ...allFields.PARAGRAPH,
    },
    // {
    //     val: 'PERCENTAGE',
    //     fieldCategory: 'MAIN',
    //     ...allFields.PERCENTAGE,
    // },
    {
        val: 'PHONE',
        fieldCategory: 'CONNECTION',
        ...allFields.PHONE,
    },
    {
        val: 'RATING',
        fieldCategory: 'MAIN',
        ...allFields.RATING,
    },
    {
        val: 'TOGGLE_LIST',
        fieldCategory: 'SELECTIONS',
        ...allFields.SELECT,
        options: {
            ...allFields.SELECT.options,
            multiSelect: true,
        },
        meta: {
            display: 'TOGGLE_LIST',
        },
    },
    {
        val: 'CHECKBOX_LIST',
        fieldCategory: 'SELECTIONS',
        ...allFields.SELECT,
        options: {
            ...allFields.SELECT.options,
            multiSelect: true,
        },
        meta: {
            display: 'CHECKBOX_LIST',
        },
    },
    {
        val: 'RADIO_LIST',
        fieldCategory: 'SELECTIONS',
        ...allFields.SELECT,
        meta: {
            display: 'RADIO_LIST',
        },
    },
    {
        val: 'DROPDOWN',
        fieldCategory: 'SELECTIONS',
        ...allFields.SELECT,
        meta: {
            display: 'DROPDOWN', // Depends on multiselect
        },
    },
    {
        val: 'TIME',
        fieldCategory: 'DATES_TIME',
        ...allFields.TIME,
    },
    {
        val: 'TIME_RANGE',
        fieldCategory: 'DATES_TIME',
        ...allFields.TIME,
        options: {
            ...allFields.TIME.options,
            isRange: true,
        },
        meta: {
            display: 'TIME_RANGE',
        },
    },
    // {
    //     val: 'TIMEZONE',
    //     fieldCategory: 'DATES_TIME',
    //     ...allFields.TIMEZONE,
    // },
    // {
    //     val: 'TITLE',
    //     fieldCategory: 'NAMES',
    //     ...allFields.TITLE,
    // },
    {
        val: 'URL',
        fieldCategory: 'MAIN',
        ...allFields.URL,
    },

];

export const groupedFields = _.groupBy(frontendFields, 'fieldCategory');

export default { frontendFields, groupedFields };
