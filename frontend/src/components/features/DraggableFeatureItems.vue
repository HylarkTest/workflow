<template>
    <Draggable
        class="c-draggable-feature-item"
        itemKey="id"
        :modelValue="items"
        :chosenClass="chosenClass"
        :group="group"
        :move="onMove"
        :disabled="noDrag"
        :sort="!noSort"
        :revertOnSpill="true"
        @update="moveItem"
    >
        <template #item="{ element }">
            <div>
                <slot
                    name="item"
                    :element="element"
                >
                </slot>
            </div>
        </template>
    </Draggable>
</template>

<script>
import Draggable from 'vuedraggable';

export default {
    name: 'DraggableFeatureItems',
    components: {
        Draggable,
    },
    props: {
        items: {
            type: Array,
            required: true,
        },
        displayedList: {
            type: [Object, null],
            required: true,
        },
        chosenClass: {
            type: String,
            default: 'item-sortable-chosen',
        },
        noDrag: Boolean,
        noSort: Boolean,
    },
    emits: [
        'moveItem',
    ],
    computed: {
        group() {
            return {
                name: 'listItems',
                pull: (to) => !this.noDrag
                    && to.options.group.name === this.displayedList.space.id,
            };
        },
    },
    methods: {
        // Here we check to see if we are moving over a list and making sure
        // that list element has a callback that should be triggered when the
        // item is dragged over.
        onMove(moveEvent) {
            if (_.isFunction(moveEvent.related._onItemEnter)) {
                if (moveEvent.relatedContext.element.id === this.displayedList.id) {
                    return false;
                }
                const listEl = moveEvent.related;
                listEl._onItemEnter();
            }
            return true;
        },
        moveItem(event) {
            this.$emit('moveItem', { item: this.items[event.oldIndex], from: event.oldIndex, to: event.newIndex });
        },
    },
};
</script>

<style scoped>
/* .c-draggable-feature-item {
} */
</style>
