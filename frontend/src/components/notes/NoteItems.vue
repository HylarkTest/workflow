<template>
    <div class="o-note-items">
        <div class="flex justify-center mb-8">
            <FeatureQuickForm
                v-if="displayedList && !hasActiveFilters"
                :displayedList="displayedList"
                :featureType="featureType"
                class="max-w-xl"
            >
                <NoteQuickForm
                    :notebook="displayedList"
                    :spaceId="spaceId"
                    :page="page"
                    :defaultAssociations="defaultAssociations"
                >
                </NoteQuickForm>
            </FeatureQuickForm>
        </div>

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
import FeatureQuickForm from '@/components/features/FeatureQuickForm.vue';
import NoteQuickForm from '@/components/notes/NoteQuickForm.vue';

import interactsWithFeatureItems from '@/vue-mixins/features/interactsWithFeatureItems.js';

export default {
    name: 'NoteItems',
    components: {
        NoteQuickForm,
        FeatureQuickForm,
    },
    mixins: [
        interactsWithFeatureItems,
    ],
    props: {
        notes: {
            type: [Array, null],
            required: true,
        },
        groupedNotes: {
            type: [Object, null],
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
            return `o-note-items--${this.lowerType}`;
        },
        allItems() {
            return this.notes;
        },
        itemGroupings() {
            return this.getGroupings(this.notes, this.groupedNotes);
        },
        viewType() {
            return 'LINE'; // Overwriting mixin until currentView is received as a prop. See TodoList.vue for example
        },
    },
    methods: {
        hasMore(grouping) {
            return this.hasMoreToLoad(grouping, this.notes, this.groupedNotes);
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-note-items {
    &--line {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));

        @apply
            gap-3.5
            grid
        ;
    }
}
</style>
