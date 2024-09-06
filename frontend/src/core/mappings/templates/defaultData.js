import { warn } from 'vue';

import * as defaultVals from '@/core/mappings/templates/defaultDataValues.js';

import { getMarkerGroupsFromPages } from '@/core/mappings/templates/helpers.js';

const featuresWithDefaults = ['PRIORITIES', 'FAVORITES'];

const defaultFeatures = {
    PRIORITIES: defaultVals.priorities,
    FAVORITES: defaultVals.favorites,
};

export const defaultPeople = [
    {
        SYSTEM_NAME: 'Sophia Craig (example)',
        FULL_NAME: 'Sophia Craig',
        FIRST_NAME: 'Sophia',
        LAST_NAME: 'Craig',
        PREFERRED_NAME: 'Sophie',
        IMAGE: 'images/defaultPeople/person1.png',
    },
    {
        SYSTEM_NAME: 'Frances Tran (example)',
        FULL_NAME: 'Frances Tran',
        FIRST_NAME: 'Frances',
        LAST_NAME: 'Tran',
        IMAGE: 'images/defaultPeople/person2.png',
    },
    {
        SYSTEM_NAME: 'Konrad Altmeier (example)',
        FULL_NAME: 'Konrad Altmeier',
        FIRST_NAME: 'Konrad',
        LAST_NAME: 'Altmeier',
        IMAGE: 'images/defaultPeople/person3.png',
    },
    {
        SYSTEM_NAME: 'Carl Martinez (example)',
        FULL_NAME: 'Carl Martinez',
        FIRST_NAME: 'Carl',
        LAST_NAME: 'Martinez',
        IMAGE: 'images/defaultPeople/person4.png',
    },
    {
        SYSTEM_NAME: 'Annie Brochand (example)',
        FULL_NAME: 'Annie Brochand',
        FIRST_NAME: 'Annie',
        LAST_NAME: 'Brochand',
        IMAGE: 'images/defaultPeople/person5.png',
    },
    {
        SYSTEM_NAME: 'Fiona Lam (example)',
        FULL_NAME: 'Fiona Lam',
        FIRST_NAME: 'Fiona',
        LAST_NAME: 'Lam',
        IMAGE: 'images/defaultPeople/person6.png',
    },
    {
        SYSTEM_NAME: 'Zara Andrade (example)',
        FULL_NAME: 'Zara Andrade',
        FIRST_NAME: 'Zara',
        LAST_NAME: 'Andrade',
        IMAGE: 'images/defaultPeople/person7.png',
    },
    {
        SYSTEM_NAME: 'Aaron Huffman (example)',
        FULL_NAME: 'Aaron Huffman',
        FIRST_NAME: 'Aaron',
        LAST_NAME: 'Huffman',
        IMAGE: 'images/defaultPeople/person8.png',
    },
    {
        SYSTEM_NAME: 'Ana Maria Pereira (example)',
        FULL_NAME: 'Ana Maria Pereira',
        FIRST_NAME: 'Ana Maria',
        LAST_NAME: 'Pereira',
        IMAGE: 'images/defaultPeople/person9.png',
    },
    {
        SYSTEM_NAME: 'José da Rocha (example)',
        FULL_NAME: 'José da Rocha',
        FIRST_NAME: 'José',
        LAST_NAME: 'da Rocha',
        IMAGE: 'images/defaultPeople/person10.png',
    },
];

const specificDefaultValues = {
    // Set specific default here with the key matching the exampleKey
};

function getPersonValues(fieldIds, indexes) {
    const typeIndex = indexes.typeIndex;
    const doubledIndex = typeIndex * 2;
    const personIndex = doubledIndex + indexes.defaultIndex;
    const person = defaultPeople[personIndex];
    const personKeys = _.keys(person);
    const validKeys = _.intersection(personKeys, fieldIds);
    const extraKeys = _.difference(personKeys, fieldIds);

    const personObj = {};

    validKeys.forEach((validKey) => {
        personObj[validKey] = { fieldValue: person[validKey] };
    });

    if (extraKeys?.length) {
        personObj.extras = {};
        extraKeys.forEach((extraKey) => {
            personObj.extras[extraKey] = { fieldValue: person[extraKey] };
        });
    }

    return personObj;
}

