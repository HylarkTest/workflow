import FeatureMain from '@/components/features/FeatureMain.vue';
import FeaturePage from '@/components/features/FeaturePage.vue';

export default {
    components: {
        FeatureMain,
        FeaturePage,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            default: null,
        },
        isSubsetPage: Boolean,
        isBirdseyePage: Boolean,
        subsetHeaderProps: {
            type: [Object, null],
            default: null,
        },
        defaultFilter: {
            type: [String, null],
            default: null,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        historyPageType() {
            return ''; // In component
        },
        featureType() {
            return ''; // In component
        },
        featurePageProps() {
            return {
                isLoading: this.isLoading, // From accompanying mixin
                backgroundStyle: this.isSubsetPage ? 'SUBFEATURE' : 'FEATURE',
                featureType: this.featureType,
                page: this.page,
                isSubsetPage: this.isSubsetPage,
                historyPageType: this.historyPageType,
                subsetHeaderProps: this.subsetHeaderProps,
            };
        },
        featureMainProps() {
            return {
                sourceLists: this.sourceLists,
                defaultFilter: this.defaultFilter,
                featureType: this.featureType,
                page: this.page,
                isSubsetPage: this.isSubsetPage,
                deleteListFunction: this.deleteListFunction,
                createListFromObjectFunction: this.createListFromObjectFunction,
                updateListFunction: this.updateListFunction,
                createListFunction: this.createListFunction,
                moveListFunction: this.moveListFunction,
                moveItemToListFunction: this.moveItemToListFunction,
                allowRouterTitle: true,
                isBirdseyePage: this.isBirdseyePage,
            };
        },
        layoutProps() {
            if (this.isBirdseyePage) {
                return {};
            }
            return this.featurePageProps;
        },
        layoutComponent() {
            if (this.isBirdseyePage) {
                return 'div';
            }
            return 'FeaturePage';
        },
        shouldSkipIntegrations() {
            return !!this.page;
        },
    },
    methods: {
        contextVariables() {
            const variables = {};
            if (this.page) {
                if (this.page.type === 'ENTITIES') {
                    variables.forMapping = this.page.mapping.id;
                } else {
                    variables.forLists = this.page.lists;
                }
                variables.spaceIds = this.page.space.id ? [this.page.space.id] : null;
            }
            return variables;
        },
    },
    created() {
    },
};
