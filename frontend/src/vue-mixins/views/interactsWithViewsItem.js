import { warn } from 'vue';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { createModal } from '@/core/modals.js';

// import FullItem from '@/components/views/FullItem.vue';

export function openFullEntityViewModal(fullViewProps) {
    const essentialProps = [
        'item',
        'page',
    ];

    // Helping developers get essentials right
    const missingEssentialProps = essentialProps.filter((key) => {
        return !_.has(fullViewProps, key);
    });

    if (missingEssentialProps.length) {
        warn(`Missing essential keys in modal object:
            ${missingEssentialProps.join(', ')}`);
        return;
    }

    createModal({
        attributes: {
            containerClass: 'w-full lg:w-10/12',
            containerStyle: { height: '80vh' },
        },
        component: 'FullView',
        props: fullViewProps,
        // Leaving for now as an example
        // listeners: {
        //     entityDeleted: (payload) => {
        //         console.log(payload);
        //     },
        // },
        val: 'entityFullView', // Unique value
    });
}

export default {
    components: {
        // FullItem,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            showFullEdit: false,
        };
    },
    computed: {
        // info() {
        //     return this.item.data;
        // },
        itemRoute() {
            return {
                name: 'entityPage',
                params: { itemId: this.item.id, pageId: this.page.id },
            };
        },
    },
    methods: {
        openFull(goTo) {
            this.openModal(goTo);
        },
        openFullEdit() {
            this.showFullEdit = true;
        },
        closeFullEdit() {
            this.showFullEdit = false;
        },
        openFullView() {
            openFullEntityViewModal({
                item: this.item,
                page: this.page,
            });
        },
    },
};
