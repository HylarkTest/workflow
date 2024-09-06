// Put anything in, and it gives you a standardized format out

import { $t } from '@/i18n.js';

const noHierarchyDisplayOption = ['LIST_COUNT'];

export function getId(obj) {
    if (obj.group) {
        return obj.group.id;
    }
    if (obj.id) {
        return obj.id;
    }
    return obj.val;
}

export function getName(obj, type) {
    if (obj.group) {
        return obj.group.name;
    }
    if (obj.name) {
        return obj.name;
    }
    if (obj.val) {
        return $t(`labels.${_.camelCase(obj.val)}`);
    }
    if (type === 'X') {
        return '';
    }

    return '';
}

function hasValidMultiDisplayOption(displayOption) {
    // Excluded for multi-fields due to it not applying to subfields
    const included = !noHierarchyDisplayOption.includes(displayOption);
    return !!included;
}

// TEMP to be able to work due to issue saving
function getOptions(options) {
    if (_.isArray(options)) {
        return {};
    }
    return _.clone(options);
}

export function getFormattedId(option, displayOption, dataType, parent) {
    // Unique id for loops and specialized behavior, where id can be shared
    // between two or more things (they have different options)
    const id = getId(option);

    let newId = id;

    // The id of relationships should stay the same
    // despite the display option
    const excludeVal = []; // Add excluded field vals here if any
    const excludeDataType = ['RELATIONSHIPS'];
    const exclusions = excludeVal.includes(option.val)
        || excludeDataType.includes(dataType);

    if (displayOption && !exclusions) {
        newId = `${id}.${displayOption}`;
    }

    const parentId = parent?.formattedId;

    if (parentId) {
        newId = `${parentId}>${newId}`;
    }
    return newId;
}

export function convertToFunctionalFormat(dataType, option, displayOption = null, parent = null, extras = {}) {
    const formattedId = getFormattedId(option, displayOption, dataType, parent);
    const obj = {
        dataType,
        id: getId(option),
        name: getName(option),
        formattedId,
    };

    if (dataType === 'COLLABORATION') {
        obj.displayOption = option.val;
    }

    if (displayOption) {
        obj.displayOption = displayOption;
    }

    if (dataType === 'MARKERS') {
        obj.info = {
            subType: option.group.type,
            group: option.group,
            groupId: option.id,
        };
    }

    if (dataType === 'RELATIONSHIPS') {
        obj.info = {
            name: option.name,
        };
    }

    if (dataType === 'FIELDS') {
        obj.info = {
            subType: option.val,
            fieldType: option.type,
            section: option.section,
            meta: option.meta,
            options: getOptions(option.options),
        };

        if (option.val === 'MULTI') {
            const validDisplay = hasValidMultiDisplayOption(displayOption);

            if (!validDisplay) {
                obj.info.noHierarchy = true;
            }

            if (extras.multiForSelection) {
                obj.info.options.isGrouped = true;

                // Get the subSections if the multi-field has a valid displayOption with a hierarchy
                if (validDisplay) {
                    if (displayOption) {
                        obj.info.options.hasSubSections = true;
                    }

                    // Get the subFields by converting them to the standard format
                    obj.info.options.hasSubOptions = true;
                    obj.info.options.subOptionsKey = 'subFields'; // In case other types have subOptions
                }
            }

            const useDisplayOption = validDisplay && extras.displayOptionMultiFields;

            obj.info.options.subFields = _(option.options.fields).flatMap((field) => {
                let newFields = [];
                if (useDisplayOption && field.displayOptions) {
                    const displayFields = field.displayOptions.map((fieldOption) => {
                        return convertToFunctionalFormat('FIELDS', field, fieldOption, obj);
                    });
                    newFields = _.concat(newFields, displayFields);
                }
                const plainFields = convertToFunctionalFormat('FIELDS', field, null, obj);
                newFields = _.concat(newFields, plainFields);
                return newFields;
            }).value();

            // Set the subFields on the parent object within subFields
            if (obj.info.options.subFields) {
                obj.info.options.subFields.forEach((subField) => {
                    // eslint-disable-next-line no-param-reassign
                    subField.info.parent = _.cloneDeep(obj);
                });
            }
        }
    }
    if (dataType === 'SYSTEM') {
        obj.info = {
            subType: option.val,
        };
    }

    return obj;
}

