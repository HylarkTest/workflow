<template>
    <ButtonEl
        class="o-todo-kanban feature__item feature__item--style"
        :class="mainClasses"
        @click="selectTodo(todo)"
        @keyup.enter="selectTodo(todo)"
        @keyup.space="selectTodo(todo)"
    >
        <div
            class="flex"
        >
            <TodoCheck
                class="mr-3"
                :isCompleted="isCompleted"
                @toggleCompletion="toggleCompletion"
            >
            </TodoCheck>

            <h6
                class="font-semibold flex-1"
                :class="nameClass"
            >
                {{ nameDisplay }}
            </h6>

            <PriorityFlag
                class="ml-2"
                :priority="priority"
                :isModifiable="true"
                :allowedPriorities="allowedPriorities"
                @selectPriority="selectPriority"
                @click.stop
            >
            </PriorityFlag>

            <ExtrasButton
                class="ml-2"
                :options="['delete']"
                alignRight
                nudgeDownProp="0.375rem"
                nudgeRightProp="0.375rem"
                @click.stop
                @selectOption="selectOption"
            >
            </ExtrasButton>
        </div>

        <div
            v-if="dueBy || recurrence"
            class="flex ml-8 mt-1"
        >
            <DateDisplay
                v-if="dueBy"
                :dateTime="dueBy"
                :isMicrosoftItem="isMicrosoftItem"
                class="mr-4"
            >
                <div
                    v-if="isOverdue"
                    class="rounded-full bg-rose-500 h-2 w-2 ml-1"
                >

                </div>
            </DateDisplay>

            <RecurrenceDisplay
                v-if="recurrence"
                :recurrence="recurrence"
            >
            </RecurrenceDisplay>
        </div>

        <FeatureAdditional
            v-if="hasAdditional"
            class="mt-2"
            :associations="associations"
            :tags="tags"
            :bgClass="additionalBgClass"
            :pipelines="pipelines"
            :statuses="statuses"
        >
        </FeatureAdditional>
    </ButtonEl>
</template>

<script>
import interactsWithTodoItem from '@/vue-mixins/interactsWithTodoItem.js';

export default {
    name: 'TodoKanban',
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
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-todo-kanban {
    @apply
        mb-2
        p-4
    ;
}

</style>
