<template>
    <div class="o-finder-result-todo">
        <div
            v-if="showDetailsBar"
            class="o-finder-result-todo__section"
        >
            <PriorityFlag
                v-if="priority"
                :priority="priority"
            >
            </PriorityFlag>

            <DateDisplay
                v-if="dueBy"
                :dateTime="dueBy"
                :class="{ 'ml-6': priority }"
            >
            </DateDisplay>

            <RecurrenceDisplay
                v-if="recurrence"
                :recurrence="recurrence"
                :class="{ 'ml-6': priority || dueBy }"
            >
            </RecurrenceDisplay>
        </div>

        <div class="flex items-center text-sm mt-1">
            <ColorSquare
                class="mr-2"
                :currentColor="list.color"
            >
            </ColorSquare>

            <div
                v-if="hasHighlightedList"
                v-dompurify-html="highlightedList.highlight"
            >
            </div>

            <template
                v-else
            >
                {{ list.name }}
            </template>
        </div>

        <div
            v-if="hasHighlightedDescription"
            v-dompurify-html="highlightedDescription.highlight"
            class="o-finder-result__description mt-1"
        >
        </div>

        <TrimmedParagraph
            v-if="!hasHighlightedDescription && description"
            class="o-finder-result__description mt-1"
            :paragraph="description"
        >
        </TrimmedParagraph>
    </div>
</template>

<script>

import PriorityFlag from '@/components/assets/PriorityFlag.vue';
import ColorSquare from '@/components/assets/ColorSquare.vue';
import RecurrenceDisplay from '@/components/time/RecurrenceDisplay.vue';
import DateDisplay from '@/components/time/DateDisplay.vue';
import TrimmedParagraph from '@/components/display/TrimmedParagraph.vue';

export default {
    name: 'FinderResultTodo',
    components: {
        PriorityFlag,
        ColorSquare,
        RecurrenceDisplay,
        DateDisplay,
        TrimmedParagraph,
    },
    mixins: [
    ],
    props: {
        highlightedFields: {
            type: Array,
            required: true,
        },
        node: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        priority() {
            return this.node.priority;
        },
        description() {
            return this.node.description;
        },
        highlightedDescription() {
            return this.highlightedFields.find((field) => {
                return field.path === 'description';
            });
        },
        highlightedList() {
            return this.highlightedFields.find((field) => {
                return field.path === 'todoList.name';
            });
        },
        hasHighlightedDescription() {
            return this.highlightedPaths.includes('description');
        },

        highlightedPaths() {
            return this.highlightedFields.map((field) => {
                return field.path;
            });
        },
        hasHighlightedList() {
            return this.highlightedPaths.includes('todoList.name');
        },

        dueBy() {
            return this.node.dueBy;
        },

        recurrence() {
            return this.node.recurrence;
        },

        list() {
            return this.node.list;
        },

        showDetailsBar() {
            return this.priority || this.dueBy || this.recurrence;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-finder-result-todo {
    &__section {
        @apply
            bg-cm-100
            flex-wrap
            inline-flex
            items-center
            mt-1
            px-4
            py-1
            rounded-lg
            text-cm-600
            text-xs
        ;
    }
}

</style>