function flatMapThroughDataCategoriesWithDisplayOptions(categoryData, categoryKey, extras) {
    return categoryData.flatMap((item) => {
        const displayOptions = item.displayOptions;
        if (!displayOptions?.length) {
            return convertToFunctionalFormat(categoryKey, item, null, null, extras);
        }
        return displayOptions.map((option) => {
            return convertToFunctionalFormat(categoryKey, item, option, null, extras);
        });
    });
}

export function getExpandedData(data) {
    // Gets flattened data of all available displays on summary cards
    // Includes displayOptions
    // Expands subFields

    // Data is an object with the keys COLLABORATION,
    // FEATURES, FIELDS, RELATIONSHIPS, and SYSTEM
    // It is all of the data related to a blueprint

    const extras = {
        displayOptionMultiFields: true,
    };

    const basicData = _(data).flatMap((category, key) => {
        return flatMapThroughDataCategoriesWithDisplayOptions(category, key, extras);
    }).value();

    const unpacked = _(basicData).flatMap((item) => {
        if (item.info?.options?.subFields && !item.info.noHierarchy) {
            return item.info.options.subFields;
        }
        return item;
    }).value();

    return unpacked;
}

export function getPickerOptions(data, returnFormat = 'obj') {
    // Gets options for selection
    // Includes displayOptions
    // DOES NOT expand subFields

    // Return format is 'obj' or 'arr'

    // multiForSelection adds a few extra options to help with organizing the data
    // displayOptionMultiFields adds the displayOptions for the multi's subfields
    const extras = {
        multiForSelection: true,
        displayOptionMultiFields: true,
    };

    if (returnFormat === 'arr') {
        return _(data).flatMap((category, key) => {
            return flatMapThroughDataCategoriesWithDisplayOptions(category, key, extras);
        }).value();
    }
    return _.mapValues(data, (category, key) => {
        return flatMapThroughDataCategoriesWithDisplayOptions(category, key, extras);
    });
}

export function getBasicFormattedData(data, dataType) {
    // Gets the formatted data without display options, just the full data
    // Currently accepts an array
    // Currently returns an array

    // Can expand to accept an object, return an object,
    // making the dataType prop optional

    const extras = {
        plainMultiSubfields: true,
    };

    return _(data).flatMap((item) => {
        return convertToFunctionalFormat(dataType, item, null, null, extras);
    }).value();
}

export function getAllAvailableDataFormatted(data) {
    // Gets flattened data of all available displays on summary cards
    // Includes displayOptions
    // Includes the base data of the one with display options as well
    // Expands subFields

    // Data is an object with the keys COLLABORATION,
    // FEATURES, FIELDS, RELATIONSHIPS, and SYSTEM
    // It is all of the data related to a blueprint

    const extras = {
        displayOptionMultiFields: true,
        plainMultiSubfields: true,
    };

    const basicData = _(data).flatMap((category, key) => {
        return _(category).flatMap((item) => {
            const displayOptions = item.displayOptions;
            const displayLength = displayOptions?.length;

            let vals = [];

            if (key === 'FIELDS' || !displayLength) {
                vals = _.concat(vals, convertToFunctionalFormat(key, item, null, null, extras));
            }
            if (displayLength) {
                const displayOptionsArr = displayOptions.map((option) => {
                    return convertToFunctionalFormat(key, item, option, null, extras);
                });
                vals = _.concat(vals, displayOptionsArr);
            }
            return vals;
        }).value();
    }).value();

    // Display the field itself, and the options IF it's a valid display

    const unpacked = _(basicData).flatMap((item) => {
        let vals = [];
        if (item.info?.options?.subFields && !item.info.noHierarchy) {
            vals = _.concat(vals, item.info.options.subFields);
        }
        vals = _.concat(vals, item);
        return vals;
    }).value();

    return unpacked;
}

export function visibleDataFlatAndFormatted(visibleData, allData) {
    // Data can be the object of the unformatted data, or the
    // already flat and formatted data
    let formattedData;
    if (_.isArray(allData)) {
        formattedData = allData;
    } else {
        formattedData = getExpandedData(allData);
    }
    return visibleData?.map((data) => {
        const existing = _.find(formattedData, { formattedId: data.formattedId });

        return existing && {
            ...existing,
            dataType: data.dataType,
            slot: data.slot,
            width: data.width,
            combo: data.combo,
            designAdditional: data.designAdditional,
        };
    }).filter((data) => data);
}

