const templates = {
    KANBAN: [
        'Kanban1',
        'Kanban2',
        'Kanban3',
        'Kanban4',
        // 'Kanban5',
    ],
    LINE: [
        'Line1',
        'Line2',
        'Line3',
    ],
    TILE: [
        'Tile1',
        'Tile2',
        'Tile3',
        'Tile4',
    ],
};

export const availableSlotOptions = ['HEADER1', 'IMAGE1', 'REG1', 'REG2', 'REG3', 'REG4', 'REG5'];

const slotCounts = {
    Line1: 4,
    Line2: 5,
    Line3: 7,
    Tile1: 4,
    Tile2: 4,
    Tile3: 6,
    Tile4: 7,
    Kanban1: 5,
    Kanban2: 5,
    Kanban3: 6,
    Kanban4: 5,
};

export function getSlotCount(templateVal) {
    return slotCounts[templateVal] || 4;
}

export function getSlots(templateVal) {
    // This will need to pick out the needed ones rather than slice
    return availableSlotOptions.slice(0, getSlotCount(templateVal));
}

export function getDataForSlots(visibleData, view) {
    const templateVal = view.template;
    if (templateVal) {
        const validSlots = getSlots(templateVal);

        const filteredVisible = visibleData.filter((dataLine) => {
            return validSlots.includes(dataLine.slot);
        });
        return _(validSlots).map((slot) => {
            return filteredVisible.find((dataLine) => {
                return dataLine.slot === slot;
            });
        }).compact().value();
    }
    return visibleData;
}

export function getTemplates(viewVal) {
    return templates[viewVal];
}

export default {
    getTemplates,
};
