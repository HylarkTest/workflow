<template>
    <div class="o-event-list">
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
    name: 'EventList',
    components: {
    },
    mixins: [
        interactsWithFeatureItems,
    ],
    props: {
        events: {
            type: [Array, null],
            required: true,
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
            return `o-event-list--${this.lowerType}`;
        },
        allItems() {
            return this.events;
        },
        itemGroupings() {
            return this.getBasicGroupings(this.events);
        },
    },
    methods: {
        hasMore() {
            return this.events?.__EventConnection.pageInfo.hasNextPage;
        },

    },
    created() {

    },
};
</script>

<style scoped>
.o-event-list {
    &--line {
        @apply
            flex
            flex-col
            gap-2
        ;
    }
}

</style>
