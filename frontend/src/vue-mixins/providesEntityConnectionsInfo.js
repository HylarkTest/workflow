import { allFeatures } from '@/core/display/typenamesList.js';

export default {
    methods: {
        getSectionInfo(section, sectionName) {
            return _(section).map((tab) => {
                return {
                    value: tab.val || tab.id,
                    name: this.getName(tab),
                    icon: this.getIcon(tab, sectionName),
                    section: sectionName,
                    paramName: _.kebabCase(tab.val) || tab.id,
                    count: this.getCount(tab, sectionName),
                    dot: this.getDot(tab),
                    // Where there might be a longer name for the header
                    longName: this.getLongName(tab),
                };
            }).value();
        },
        getName(tab) {
            return tab.name || this.$t(`labels.${_.camelCase(tab.val)}`);
        },
        getLongName(tab) {
            if (tab.val === 'EMAILS') {
                return this.$t('emails.emailsFor', { name: this.fullItem.name });
            }
            return this.getName(tab);
        },
        getDot(tab) {
            if (tab.val === 'EMAILS') {
                return this.fullItem.features.EMAILS_PRESENT || this.fullItem.features.EMAILS_ASSOCIATED_ADDRESS;
            }
            return false;
        },
        getIcon(tab, sectionName) {
            if (sectionName === 'FEATURES') {
                return allFeatures[tab.val] && allFeatures[tab.val].symbol;
            }
            return tab.to.pages[0].symbol;
        },
        getCount(tab, sectionName) {
            if (sectionName === 'FEATURES') {
                return this.fullItem.features[`${tab.val}.FEATURE_COUNT`];
            }
            return 0;
        },
    },
};
