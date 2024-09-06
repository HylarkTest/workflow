import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    mixins: [
        interactsWithModal,
    ],
    props: {
        dataValue: {
            type: [null, Object, Number],
            default: null,
        },
        page: {
            type: [Object, null],
            required: true,
        },
        dataInfo: {
            type: Object,
            required: true,
        },
        item: {
            type: [Object, null],
            default: null,
        },
        isModifiable: Boolean,
    },
    computed: {
        tabKey() {
            return this.dataInfo.id;
        },
        featureFormatted() {
            return _.camelCase(this.tabKey);
        },
        cantModifyClass() {
            return !this.isModifiable
                ? 'pointer-events-none'
                : 'cursor-pointer';
        },
    },
    methods: {
        itemRoute(tab) {
            return {
                name: 'entityPage',
                params: {
                    itemId: this.item.id,
                    pageId: this.page.id,
                    tab,
                },
            };
        },
        goToTab(tab) {
            this.$router.push(this.itemRoute(tab));
        },
        openFull() {
            this.openModal();
        },
        goToPage(tab) {
            this.$router.push(this.itemRoute(tab));
        },
        openPage() {
            this.goToTab(_.camelCase(this.tabKey));
        },
    },
};
