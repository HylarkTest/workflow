<template>
    <div class="o-todo-items">
        <Teleport
            :to="teleportRef"
        >
            <div
                v-if="displayedList && !hasActiveFilters"
                class="flex justify-center pb-8"
            >
                <FeatureQuickForm
                    class="w-full"
                    :displayedList="displayedList"
                    featureType="TODOS"
                >
                    <TodoQuickForm
                        :todoList="displayedList"
                        :spaceId="spaceId"
                        :page="page"
                        :defaultAssociations="defaultAssociations"
                    >
                    </TodoQuickForm>
                </FeatureQuickForm>
            </div>

            <div
                class="flex justify-end pb-2"
            >
                <CheckHolder
                    class="items-center"
                    :modelValue="showCompleted"
                    @update:modelValue="$emit('update:showCompleted', $event)"
                >
                    <div class="flex items-center">
                        <span
                            v-t="'common.showCompleted'"
                            class="font-semibold"
                        >
                        </span>

                        <span
                            v-if="completedTodosLength"
                            class="o-todo-items__number circle-center"
                        >
                            {{ completedTodosLength }}
                        </span>
                    </div>
                </CheckHolder>
            </div>
        </Teleport>

        <FeatureItemsList v-bind="featureItemsProps">
            <template #itemsSlot="{ items }">
                <FeatureItemsDraggable
                    v-bind="draggableProps(items)"
                    @moveItem="manuallyMoveTodo"
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

import TodoQuickForm from './TodoQuickForm.vue';
import FeatureQuickForm from '@/components/features/FeatureQuickForm.vue';

import interactsWithFeatureItems from '@/vue-mixins/features/interactsWithFeatureItems.js';

import {
    moveTodo,
} from '@/core/repositories/todoRepository.js';

export default {
    name: 'TodoItems',
    components: {
        TodoQuickForm,
        FeatureQuickForm,
    },
    mixins: [
        interactsWithFeatureItems,
    ],
    props: {
        todos: {
            type: [Array, null],
            required: true,
        },
        groupedTodos: {
            type: [Object, null],
            required: true,
        },
        externalTodos: {
            type: [Object, null],
            required: true,
        },
        showCompleted: Boolean,
        teleportRef: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'showMore',
        'openItem',
        'manuallyMoveItem',
        'update:showCompleted',
    ],
    data() {
        return {
        };
    },
    computed: {
        // interactsWithFeatureItems.js mixin
        viewClass() {
            return `o-todo-items--${this.lowerType}`;
        },
        allItems() {
            if (this.hasExternalFunctionality) {
                return this.externalTodosArr;
            }
            return this.todosArr;
        },
        itemGroupings() {
            if (!this.filtersObj.currentGroup && this.hasExternalFunctionality) {
                return this.getBasicGroupings(this.externalTodosArr);
            }
            return this.getGroupings(this.todos, this.groupedTodos);
        },

        externalTodosArr() {
            return this.externalTodos?.data || [];
        },
        todosArr() {
            return this.todos
                || this.flattenedTodos
                || [];
        },
        flattenedTodos() {
            return _.flatMap(this.groupedTodos?.groups, 'items');
        },
        hasExternalFunctionality() {
            return this.displayedList?.isExternalList() && !this.hasActiveFilters;
        },

        // Completions
        completedTodosLength() {
            if (this.hasExternalFunctionality) {
                return null;
            }
            if (this.filtersObj.currentGroup) {
                return this.groupedTodos?.meta?.completedCount;
            }
            return this.todos.__TodoConnection?.meta?.completedCount;
        },
    },
    methods: {
        manuallyMoveTodo({ item, from, to }) {
            // To and from are indexes
            const isNowAtEnd = to === this.allItemsLength - 1;
            const isTodoMovedDown = to > from;
            // Because the ordering of to-dos is reversed (latest at the top for users
            // whereas programmatically the later ones have a higher index), we do
            // + 1 instead of - 1
            const previousTodoPosition = isTodoMovedDown ? to + 1 : to;
            const previousTodo = isNowAtEnd ? null : this.allItems[previousTodoPosition];
            return moveTodo(item, previousTodo);
        },
        hasMore(grouping) {
            if (!this.filtersObj.currentGroup && this.hasExternalFunctionality) {
                return this.externalTodos?.paginatorInfo.hasMorePages;
            }
            return this.hasMoreToLoad(grouping, this.todos, this.groupedTodos);
        },
        // External todos cannot be ordered and cannot be moved to new lists, so
        // we disable all kinds of dragging for external todos.
        // If there are no todos it doesn't matter if this is false.
        noDrag(items) {
            return this.forceNoDrag
                || !this.displayedList
                || this.isLoading
                || items[0]?.isExternalItem();
        },
        // For internal todos we want to disable ordering when they are sorting
        // by anything other than MANUAL or when there are active filters.
        // However, they can still be moved to other lists. So we don't want to
        // fully disable draggable.
        noSort(items) {
            return !this.filtersAllowDrag
                || !this.displayedList
                || items[0]?.isExternalItem();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-todo-items {
    &__number {
        height: 24px;
        min-width: 24px;

        @apply
            bg-cm-200
            font-semibold
            ml-4
            text-sm
        ;
    }

    &--line {
        @apply
            flex
            flex-col
            gap-4
        ;
    }
}

</style>
