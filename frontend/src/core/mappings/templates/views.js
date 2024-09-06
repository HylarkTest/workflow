import { getMarkerGroupsFromPages } from '@/core/mappings/templates/helpers.js';
import { featureOptions } from '@/core/display/getAllEntityData.js';
import { getFormattedId } from '@/core/display/theStandardizer.js';
import { $translationRaw } from '@/i18n.js';

const specificViews = {
    // Page ids with specific values for a page (outlined in getVisibleData vals constant)
};

const rank = {
    // Last in the array has the highest priority in terms of appearing on the card
    FIELDS: ['URL', 'PARAGRAPH', 'PHONE', 'EMAIL'],
    FEATURES: ['PRIORITIES', 'FAVORITES'],
};

function getRank(data, dataType) {
    return _.orderBy(data, (item) => {
        if (_.isObject(item) && 'type' in item) {
            return rank[dataType].indexOf(item.type);
        }
        return rank[dataType].indexOf(item);
    }, 'desc');
}

function getVisibleData(pageId, pageObj) {
    const specific = specificViews[pageId];
    const data = {
        fields: pageObj.fields,
        markerGroups: pageObj.markerGroups,
    };
    if (specific) {
        const vals = [
            'tile',
            'line',
            'markerGroups',
            'fields',
        ];

        vals.forEach((val) => {
            const specificVal = specific[val];
            if (specificVal) {
                data[val] = specificVal;
            }
        });
    }
    return data;
}

function getFilledSlot(data, slot, dataType, combo) {
    // TODO: CHECK INFO KEY
    const slotData = {
        dataType,
        slot,
    };

    if (dataType === 'FEATURES') {
        const displayOption = featureOptions[data][0];
        slotData.id = data;
        slotData.formattedId = getFormattedId(data, displayOption, dataType);
        slotData.name = $translationRaw(`labels.${_.camelCase(data)}`);
    } else if (dataType === 'FIELDS' || dataType === 'MARKERS') {
        const displayOption = data.defaultDisplayOption || null;
        slotData.id = data.id;
        slotData.formattedId = getFormattedId(data, displayOption, dataType);
        slotData.name = data.name;
    }

    if (combo) {
        slotData.combo = combo;
    }

    return slotData;
}

function getMoreFill(visibleData, markerGroups, featureVals) {
    const fields = visibleData.fields;

    // we push to fill and splice from slots, one slot at a time
    const slots = ['REG1', 'REG2'];
    const fill = [];

    // reorder lists
    const fieldsWithRank = getRank(fields, 'FIELDS');
    const featuresWithRank = getRank(featureVals, 'FEATURES');

    // apply first marker if it exists
    const firstMarkerGroup = markerGroups?.[0];
    if (firstMarkerGroup) {
        const filledSlot = getFilledSlot(firstMarkerGroup, slots[0], 'MARKERS');
        fill.push(filledSlot);
        slots.splice(0, 1);
    }

    // apply first feature if it exists
    const firstFeature = featuresWithRank[0];
    if (firstFeature) {
        const filledSlot = getFilledSlot(firstFeature, slots[0], 'FEATURES');
        fill.push(filledSlot);
        slots.splice(0, 1);
    }

    // apply image field if it exists
    const imageField = fields.find((field) => {
        return field.type === 'IMAGE' && field.options.primary;
    });
    if (imageField) {
        const imageFill = getFilledSlot(imageField, 'IMAGE1', 'FIELDS');
        fill.push(imageFill);
    }

    // finally, fill the remaining slots with fields first, then features
    const fillIds = _.map(fill, 'id');

    const remainingFields = fieldsWithRank.filter((field) => {
        return field.id !== 'SYSTEM_NAME'
            && field.type !== 'IMAGE'
            && !fillIds.includes(field.id);
    });

    const remainingFeatures = featuresWithRank.filter((featureVal) => {
        return !fill.includes((slot) => slot.id === featureVal);
    });

    while (slots.length && remainingFields.length) {
        slots.forEach((slot, index) => {
            const filledSlot = getFilledSlot(remainingFields[index], slots[index], 'FIELDS');
            fill.push(filledSlot);
            slots.splice(index, 1);
            remainingFields.splice(index, 1);
        });
    }

    while (slots.length && remainingFeatures.length) {
        slots.forEach((slot, index) => {
            const filledSlot = getFilledSlot(remainingFeatures[index], slots[index], 'FEATURES');
            fill.push(filledSlot);
            slots.splice(index, 1);
            remainingFeatures.splice(index, 1);
        });
    }

    return fill;
}

function getNameSlot(visibleData) {
    const nameCombo = _.random(1, 5);
    const nameField = _.find(visibleData.fields, { id: 'SYSTEM_NAME' });
    return getFilledSlot(nameField, 'HEADER1', 'FIELDS', nameCombo);
}

function getFilledSlots(visibleData, markerGroups, featureVals) {
    return [
        getNameSlot(visibleData),
        ...getMoreFill(visibleData, markerGroups, featureVals),
    ];
}

function getLineView(visibleData, markerGroups, featureVals) {
    return {
        viewType: 'LINE',
        id: 'LINE',
        template: visibleData.line || 'Line1',
        name: 'Line',
        visibleData: getFilledSlots(visibleData, markerGroups, featureVals),
    };
}

function getTileView(visibleData, markerGroups) {
    return {
        viewType: 'TILE',
        id: 'TILE',
        template: visibleData.tile || 'Tile1',
        name: 'Tile',
        visibleData: getFilledSlots(visibleData, markerGroups),
    };
}

export function getViews(page) {
    // Eventually use examples to pick out the fields for the cards
    const markerGroups = getMarkerGroupsFromPages([page]);
    const visibleData = getVisibleData(page.id, page);
    const featureVals = _.map(page.features, 'val');

    return [
        getLineView(visibleData, markerGroups, featureVals),
        getTileView(visibleData, markerGroups),
    ];
}

export default {
    getViews,
};
