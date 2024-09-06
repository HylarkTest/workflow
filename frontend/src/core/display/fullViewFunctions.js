import { getFields } from '@/core/display/getAllEntityData.js';
import { getBasicFormattedData } from '@/core/display/theStandardizer.js';
import { isValueFilled } from '@/core/utils.js';

export const selfEditDisplays = [
    'TOGGLE',
    'CHECKBOX',
    'ICON_TOGGLE',
    'RATING',
    'TOGGLE_LIST',
    'CHECKBOX_LIST',
    'RADIO_LIST',
    'DROPDOWN',
    'ICON',
];

const selfEditFields = [
    'BOOLEAN',
    'RATING',
    'SELECT',
    'ICON',
];

export function isBooleanField(fieldType) {
    return fieldType === 'BOOLEAN';
}

export function isSelfEditField(fieldType) {
    return selfEditFields.includes(fieldType);
}

export function gimmeThePathToGetTheValue(fieldInfo, index = null) {
    const isList = fieldInfo.info?.options?.list;

    let prefix = `${fieldInfo.id}`;

    if (isList) {
        prefix = `${prefix}.listValue`;

        if (!_.isNull(index)) {
            prefix = `${prefix}.${index}`;
        }
    }

    prefix = `${prefix}.fieldValue`;

    return `${prefix}.`;
}

function isFieldList(field) {
    return field.info?.options?.list;
}

function hasItemData(field, itemData) {
    const fieldInfo = field.info;
    const subFields = fieldInfo.options?.subFields;

    // const fieldVal = itemData?.fieldValue?.[field.id] || itemData?.[field.id]?;

    if (itemData) {
        // Building the path for the value
        let fieldValuePath = '';

        if (itemData.fieldValue) {
            fieldValuePath = fieldValuePath.concat(`fieldValue.${field.id}`);
        }
        if (itemData[field.id]) {
            fieldValuePath = fieldValuePath.concat([field.id]);
        }
        const tempFieldVal = _.get(itemData, fieldValuePath);

        if (tempFieldVal?.listValue) {
            fieldValuePath = fieldValuePath.concat('.listValue');
        }

        const fieldVal = _.get(itemData, fieldValuePath);

        if (fieldInfo.fieldType === 'MULTI') {
            // Though this is a repetition from fields other than multi, leaving
            // it separately for now as this check will need to become more
            // sophisticated
            if (isFieldList(field)) {
                const listValLength = fieldVal?.length;
                return listValLength;
            }

            if (!subFields.length) {
                return false;
            }

            return _(subFields).some((subField) => {
                return (selfEditFields.includes(subField.info.subType) && !isFieldList(subField))
                    || hasItemData(subField, fieldVal);
            });
        }

        if (isFieldList(field)) {
            const listValLength = fieldVal?.length;
            return listValLength;
        }

        return isValueFilled(fieldVal);
    }

    return false;
}

function showDataCheck(field, itemData) {
    return (selfEditDisplays.includes(field.info.subType) && !isFieldList(field))
        || hasItemData(field, itemData);
}

function showData(field, itemData) {
    if (_.isArray(itemData?.listValue)) {
        return _(itemData.listValue).some((dataObj) => {
            return showDataCheck(field, dataObj);
        });
    }
    return showDataCheck(field, itemData);
}

function findSectionName(id, sections) {
    return _.find(sections, { id }).name;
}

function getFieldsWithoutEmptySubfields(fields, itemData) {
    const finalFields = [];
    fields.forEach((field) => {
        if (field.info.fieldType !== 'MULTI') {
            finalFields.push(field);
        } else {
            const mainFieldData = itemData[field.formattedId];
            const fieldObj = field;
            const subFields = fieldObj.info.options.subFields;
            fieldObj.info.options.subFields = subFields.filter((subField) => {
                return showData(subField, mainFieldData);
            });
            finalFields.push(fieldObj);
        }
    });
    return finalFields;
}

export function getFullFieldsInfoDefault(fieldContainer, itemData = null) {
    // Returns everything if no item data
    const sections = fieldContainer.sections;

    const fields = getFields(fieldContainer);

    const formatted = getBasicFormattedData(fields, 'FIELDS');

    let finalFields = formatted;

    if (itemData) {
        finalFields = getFieldsWithoutEmptySubfields(formatted, itemData);
    }

    const withSection = finalFields.filter((field) => {
        const condition = field.info.section;
        if (itemData) {
            return condition && showData(field, itemData);
        }
        return condition;
    });

    const withoutSection = finalFields.filter((item) => {
        return !item.info.section;
    });

    const grouped = _(withSection).groupBy((item) => {
        return item.info.section;
    }).value();

    const nameNoSection = withoutSection.filter((field) => {
        const condition = field.info.fieldType === 'NAME' || field.info.fieldType === 'SYSTEM_NAME';
        if (itemData) {
            return condition && showData(field, itemData);
        }
        return condition;
    });

    const connectionNoSection = withoutSection.filter((field) => {
        const condition = ['EMAIL', 'PHONE', 'ADDRESS', 'URL'].includes(field.info.fieldType);
        if (itemData) {
            return condition && showData(field, itemData);
        }
        return condition;
    });

    const imageNoSection = withoutSection.filter((field) => {
        const condition = field.info.fieldType === 'IMAGE';
        if (itemData) {
            return condition && showData(field, itemData);
        }
        return condition;
    });

    const fileNoSection = withoutSection.filter((field) => {
        const condition = field.info.fieldType === 'FILE';
        if (itemData) {
            return condition && showData(field, itemData);
        }
        return condition;
    });

    let arr = [];

    if (nameNoSection.length) {
        const panel = {
            header: '',
            id: _.random(1, 10000, 5),
            fields: nameNoSection,
        };

        arr = arr.concat(panel);
    }

    if (connectionNoSection.length) {
        const panel = {
            header: 'Connection',
            id: _.random(1, 10000, 5),
            fields: connectionNoSection,
        };

        arr = arr.concat(panel);
    }

    if (imageNoSection.length) {
        const panel = {
            header: 'Images',
            id: _.random(1, 10000, 5),
            fields: imageNoSection,
        };

        arr = arr.concat(panel);
    }

    if (fileNoSection.length) {
        const panel = {
            header: 'Files',
            id: _.random(1, 10000, 5),
            fields: fileNoSection,
        };

        arr = arr.concat(panel);
    }

    if (withSection.length) {
        _(grouped).forEach((group, key) => {
            const panel = {
                header: findSectionName(key, sections),
                id: _.random(1, 10000, 5),
                fields: group,
            };
            arr = arr.concat(panel);
        });
    }
    const usedFields = _(arr).flatMap((item) => {
        return item.fields;
    }).value();

    const unusedFields = _.differenceBy(finalFields, usedFields, 'formattedId');

    let filteredUnused = unusedFields;

    if (itemData) {
        filteredUnused = unusedFields.filter((field) => {
            return showData(field, itemData);
        });
    }

    if (filteredUnused.length) {
        const unusedPanel = {
            header: '',
            id: _.random(1, 10000, 5),
            fields: filteredUnused,
        };

        arr = arr.concat(unusedPanel);
    }

    return arr;
}

export default {
    getFullFieldsInfoDefault,
};
