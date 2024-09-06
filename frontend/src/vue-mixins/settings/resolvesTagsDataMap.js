export default {
    methods: {
        resolveTagsObject(key) {
            const tagGroup = _.find(this.page.tagGroups, ['id', key]);
            const tagType = tagGroup.type;
            const isStatus = tagType === 'STATUS';
            const sliceNumber = isStatus ? 1 : 3;
            const initial = tagGroup.group.tags.slice(0, sliceNumber);
            return {
                ...tagGroup,
                type: 'TAGS',
                value: initial,
            };
        },
    },
};
