<template>
    <Draggable
        class="o-feature-items-draggable"
        itemKey="id"
        :modelValue="items"
        :chosenClass="chosenClass"
        :group="group"
        :move="onMove"
        :disabled="deactivateDrag"
        :sort="!noSort"
        :revertOnSpill="true"
        @update="moveItem"
    >
        <template #item="{ element }">
            <div>
                <component
                    :is="itemComponent"
                    :[itemName]="element"
                    :[listName]="displayedList"
                    :actionProcessing="actionProcessingIds.includes(element.id)"
                    :deleteProcessing="deleteProcessingIds.includes(element.id)"
                    :selectedItem="selectedItem"
                    @[selectName]="selectItem"
                    @update:processing="updateProcessingItems(element.id, $event)"
                >
                </component>
            </div>
        </template>
    </Draggable>
</template>

<script>
import Draggable from 'vuedraggable';
import NoteItem from '@/components/notes/NoteItem.vue';
import TodoItem from '@/components/todos/TodoItem.vue';
import TodoKanban from '@/components/todos/TodoKanban.vue';
import DocumentItem from '@/components/documents/DocumentItem.vue';
import LinkItem from '@/components/links/LinkItem.vue';
import PinItem from '@/components/pinboard/PinItem.vue';
import EventItem from '@/components/events/EventItem.vue';

import { featureMap } from '@/vue-mixins/sortsOutTypes.js';

const itemComponents = {
    NOTES: {
        LINE: 'NoteItem',
    },
    TODOS: {
        LINE: 'TodoItem',
        KANBAN: 'TodoKanban',
    },
    DOCUMENTS: {
        LINE: 'DocumentItem',
    },
    LINKS: {
        LINE: 'LinkItem',
    },
    PINBOARD: {
        LINE: 'PinItem',
    },
    EVENTS: {
        LINE: 'EventItem',
    },
};

export default {
    name: 'FeatureItemsDraggable',
    components: {
        Draggable,
        NoteItem,
        TodoItem,
        TodoKanban,
        DocumentItem,
        LinkItem,
        PinItem,
        EventItem,
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
        chosenClass: {
            type: String,
            default: 'item-sortable-chosen',
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
        noDrag: Boolean,
        noSort: Boolean,
    },
    emits: [
        'moveEvent',
        'moveItem',
        'selectItem',
    ],
    data() {
        return {
            actionProcessingIds: [],
            deleteProcessingIds: [],
        };
    },
    computed: {
        actionIsProcessing() {
            return !!this.actionProcessingIds.length;
        },
        deleteIsProcessing() {
            return !!this.deleteProcessingIds.length;
        },
        deactivateDrag() {
            return this.noDrag || this.actionIsProcessing || this.deleteIsProcessing;
        },
        featureObj() {
            return _.find(featureMap, { featureType: this.featureType });
        },
        itemTypename() {
            return this.featureObj.itemTypename;
        },
        itemName() {
            return _.lowerCase(this.itemTypename);
        },
        listName() {
            return this.featureObj.listName;
        },
        selectName() {
            return `select${this.itemTypename}`;
        },
        itemComponent() {
            return itemComponents[this.featureType][this.viewType];
        },
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
        selectItem(event) {
            this.$emit('selectItem', event);
        },
        moveItem(event) {
            this.$emit('moveItem', { item: this.items[event.oldIndex], from: event.oldIndex, to: event.newIndex });
        },
        updateProcessingItems(itemId, { processingType, state }) {
            const processingKey = `${processingType}ProcessingIds`;
            if (state) {
                this[processingKey].push(itemId);
            } else {
                _.remove(this[processingKey], (id) => id === itemId);
            }
        },
    },
    created() {
    },
};
</script>

<style>
/* .o-feature-items-draggable {
} */
</style>
