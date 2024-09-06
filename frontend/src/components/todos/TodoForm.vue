<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.todoListId"
        v-model:formAssigneeGroups="form.assigneeGroups"
        class="o-todo-form"
        v-bind="baseProps"
        :changeListFunction="changeTodoList"
        :integrationAccountId="integrationAccountId"
        @saveItem="saveItem(true)"
        @deleteItem="deleteItem"
        @updateSourceId="updateSourceId"
    >
        <div class="mb-2">
            <div
                class="mb-4"
            >
                <label class="header-form">
                    Name*
                </label>

                <div class="flex items-center w-full">
                    <TodoCheck
                        v-if="!isNew"
                        class="mr-4"
                        :class="{ unclickable: processingCompletion }"
                        :isCompleted="isCompleted"
                        @toggleCompletion="toggleCompletion"
                    >
                    </TodoCheck>

                    <InputBox
                        ref="nameInput"
                        class="w-full"
                        bgColor="gray"
                        formField="name"
                        placeholder="Todo name"
                    >
                    </InputBox>
                </div>
                <div
                    v-if="completedAt"
                    class="bg-primary-100 px-2 py-1 rounded mt-1 inline-block"
                >
                    <DateLabel
                        :date="completedAt"
                        :includeLabel="true"
                        :fullTime="true"
                        mode="OTHER"
                        labelProp="Completed at"
                        iconProp="fa-circle-check"
                        iconColorClass="text-primary-600"
                    >
                    </DateLabel>
                </div>
            </div>
            <div
                class="mb-4"
            >
                <label class="header-form">
                    Priority
                </label>

                <PriorityFlag
                    class="ml-2"
                    :class="{ unclickable: processingPriority }"
                    :priority="priority"
                    :allowedPriorities="allowedPriorities"
                    :isModifiable="true"
                    @selectPriority="selectPriority"
                >
                </PriorityFlag>
            </div>
            <div
                class="mb-4"
            >
                <label class="header-form">
                    Due date
                </label>

                <div
                    class="relative"
                >
                    <AlertTooltip
                        v-if="form.errors().has('dueBy')"
                    >
                        {{ form.errors().getFirst('dueBy') }}
                    </AlertTooltip>

                    <!-- <h6 class="header-uppercase-light my-1">
                        {{ dueDate ? 'Due date' : 'Select due date' }}
                    </h6> -->

                    <div
                        v-if="!dueDate"
                        class="flex flex-wrap gap-2"
                    >
                        <button
                            v-for="option in dueOptions"
                            :key="option.id"
                            v-t="dateOptionLabel(option.id)"
                            type="button"
                            class="o-todo-form__date bg-cm-100 hover:bg-cm-200"
                            @click="dueSelection(option)"
                        >
                        </button>
                    </div>

                    <ButtonEl
                        v-else
                        @click="openModal"
                    >
                        <DateDisplay
                            class="o-todo-form__due bg-cm-100 hover:bg-cm-200"
                            :dateTime="dueDate"
                            :showClear="true"
                            @clearDate="clearDate"
                        >
                        </DateDisplay>
                    </ButtonEl>
                </div>
            </div>

            <div
                class="mb-4"
            >
                <label class="header-form">
                    Repeat
                </label>

                <RecurrenceForm
                    v-model:recurrence="form.recurrence"
                >
                </RecurrenceForm>
            </div>
        </div>
        <Modal
            v-if="isModalOpen"
            containerClass="p-4"
            @closeModal="closeDueDatePicker"
        >
            <DatePicker
                v-model:dateTime="dueDate"
                :timeOptionsProp="{ forceDate: true }"
                mode="DATE_TIME"
            >
            </DatePicker>
        </Modal>
    </FeatureFormBase>
</template>

<script>

import TodoCheck from './TodoCheck.vue';
import PriorityFlag from '@/components/assets/PriorityFlag.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import RecurrenceForm from '@/components/assets/RecurrenceForm.vue';
import DatePicker from '@/components/datePicker/DatePicker.vue';
import DateDisplay from '@/components/time/DateDisplay.vue';

import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    changeTodoList,
    createTodoFromObject,
    updateTodo,
    createTodo,
    deleteTodo,
    toggleCompletion,
    setPriority,
} from '@/core/repositories/todoRepository.js';

import TODO_UPDATED from '@/graphql/todos/subscriptions/TodoUpdated.gql';
import EXTERNAL_TODO from '@/graphql/todos/queries/ExternalTodo.gql';
import TODO from '@/graphql/todos/queries/Todo.gql';

import { getFirstKey } from '@/core/utils.js';
import { getDateAfterPeriod } from '@/core/helpers/dateHelpers.js';

const dueOptions = [
    {
        id: 'TODAY',
    },
    {
        id: 'TOMORROW',
    },
    {
        id: 'NEXT_WEEK',
    },
    {
        id: 'CUSTOM',
    },
];

