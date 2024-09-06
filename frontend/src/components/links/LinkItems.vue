<template>
    <div class="o-link-items">
        <FeatureItemsList v-bind="featureItemsProps">
            <template #itemsSlot="{ items }">
                <FeatureItemsDraggable
                    v-bind="draggableProps(items)"
                    @selectItem="openItemModal"
                >
                </FeatureItemsDraggable>
            </template>

            <template #noContentSlot>
                <slot name="noContentSlot">
                </slot>
            </template>
        </FeatureItemsList>
    </div>
</template>

<script>
import interactsWithFeatureItems from '@/vue-mixins/features/interactsWithFeatureItems.js';

export default {
    name: 'LinkItems',
    components: {
    },
    mixins: [
        interactsWithFeatureItems,
    ],
    props: {
        links: {
            type: [Array, null],
            required: true,
        },
        groupedLinks: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'showMore',
        'openItem',
    ],
    data() {
        return {
        };
    },
    computed: {
        viewClass() {
            return `o-link-items--${this.lowerType}`;
        },
        allItems() {
            return this.links;
        },
        itemGroupings() {
            return this.getGroupings(this.links, this.groupedLinks);
        },
        viewType() {
            return 'LINE'; // Overwriting mixin until currentView is received as a prop. See TodoList.vue for example
        },
    },
    methods: {
        hasMore(grouping) {
            return this.hasMoreToLoad(grouping, this.links, this.groupedLinks);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-link-items {
    &--line {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));

        @apply
            gap-3.5
            grid
        ;
    }
}

</style>
