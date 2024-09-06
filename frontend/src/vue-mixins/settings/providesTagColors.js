const tagColors = {
    descriptive: {
        text: 'text-gold-700',
        bg: 'bg-gold-200',
    },
    pipeline: {
        text: 'text-peach-700',
        bg: 'bg-peach-200',
    },
    status: {
        text: 'text-cm-700',
        bg: 'bg-cm-200',
    },
};

export default {
    methods: {
        tagClasses(tagType) {
            const type = _.lowerCase(tagType);
            const bg = tagColors[type].bg;
            const text = tagColors[type].text;
            return [bg, text];
        },
    },
};
