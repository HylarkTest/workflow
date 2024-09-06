import DraggableFeatureItems from '@/components/features/DraggableFeatureItems.vue';

export default {
    components: {
        DraggableFeatureItems,
    },
    props: {
        featureType: {
            type: String,
            required: true,
        },
        items: {
            type: Array,
            required: true,
        },
        displayedList: {
            type: [Object, null],
            default: null,
        },
        selectedItem: {
            type: [Object, null],
            default: null,
        },
        viewType: {
            type: String,
            required: true,
        },
        deactivateDrag: Boolean,
        deactivateSort: Boolean,
    },
    emits: [
        'moveItem',
        'selectItem',
    ],
    data() {
        return {
            itemComponents: {}, // Add in component
        };
    },
    computed: {
        itemComponent() {
            return this.itemComponents[this.viewType];
        },
        lowerType() {
            return _.camelCase(this.viewType);
        },
        noDrag() {
            return this.deactivateDrag;
        },
        noSort() {
            return this.deactivateSort || !this.displayedList;
        },
        draggableItemsProps() {
            return {
                class: this.typeClass,
                displayedList: this.displayedList,
                items: this.items,
                noDrag: this.noDrag,
                noSort: this.noSort,
            };
        },

    },
    methods: {
        moveItem(event) {
            this.$emit('moveItem', event);
        },
        selectItem(event) {
            this.$emit('selectItem', event);
        },
    },
};
