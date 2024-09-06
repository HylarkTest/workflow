import { infoOptions } from '@/core/display/checkUse/infoOptionsList.js';
import { makeRows, makeRowsOrSub, makeContainers } from '@/core/display/checkUse/fullDataFunctions.js';

function listTags(tags) {
    const tagsArr = tags.map((tag) => {
        return {
            elementType: 'ROW',
            id: Math.random(),
            containers: makeContainers([tag], 'TAGS'),
        };
    });
    return [{
        elementType: 'SECTION',
        id: Math.random(),
        sectionName: 'Tags',
        elements: tagsArr,
    }];
}

function listFreeFields(freeFields) {
    return makeRows(freeFields);
}

function getSectionName(id, sections) {
    return _.find(sections, { id }).name;
}

function getSectionElements(section) {
    return makeRowsOrSub(section);
}

function listSections(filledSections, page) {
    return _(filledSections).map((section, key) => {
        return {
            id: Math.random(),
            elementType: 'SECTION',
            sectionName: getSectionName(key, page.sections),
            elements: getSectionElements(section),
        };
    }).value();
}

function multiSections(multiFields) {
    return multiFields.map((field) => {
        return {
            id: Math.random(),
            elementType: 'SECTION',
            sectionName: field.name,
            elements: getSectionElements(field.options.fields.map((subField) => {
                return {
                    ...subField,
                    id: `${field.id}.${subField.id}`,
                };
            })),
        };
    });
}

function getInfoElements() {
    return infoOptions.map((option) => {
        return {
            elementType: 'ROW',
            id: Math.random(),
            containers: makeContainers([option], 'INFO'),
        };
    });
}

function infoSection() {
    return {
        id: Math.random(),
        elementType: 'SECTION',
        sectionName: 'Information',
        elements: getInfoElements(),
    };
}

export function getAllDefault(page) {
    const tags = page.tagGroups;
    const fields = page.fields;
    const nonMultiSection = fields.filter((field) => {
        return field.type !== 'MULTI' || field.section;
    });
    const fieldSections = _.groupBy(nonMultiSection, (field) => {
        return field.section || 'FREE_FIELDS';
    });
    const multiFields = _.filter(fields, { type: 'MULTI', section: null });

    const { FREE_FIELDS: freeFields, ...filledSections } = fieldSections;

    const getTagsList = tags && tags.length ? listTags(tags) : [];
    const getFreeFields = freeFields && freeFields.length ? listFreeFields(freeFields) : [];
    const getSections = _.isEmpty(filledSections) ? [] : listSections(filledSections, page);
    const getMulti = !multiFields.length ? [] : multiSections(multiFields);

    return [
        ...getTagsList,
        ...getFreeFields,
        ...getSections,
        ...getMulti,
        infoSection(),
    ];
}

export default {};
