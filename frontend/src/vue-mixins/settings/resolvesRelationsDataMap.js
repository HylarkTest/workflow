import { fieldExamples } from '@/core/display/checkUse/fieldExamples.js';

function relationExample(key, defaultNumber) {
    if (_.endsWith(key, '.COUNT')) {
        return '42';
    }
    return key.replace(/{?[\w]*?\.((FIRST_|LAST_)?NAME|TITLE)}?/g, (ignore, p1) => {
        return fieldExamples[defaultNumber][p1];
    });
}

export default {
    methods: {
        resolveRelationsObject(key, defaultNumber) {
            const id = key.match(/[\w]+(?=\.)/)[0];
            const relationship = _.find(this.page.relationships, { id });

            return {
                ...relationship,
                type: 'RELATIONS',
                value: relationExample(key, defaultNumber),
            };
        },
    },
};
