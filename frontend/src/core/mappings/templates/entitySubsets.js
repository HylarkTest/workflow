export const subsetFilters = {
    REFEREE_SUBSET: {
        type: 'MARKER',
        id: 'CAREER_CONTACT_DESCRIPTOR_TAGS.referee',
        comparator: 'IS',
    },
    HEADHUNTER_SUBSET: {
        type: 'MARKER',
        id: 'CAREER_CONTACT_DESCRIPTOR_TAGS.headhunter',
        comparator: 'IS',
    },
    WEDDING_GUEST_SUBSET: {
        type: 'FIELD',
        id: 'IS_WEDDING_GUEST',
        comparator: 'IS',
        val: true,
    },
};

export function getSubset(subsetPageId, mainPageId) {
    return {
        mainId: mainPageId,
        filter: subsetFilters[subsetPageId] || null,
    };
}
