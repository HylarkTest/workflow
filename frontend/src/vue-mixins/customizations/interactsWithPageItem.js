import providesColors from '@/vue-mixins/style/providesColors.js';
import interactsWithSubsetInfo from '@/vue-mixins/pages/interactsWithSubsetInfo.js';

import { pageTypeSmall } from '@/core/display/systemTagDesigns.js';
import Page from '@/core/models/Page.js';

export default {
    mixins: [
        providesColors,
        interactsWithSubsetInfo,
    ],
    props: {
        page: {
            type: [Object, Page],
            required: true,
        },
    },
    data() {
        return {
            selectedView: null,
        };
    },
    computed: {
        pageType() {
            return this.page.type;
        },
        pageTypeName() {
            return this.$t(`common.pageTypes.${_.camelCase(this.pageType)}`);
        },
        pageMapping() {
            return this.page.mapping;
        },
        mappingObj() {
            return this.pageMapping;
        },
    },
    methods: {
        openPageEdit(selectedView = 'PAGE') {
            this.$emit('openPageEdit', { page: this.page, selectedView });
        },
    },
    created() {
        this.pageTypeSmall = pageTypeSmall;
    },
};
