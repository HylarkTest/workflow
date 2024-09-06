<template>
    <ButtonEl
        class="o-todo-item feature__item feature__item--style"
        :class="mainFeatureItemClasses"
        @click="selectTodo(todo)"
        @keyup.enter="selectTodo(todo)"
        @keyup.space="selectTodo(todo)"
    >
        <div
            class="o-todo-item__top"
        >
            <TodoCheck
                class="mr-4"
                :isCompleted="isCompleted"
                @toggleCompletion="toggleCompletion"
            >
            </TodoCheck>

            <div class="flex-1">
                <h6
                    class="font-semibold inline-flex"
                    :class="nameClass"
                >
                    {{ nameDisplay }}
                </h6>
            </div>

            <div class="flex items-center -mr-4">
                <div
                    v-if="description"
                    class="mr-3 text-cm-300"
                    :title="$t('labels.description')"
                >
                    <i
                        class="fa-regular fa-memo-pad"
                    >
                    </i>
                </div>

                <PriorityFlag
                    v-if="hasPriority"
                    :priority="priority"
                    :isModifiable="true"
                    :allowedPriorities="allowedPriorities"
                    @selectPriority="selectPriority"
                    @click.stop
                >
                </PriorityFlag>

                <div
                    class="ml-2 bg-cm-00 -mr-px h-7 w-7 centered rounded-l-full shadow-md"
                >
                    <ExtrasButton
                        :options="['DUPLICATE', 'DELETE']"
                        :item="featureItem"
                        contextItemType="FEATURE_ITEM"
                        alignRight
                        nudgeDownProp="0.375rem"
                        nudgeRightProp="0.375rem"
                        :duplicateItemMethod="duplicateItem"
                        @click.stop
                        @selectOption="selectOption"
                    >
                    </ExtrasButton>
                </div>
            </div>
        </div>

        <div class="flex items-center ml-4">
            <DateDisplay
                v-if="dueBy"
                :dateTime="dueBy"
                :isMicrosoftItem="isMicrosoftItem"
                class="o-todo-item__data ml-2"
            >
                <div
                    v-if="isOverdue"
                    class="rounded-full bg-rose-500 h-2 w-2 ml-1"
                >
                </div>
            </DateDisplay>

            <RecurrenceDisplay
                v-if="recurrence"
                class="o-todo-item__data ml-2"
                :recurrence="recurrence"
            >
            </RecurrenceDisplay>
        </div>

        <div
            v-if="markersLength || associationsLength"
            class="flex flex-wrap mt-2"
            :class="!markersLength ? 'justify-end' : 'justify-between'"
        >
            <EditableMarkerSet
                v-if="markersLength"
                :item="todo"
                :tags="tags"
                :pipelines="pipelines"
                :statuses="statuses"
            >
            </EditableMarkerSet>

            <div
                v-if="associationsLength"
                class="flex flex-wrap gap-0.5"
            >
                <div
                    v-for="association in associations"
                    :key="association.id"
                    class="w-6 h-6"
                >
                    <ConnectedRecord
                        class="h-full w-full text-xssm"
                        :item="association"
                        :isMinimized="true"
                        imageSize="full"
                        @click.stop
                    >
                    </ConnectedRecord>
                </div>
            </div>
        </div>

        <div
            v-if="!list || isExternal || showAssignees"
            class="flex flex-wrap justify-end mt-2 items-center"
        >
            <FeatureSource
                v-if="!list || isExternal"
                :featureItem="todo"
                listKey="list"
                :onlyExternal="!!list"
            >
            </FeatureSource>

            <AssigneesPicker
                v-if="showAssignees"
                v-model:assigneeGroups="assigneeGroups"
                class="ml-2"
                bgColor="white"
            >
            </AssigneesPicker>
        </div>
    </ButtonEl>
</template>

<script>
import interactsWithTodoItem from '@/vue-mixins/interactsWithTodoItem.js';
import { duplicateTodo } from '@/core/repositories/todoRepository.js';

export default {
    name: 'TodoItem',
    components: {
    },
    mixins: [
        interactsWithTodoItem,
    ],
    props: {
    },
    emits: [
        'selectTodo',
    ],
    computed: {
    },
    methods: {
        duplicateItem(records) {
            return duplicateTodo(this.todo, records);
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-todo-item {
    @apply
        px-4
        py-3
    ;

    &__top {
        @apply
            flex
            items-center
        ;
    }

    &__data {
        @apply
            bg-cm-00
            px-3
            py-1
            rounded-md
            text-xs
        ;
    }
}

</style>