export function itemDisplayFlatAndFormatted(itemDisplay, allData, itemData = null) {
    const formattedData = getBasicFormattedData(allData, 'FIELDS');
    const itemDisplayFields = _(itemDisplay).flatMap((section) => {
        return _.map(section.fields);
    }).value();

    const unusedFields = _.differenceBy(formattedData, itemDisplayFields, 'formattedId');
    let allSections = itemDisplay;
    if (unusedFields?.length) {
        allSections = allSections.concat({
            fields: unusedFields,
            header: '',
            id: _.random(1, 10000, 5),
        });
    }
    return allSections.map((section) => {
        const fieldsArr = _(section.fields).map((data) => {
            if (itemData && !itemData[data.formattedId]) {
                return null;
            }
            const existing = _.find(formattedData, { formattedId: data.formattedId });

            return {
                ...existing,
                dataType: data.dataType,
            };
        }).filter('formattedId').value();

        return {
            ...section,
            fields: fieldsArr,
        };
    });
}

export function getTextPaths(info, type) {
    const val = _.camelCase(info.subType);
    if (type === 'MARKERS') {
        return `customizations.${val}.name`;
    }
    return `labels.${val}`;
}

export function getTextStrings(item) {
    const info = item.info;
    const type = item.dataType;
    return $t(getTextPaths(info, type));
}

// Not adding to multifield for now due to not
// knowing if the assumption is valid. May change later.
export function provideFieldValue(field, fieldVal) {
    const options = field.info?.options;
    const isList = options?.list;

    // List fields
    if (isList) {
        return {
            listValue: [{
                fieldValue: fieldVal,
            }],
        };
    }

    // Single value fields
    return {
        fieldValue: fieldVal,
    };
}

export function getFieldValue(field, fieldVal) {
    const options = field.info?.options || field.options;
    const isList = options?.list;

    // List fields
    if (isList) {
        return _.map(fieldVal.listValue, 'fieldValue');
    }

    // Single value fields
    return fieldVal.fieldValue;
}

export function getFieldValuesByType(allFields, itemData, fieldType) {
    // allFields is from the blueprint
    const typeFields = allFields.filter((field) => field.type === fieldType);

    return _(typeFields).flatMap((field) => {
        const fieldVal = itemData[field.id];
        return getFieldValue(field, fieldVal);
    }).compact().value();
}

// Helpers for user selections to do with the formatting in this file
function getSubGroupsForSelection(group) {
    return group.filter((item) => {
        if (item.info?.options) {
            return item.info.options.isGrouped;
        }
        return false;
    });
}

export function getSubSourceForSelection(group) {
    const subGroup = getSubGroupsForSelection(group);

    const length = subGroup.length;

    return length ? subGroup : [group];
}

export function getSubOptionsForSelection(sub) {
    const options = sub.info?.options;
    let subs = sub;
    if (options?.hasSubOptions) {
        const subKey = options.subOptionsKey;
        subs = options[subKey];
    }
    if (!_.isArray(subs)) {
        return [subs];
    }
    return subs;
}

export function getGroupHeader(groupKey, group) {
    const first = group[0];
    if (first.dataType === 'FIELDS') {
        return first.name;
    }
    if (first.dataType === 'RELATIONSHIPS') {
        return first.name;
    }
    const camel = _.camelCase(groupKey);
    return $t(`labels.${camel}`);
}

export function getGroupedDataFromSections(data, dataType) {
    // Formats the data within FIELDS, FEATURES, COLLABORATION...
    // No grouping arr: ['RELATIONSHIPS', 'COLLABORATION', 'SYSTEM'];
    if (dataType === 'FIELDS') {
        return _.groupBy(data, (item) => {
            if (item.info.options?.isGrouped) {
                return item.id;
            }
            return 'undefined';
        });
    }
    if (dataType === 'FEATURES' || dataType === 'RELATIONSHIPS') {
        return _.groupBy(data, 'id');
    }
    return { undefined: data };
}

export default {
    getName,
    convertToFunctionalFormat,
    getTextPaths,
    getTextStrings,
    getFormattedId,
};