export default {
    name: 'TodoForm',
    components: {
        TodoCheck,
        PriorityFlag,
        AlertTooltip,
        RecurrenceForm,
        DatePicker,
        DateDisplay,
    },
    mixins: [
        interactsWithFeatureForms,
        interactsWithModal,
    ],
    props: {
        todoList: {
            type: [Object, null],
            default: null,
        },
        todo: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        fullTodo: {
            query() {
                return this.isExternal ? EXTERNAL_TODO : TODO;
            },
            variables() {
                if (this.isExternal) {
                    return {
                        sourceId: this.todo.account.id,
                        todoListId: this.todo.list.id,
                        id: this.todo.id,
                    };
                }
                return { id: this.todo.id };
            },
            skip() {
                return !this.todo?.id;
            },
            update: (data) => createTodoFromObject(getFirstKey(data)),
            fetchPolicy: 'cache-first',
            subscribeToMore: { document: TODO_UPDATED },
        },
    },
    data() {
        return {
            processingPriority: false,
            processingCompletion: false,
            listKey: 'todoListId',
            listObjKey: 'list',
            featureType: 'TODOS',
            form: this.$apolloForm(() => {
                const isExternal = this.todoList?.isExternalList();
                const data = {
                    name: this.todo?.name || '',
                    description: this.todo?.description || '',
                    recurrence: this.todo?.recurrence || null,
                    dueBy: this.todo?.dueBy || null,
                };

                if (this.todoList?.id) {
                    data.todoListId = this.todoList.id;
                }
                if (this.isNew) {
                    data.associations = this.defaultAssociations || [];
                    data.todoListId = this.todoList?.id;
                    data.priority = 0;
                    data.sourceId = this.todoList?.account?.id || null;

                    if (!isExternal) {
                        data.markers = [];
                        data.assigneeGroups = [];
                    }
                } else {
                    data.id = this.todo.id;

                    if (this.todo.account) {
                        data.sourceId = this.todo.account.id;
                    }
                }

                return data;
            }),
        };
    },
    computed: {
        // General
        // Used in mixin
        savedItem() {
            return this.fullTodo;
        },

        // Data
        isCompleted() {
            return this.fullTodo?.isCompleted();
        },
        completedAt() {
            return this.fullTodo?.completedAt;
        },
        allowedPriorities() {
            return this.fullTodo?.allowedPriorities();
        },
        priority() {
            return this.form.priority || this.fullTodo?.priority || 0;
        },
        formHasPriority() {
            return _.has(this.form, 'priority');
        },
        dueDate: {
            get() {
                return this.form.dueBy;
            },
            set(date) {
                this.form.dueBy = date;
                if (!this.isNew) {
                    this.saveItem();
                }
            },
        },
        isMicrosoftItem() {
            return this.fullTodo?.isMicrosoftItem();
        },

        // Form info
        hiddenSections() {
            const sections = ['NAME'];
            if (this.isExternal) {
                sections.push('MARKERS');
            }
            return sections;
        },
        cantModifySections() {
            const sections = [];
            if (this.isExistingExternal) {
                sections.push('LIST');
            }
            return sections;
        },

        // Integrations
        isExternal() {
            return this.isListExternal || this.isTodoExternal;
        },
        isListExternal() {
            return this.todoList?.isExternalList();
        },
        isTodoExternal() {
            return this.fullTodo?.isExternalItem();
        },
        integrationAccountId() {
            return this.todoList?.account?.id;
        },
        isExistingExternal() {
            return this.isExternal && !this.isNew;
        },
        shouldSkipListQuery() {
            return this.isExistingExternal;
        },

    },
    methods: {
        async toggleCompletion() {
            this.processingCompletion = true;
            try {
                await toggleCompletion(this.savedItem, !this.isCompleted);
            } finally {
                this.processingCompletion = false;
            }
        },

        selectPriority(priority) {
            if (this.formHasPriority) {
                // When it is new
                this.form.priority = priority;
            } else {
                this.setPriority(priority);
            }
        },
        updateRecurrence(recurrence) {
            this.form.recurrence = recurrence;
        },
        updateDueBy(dueBy) {
            this.form.dueBy = dueBy;
        },
        async setPriority(priority) {
            this.processingPriority = true;
            try {
                await setPriority(this.savedItem, priority);
            } finally {
                this.processingPriority = false;
            }
        },
        closeDueDatePicker() {
            this.closeModal();
        },
        clearDate() {
            this.dueDate = null;
            if (this.isMicrosoftItem) {
                this.form.recurrence = null;
            }
        },
        dueSelection(option) {
            if (option.id === 'CUSTOM') {
                this.openModal();
            } else {
                this.dueDate = getDateAfterPeriod(option.id);
            }
        },
        dateOptionLabel(id) {
            return `labels.${_.camelCase(id)}`;
        },
    },
    created() {
        this.changeTodoList = changeTodoList;
        this.createFunction = createTodo;
        this.updateFunction = updateTodo;
        this.dueOptions = dueOptions;
        this.deleteFunction = deleteTodo;
    },
    mounted() {
        if (this.isNew) {
            this.$refs.nameInput?.select();
        }
    },
};
</script>

<style scoped>

.o-todo-form {
    &__date {
        transition: 0.2s ease-in-out;
        @apply
            px-2
            rounded-md
            text-xs
        ;
    }

    &__due {
        transition: 0.2s ease-in-out;
        @apply
            inline-flex
            px-2
            py-1
            rounded-full
            text-sm
        ;
    }
}

</style>
