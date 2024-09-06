<template>
    <div class="o-document-items">
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
    name: 'DocumentItems',
    components: {
    },
    mixins: [
        interactsWithFeatureItems,
    ],
    props: {
        documents: {
            type: [Array, null],
            required: true,
        },
        groupedDocuments: {
            type: [Object, null],
            required: true,
        },
    },
    emits: [
        'showMore',
        'openItem',
    ],
    apollo: {
    },
    data() {
        return {
        };
    },
    computed: {
        viewClass() {
            return `o-document-items--${this.lowerType}`;
        },
        allItems() {
            return this.documents;
        },
        itemGroupings() {
            return this.getGroupings(this.documents, this.groupedDocuments);
        },
        viewType() {
            return 'LINE'; // Overwriting mixin until currentView is received as a prop. See TodoList.vue for example
        },
    },
    methods: {
        hasMore(grouping) {
            return this.hasMoreToLoad(grouping, this.documents, this.groupedDocuments);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-document-items {
    &--line {
        @apply
            flex
            flex-col
            gap-2
        ;
    }
}

</style>