function getFieldValue(fieldData, field, index) {
    const exampleKey = field.exampleKey;
    const camelKey = _.camelCase(exampleKey);
    const defaultFunctionExists = _.has(defaultVals, camelKey);
    if (defaultFunctionExists) {
        const defaultVal = defaultVals[camelKey](fieldData, index);
        const val = defaultVal || specificDefaultValues[exampleKey](fieldData, index);

        let fieldValObj = { fieldValue: val };

        const isList = field.options?.list;
        const labeled = field.options?.labeled;

        if (labeled) {
            const labelName = labeled.freeText ? 'label' : 'labelKey';
            const labelVal = labeled.freeText ? 'Example' : 1;
            fieldValObj[labelName] = labelVal;
        }
        if (isList) {
            fieldValObj = { listValue: [fieldValObj] };
        }
        return fieldValObj;
    }
    warn(`It looks like you are trying to get a default that does not exist in the defaultVals,
        namely "${exampleKey}". Add it in defaultVals.`);
    return null;
}

function getFieldDefaults(page, indexes, specifics) {
    let fieldData;
    const isPerson = page.type === 'PERSON';
    if (isPerson) {
        fieldData = getPersonValues(_.map(page.fields, 'id'), indexes);
    } else {
        const itemName = page.singularName;
        fieldData = {
            SYSTEM_NAME: { fieldValue: `${itemName} ${indexes.defaultIndex + 1}` },
        };

        const imageFields = _.filter(page.fields, ['type', 'IMAGE']);
        if (imageFields.length) {
            imageFields.forEach((imageField) => {
                const id = imageField.id;
                fieldData[id] = getFieldValue(fieldData, { exampleKey: 'IMAGE' }, indexes.typeIndex);
            });
        }
    }
    const remainingFields = _.differenceWith(page.fields, _.keys(fieldData), (first, second) => {
        return first.id === second;
    });
    const setDefaults = specifics?.FIELDS;
    const specificKeys = _.keys(setDefaults);
    remainingFields.forEach((field) => {
        if (specificKeys.includes(field.id)) {
            fieldData[field.id] = { fieldValue: setDefaults[field.id] };
        } else if (field.exampleKey) {
            fieldData[field.id] = getFieldValue(fieldData, field);
        }
    });
    return fieldData;
}

function getFeatureDefaults(features) {
    const featuresObj = {};
    features.forEach((feature) => {
        featuresObj[feature] = defaultFeatures[feature]();
    });
    return featuresObj;
}

function getRandomMarker(markerGroup) {
    const markers = markerGroup.markers;

    if (!markers?.length) {
        warn(`Check that the keys and ids associated with "${markerGroup.id}"
            are all consistent and have the correct spelling.`);
    }
    const length = markers.length;
    const index = _.random(0, length - 1);
    const selectedMarker = markers[index].id;
    return selectedMarker;
}

function getMarkerDefaults(page, markerGroups, specifics) {
    const markers = {};
    markerGroups.forEach((markerGroup) => {
        const id = markerGroup.id;
        const setDefaults = specifics?.MARKERS;

        const newMarker = getRandomMarker(markerGroup);
        const arrayType = ['PIPELINE', 'TAG'];
        const isArrayType = arrayType.includes(markerGroup.type);
        if (isArrayType) {
            markers[id] = [newMarker];
        } else {
            markers[id] = newMarker;
        }
        if (setDefaults) {
            const keys = _.keys(setDefaults);
            if (keys.includes(id)) {
                const defaultMarkers = setDefaults[id];
                if (isArrayType) {
                    const mergedMarkers = markers[id].concat(defaultMarkers);
                    markers[id] = _.uniq(mergedMarkers);
                } else {
                    // TODO: Review later if the marker is a status type and there are more than one
                    markers[id] = defaultMarkers;
                }
            }
        }
    });
    return markers;
}

export function getDefaults(page, indexes) {
    // Done out here so it doesn't get redone multiple times
    const markerGroups = getMarkerGroupsFromPages([page]);
    const features = _.map(page.features, 'val');
    const specifics = page.specificDefaults;

    return [0, 1].map((item) => {
        const newIndexes = {
            ...indexes,
            defaultIndex: item,
        };
        const defaultObj = {
            data: getFieldDefaults(page, newIndexes, specifics),
        };
        if (page.markerGroups?.length) {
            defaultObj.markers = getMarkerDefaults(page, markerGroups, specifics);
        }
        const featuresOverlap = _.intersection(features, featuresWithDefaults);
        if (featuresOverlap?.length) {
            defaultObj.features = getFeatureDefaults(featuresOverlap);
        }

        return defaultObj;
    });
}
