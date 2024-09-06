<template>
    <ButtonEl
        class="o-todo-mini feature__item--mini"
        :class="itemClasses"
        @click="$emit('selectTodo', todo)"
    >
        <TodoCheck
            class="mr-2"
            size="sm"
            :isCompleted="isCompleted"
            @toggleCompletion="toggleCompletion"
            @click.stop
        >
        </TodoCheck>

        <div class="flex-1 min-w-0">
            <div class="flex items-start">
                <div
                    class="o-todo-mini__title flex-1"
                    :class="nameClass"
                >
                    {{ displayedName }}
                </div>

                <PriorityFlag
                    v-if="priority"
                    class="ml-2"
                    :isModifiable="true"
                    :priority="priority"
                    @selectPriority="selectPriority"
                    @click.stop
                >
                </PriorityFlag>

                <div
                    v-if="showExtras"
                    class="ml-2"
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

            <DateDisplay
                v-if="dueBy"
                :dateTime="dueBy"
                :isMicrosoftItem="isMicrosoftItem"
                class="text-xxs text-cm-500"
            >
                <div
                    v-if="isOverdue"
                    class="rounded-full bg-rose-500 h-2 w-2 ml-1"
                >

                </div>
            </DateDisplay>

            <div
                v-if="!tinyVersion && markersLength"
                class="flex gap-2 justify-end flex-wrap mt-1"
            >
                <EditableMarkerSet
                    v-if="markersLength"
                    :item="todo"
                    :tags="tags"
                    :pipelines="pipelines"
                    :statuses="statuses"
                >
                </EditableMarkerSet>
            </div>

            <div
                v-if="showAssignees"
                class="mt-1 flex justify-end"
            >
                <AssigneesPicker
                    v-model:assigneeGroups="assigneeGroups"
                    displaySize="xs"
                >
                </AssigneesPicker>
            </div>
        </div>
    </ButtonEl>
</template>

<script>

import PriorityFlag from '@/components/assets/PriorityFlag.vue';

import interactsWithTodoItem from '@/vue-mixins/interactsWithTodoItem.js';
import providesColors from '@/vue-mixins/style/providesColors.js';
import { duplicateTodo } from '@/core/repositories/todoRepository.js';

export default {
    name: 'TodoMini',
    components: {
        PriorityFlag,
    },
    mixins: [
        providesColors,
        interactsWithTodoItem,
    ],
    props: {
        showExtras: Boolean,
        hoverable: Boolean,
        tinyVersion: Boolean,
    },
    emits: [
        'selectTodo',
    ],
    data() {
        return {
            bar: ' | ',
        };
    },
    computed: {
        featureItem() {
            return this.todo;
        },
        hoverableClass() {
            return { 'o-todo-mini--unopenable': !this.hoverable };
        },
        itemClasses() {
            return [
                this.hoverableClass,
                this.actionProcessingClass,
                this.deleteProcessingClass,
            ];
        },
        hasTags() {
            return this.tags?.length;
        },
        nameTruncated() {
            return _.truncate(this.name, { length: 30 });
        },
        displayedName() {
            return this.tinyVersion ? this.nameTruncated : this.name;
        },
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

.o-todo-mini {
    @apply
        flex
        items-baseline
        text-xssm
    ;

    &__title {
        @apply
            font-semibold
        ;
    }

    &--unopenable {
        @apply
            shadow-none
        ;
    }
}

</style>
