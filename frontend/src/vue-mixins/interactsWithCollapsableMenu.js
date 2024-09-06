import CollapsableMenu from '@/components/fullViews/CollapsableMenu.vue';

import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

export default {
    components: {
        CollapsableMenu,
    },
    mixins: [
        listensToScrollandResizeEvents,
    ],
    data() {
        return {
            isSideVisible: true,
            forceResponsiveDisplay: false,
            responsiveDisplayCutoff: 600, // can be replaced in component
        };
    },
    computed: {
        contentOnly() {
            return false; // Add in component
        },
    },
    methods: {
        showSide() {
            this.isSideVisible = true;
        },
        hideSide() {
            this.isSideVisible = false;
        },
        onResize() {
            const el = this.$el;
            const elWidth = el.offsetWidth;
            if (elWidth < this.responsiveDisplayCutoff) {
                this.forceResponsiveDisplay = true;
            } else {
                this.forceResponsiveDisplay = false;
            }
        },
    },
    created() {
        this.isSideVisible = !this.contentOnly;
    },
    beforeUpdate() {
        this.onResize();
    },
};
