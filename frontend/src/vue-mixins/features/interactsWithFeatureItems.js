import FeatureItemsList from '@/components/features/FeatureItemsList.vue';
import FeatureItemsDraggable from '@/components/features/FeatureItemsDraggable.vue';

import providesFilterProperties from '@/vue-mixins/providesFilterProperties.js';
import handlesListAndGroupedItems from '@/vue-mixins/features/handlesListAndGroupedItems.js';

import { featureMap } from '@/vue-mixins/sortsOutTypes.js';

export default {
    components: {
        FeatureItemsList,
        FeatureItemsDraggable,
    },
    mixins: [
        providesFilterProperties,
        handlesListAndGroupedItems,
    ],
    props: {
        featureType: {
            type: String,
            required: true,
        },
        page: {
            type: Object,
            default: null,
        },
        spaceId: {
            type: String,
            default: null,
        },
        displayedList: {
            type: [Object, null],
            default: null,
        },
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
        bgClass: {
            type: String,
            default: 'bg-cm-00',
        },
        isLoading: Boolean,
        selectedItem: {
            type: [Object, null],
            default: null,
        },
        filtersObj: {
            type: Object,
            required: true,
        },
        currentView: {
            type: [Object, null],
            default: null,
        },
        forceNoDrag: Boolean,
    },
    data() {
        return {
        };
    },
    computed: {
        viewClass() {
            return ''; // Add in component
        },
        allItems() {
            return []; // Add in component
        },
        itemGroupings() {
            return []; // Add in component
        },
        listGroupingKey() {
            return _.find(featureMap, { featureType: this.featureType }).listName;
        },
        filtersAllowDrag() {
            return this.filtersObj.sortOrder.value === 'MANUAL' && !this.hasActiveFilters;
        },
        viewType() {
            return this.currentView?.viewType;
        },
        lowerType() {
            return _.camelCase(this.viewType);
        },
        featureItemsProps() {
            return {
                allItems: this.allItems,
                itemGroupings: this.itemGroupings,
                currentGroup: this.filtersObj.currentGroup,
                displayedList: this.displayedList,
                listGroupingKey: this.listGroupingKey,
                viewType: this.viewType,
                isLoading: this.isLoading,
                hasMoreFunction: (grouping) => this.hasMore(grouping),
            };
        },
        groupPointer() {
            return this.filtersObj.currentGroup;
        },
    },
    methods: {
        noDrag() {
            // overwritten in TodoItems.vue, but not other features
            return this.forceNoDrag
                || !this.displayedList
                || this.isLoading;
        },
        noSort() {
            // overwritten in TodoItems.vue, but not other features
            return !this.filtersAllowDrag || !this.displayedList;
        },
        draggableProps(items) {
            return {
                items,
                class: this.viewClass,
                featureType: this.featureType,
                displayedList: this.displayedList,
                selectedItem: this.selectedItem,
                viewType: this.viewType,
                noDrag: this.noDrag(items),
                noSort: this.noSort(items),
            };
        },
        openItemModal(item) {
            this.$emit('openItem', item);
        },
        showMore(grouping) {
            this.$emit('showMore', grouping);
        },
    },
};
