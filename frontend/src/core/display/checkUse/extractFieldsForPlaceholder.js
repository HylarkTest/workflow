import { lineTypes } from '@/core/display/displayTypes.js';

export const computedFields = [
    {
        name: 'Reversed name',
        dependencies: ['FIRST_NAME', 'LAST_NAME'],
        id: '{LAST_NAME}, {FIRST_NAME}',
    },
    {
        name: 'Professional name',
        dependencies: ['TITLE', 'FIRST_NAME', 'LAST_NAME'],
        id: '{TITLE} {FIRST_NAME} {LAST_NAME}',
    },
    {
        name: 'Full name',
        dependencies: ['FIRST_NAME', 'LAST_NAME'],
        id: '{FIRST_NAME} {LAST_NAME}',
    },
];

const placeholders = {
    GRAPHIC: {
        fields: [
            'IMAGE',
        ],
    },
    CONTENT: {
        fields: lineTypes,
    },
};

export function getPossibleFields(fields, slotType) {
    const options = placeholders[slotType].fields;

    const filteredFields = fields.filter((field) => {
        return options.includes(field.type);
    });

    const fieldTypes = _.map(filteredFields, 'type');

    computedFields.forEach((field) => {
        if (_.intersection(field.dependencies, fieldTypes).length === field.dependencies.length) {
            const computedField = {
                name: field.name,
                id: field.id.replace(/\{([^]*?)\}/g, (ignore, p1) => {
                    return `{${_.find(fields, ['type', p1]).id}}`;
                }),
            };
            filteredFields.unshift(computedField);
        }
    });

    return filteredFields;
}
