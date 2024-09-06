import { $translationRaw } from '@/i18n.js';

export default {
    props: {
        tabs: {
            type: [Array, Object],
            required: true,
        },
        selectedTab: {
            type: [Object, String],
            default: '',
        },
        router: Boolean,
        paramKey: {
            type: String,
            default: '',
        },
        compareValue: Boolean,
    },
    emits: [
        'selectTab',
    ],
    methods: {
        emitTab(tab) {
            this.$emit('selectTab', tab);
        },
        getTabTitle(tab) {
            return $translationRaw(tab.namePath, tab.name);
        },
        getTabSubtitle(tab) {
            return $translationRaw(tab.subtitlePath, tab.subtitle);
        },
        isActive(tab) {
            if (!this.router || this.compareValue) {
                if (_.isObject(this.selectedTab)) {
                    return this.selectedTab.value === tab.value;
                }
                return this.selectedTab === tab.value;
            }
            if (tab.paramName && this.paramKey) {
                return this.$route.params[this.paramKey] === tab.paramName;
            }
            return this.$route.name === tab.link;
        },
    },
};
