<template>
    <div class="o-todo-quick-form">
        <FormWrapper
            :form="form"
            @submit="submitTodo"
        >
            <div class="flex items-start">
                <div class="mr-2 flex-1">
                    <template
                        v-if="isCollaborative"
                    >
                        <AssigneesPicker
                            v-model:assigneeGroups="formAssigneeGroups"
                            :isButtonless="true"
                            :forceDropdownVisible="showAssigneePopup"
                            :hideSearch="true"
                            :optionsPopupProps="{
                                top: true,
                                nudgeUpProp: '0.5rem',
                            }"
                            :searchQueryProp="assigneeSearch"
                            :hasEmitVisibleOptions="true"
                            @assigneeGroupOptions="setAssigneeOptions"
                        >
                        </AssigneesPicker>
                    </template>

                    <InputBox
                        ref="input"
                        formField="name"
                        placeholder="What's your next to-do?"
                        @keydown.escape="escapeFromInput"
                    >
                    </InputBox>
                </div>

                <button
                    class="button button-secondary mr-4"
                    :class="{ unclickable: processing }"
                    :disabled="processing"
                    type="button"
                    @click="openModal"
                >
                    <i class="far fa-calendar-check"></i>
                </button>

                <div>
                    <button
                        v-t="'features.todos.add'"
                        class="button button-primary"
                        :class="{ unclickable: isSubmitOff }"
                        :disabled="isSubmitOff"
                        type="submit"
                    >
                    </button>
                    <div
                        v-if="associationsLength"
                        class="mt-2"
                    >
                        <div
                            v-for="association in form.associations"
                            :key="association.id"
                            class="h-12 w-12 min-w-12 flex flex-wrap"
                        >
                            <ConnectedRecord
                                class="h-full w-full text-lg"
                                :item="association"
                                :isMinimized="true"
                                imageSize="full"
                                @click.stop
                            >
                            </ConnectedRecord>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-2 relative">
                <div class="relative flex items-center">
                    <DateDisplay
                        v-if="dueDate"
                        :dateTime="dueDate"
                        class="o-todo-quick-form__data mr-2"
                    >
                    </DateDisplay>

                    <RecurrenceDisplay
                        v-if="form.recurrence"
                        class="o-todo-quick-form__data"
                        :recurrence="form.recurrence"
                    >
                    </RecurrenceDisplay>

                    <transition name="t-fade">
                        <AlertTooltip
                            v-if="form.errors().has('dueBy')"
                            :alertPosition="{ right: 0 }"
                        >
                            {{ form.errors().getFirst('dueBy') }}
                        </AlertTooltip>
                    </transition>
                </div>
                <div>
                    <AssigneesPicker
                        v-if="formAssigneeLength"
                        v-model:assigneeGroups="formAssigneeGroups"
                        bgColor="white"
                    >
                    </AssigneesPicker>
                </div>
            </div>

            <TodoDateModal
                v-if="isModalOpen"
                v-model:dateTime="form.dueBy"
                v-model:recurrence="form.recurrence"
                @closeModal="closeModal"
            >
            </TodoDateModal>

        </FormWrapper>
    </div>
</template>

<script>
import TodoDateModal from '@/components/todos/TodoDateModal.vue';
import DateDisplay from '@/components/time/DateDisplay.vue';
import RecurrenceDisplay from '@/components/time/RecurrenceDisplay.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import AssigneesPicker from '@/components/pickers/AssigneesPicker.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { arrRemoveId, getSectionForAssignees } from '@/core/utils.js';
import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

import {
    createTodo,
} from '@/core/repositories/todoRepository.js';

export default {
    name: 'TodoQuickForm',
    components: {
        TodoDateModal,
        AssigneesPicker,
        DateDisplay,
        RecurrenceDisplay,
        AlertTooltip,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        todoList: {
            type: Object,
            required: true,
        },
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
    },
    data() {
        return {
            processing: false,
            form: this.$apolloForm(() => {
                const data = {
                    todoListId: this.todoList.id,
                    name: '',
                    dueBy: null,
                    recurrence: null,
                    sourceId: this.todoList.account?.id || null,
                    associations: this.defaultAssociations || [],
                };
                if (isActiveBaseCollaborative()) {
                    data.assigneeGroups = [];
                }
                return data;
            }, { clear: true }),
            assigneeSearch: '',
            showAssigneePopup: false,
            assigneeGroupOptions: [],
        };
    },
    computed: {
        dueDate() {
            return this.form.dueBy;
        },
        isSubmitOff() {
            return this.processing || !this.form.name;
        },
        associationsLength() {
            return this.form.associations?.length;
        },
        displayAssigneePopup() {
            return this.isCollaborative && this.showAssigneePopup;
        },
        isCollaborative() {
            return isActiveBaseCollaborative();
        },
        formAssigneeLength() {
            return this.form.assigneeGroups?.length;
        },
        assigneeGroupOptionsLength() {
            return this.assigneeGroupOptions?.length || 0;
        },
        hasOneAssigneeOption() {
            return this.assigneeGroupOptionsLength === 1
                && this.firstGroupAssignees?.length === 1;
        },
        firstGroupOption() {
            return this.assigneeGroupOptions[0];
        },
        firstGroupAssignees() {
            return this.firstGroupOption?.assignees;
        },
        firstGroupAssignee() {
            return this.firstGroupAssignees?.[0];
        },
        formAssigneeGroups: {
            get() {
                return this.form.assigneeGroups;
            },
            set(val) {
                this.form.assigneeGroups = val;
                this.clearAllAssigneeThings();
                this.$refs.input.focus();
            },
        },
        formAssignees() {
            return _.flatMap(this.form.assigneeGroups, 'assignees');
        },
        nameForAssigneeSearch() {
            // This is only needed if collaborative
            // as currently the logic only pertains to assignees

            // Section that is watched for the assignee filter
            if (this.isCollaborative && this.form.name) {
                return this.form.name;
            }
            return '';
        },
    },
    methods: {
        submitTodo() {
            if (this.showAssigneePopup) {
                this.toggleAssignee();
            } else {
                this.saveTodo();
            }
        },
        toggleAssignee() {
            if (this.hasOneAssigneeOption) {
                const firstAssigneeAlreadyInForm = this.formAssignees.find((assignee) => {
                    return assignee.id === this.firstGroupAssignee.id;
                });

                if (firstAssigneeAlreadyInForm) {
                    this.removeAssignee();
                } else {
                    this.addAssignee();
                }
            }
        },
        addAssignee() {
            const group = this.firstGroupOption;
            const assignee = this.firstGroupAssignee;

            const groupIndex = this.formAssigneeGroups.findIndex((assigneeGroup) => {
                const groupId = assigneeGroup.groupId;
                return groupId === group.group.id;
            });

            if (~groupIndex) {
                this.form.assigneeGroups[groupIndex].assignees.push(assignee);
            } else {
                this.form.assigneeGroups.push({
                    groupId: this.firstGroupOption.group.id,
                    assignees: [assignee],
                });
            }
            this.clearAllAssigneeThings();
        },
        closeAssigneePopup() {
            this.showAssigneePopup = false;
        },
        resetAssigneeSearch() {
            this.assigneeSearch = '';
        },
        clearAssigneeFromName() {
            this.form.name = this.form.name.replace(/!!\S*\s?/, '');
        },
        removeAssignee() {
            const group = this.firstGroupOption;
            const assignee = this.firstGroupAssignee;

            const groups = this.formAssigneeGroups.map((assigneeGroup) => {
                const groupId = assigneeGroup.group?.id || assigneeGroup.groupId;
                const assignees = groupId === group.group.id
                    ? arrRemoveId(assigneeGroup.assignees, assignee.id)
                    : [...assigneeGroup.assignees];
                return {
                    groupId,
                    assignees,
                };
            });
            const filteredGroups = groups.filter((assigneeGroup) => assigneeGroup.assignees.length);
            this.form.assigneeGroups = filteredGroups;
            this.clearAllAssigneeThings();
        },
        escapeFromInput() {
            this.closeAssigneePopup();
            this.resetAssigneeSearch();
        },
        clearAllAssigneeThings() {
            this.clearAssigneeFromName();
            this.closeAssigneePopup();
            this.resetAssigneeSearch();
        },
        async saveTodo() {
            // Preserve the list ID between requests.
            // otherwise creating todos outside the context of a list would
            // require setting the list every time.
            const listId = this.form.todoListId;
            this.processing = true;
            try {
                await createTodo(this.form);
            } finally {
                this.form.todoListId = listId;
                this.processing = false;
            }
        },
        setAssigneeOptions(assigneeGroupOptions) {
            this.assigneeGroupOptions = assigneeGroupOptions;
        },
    },
    watch: {
        'todoList.id': {
            handler() {
                this.form.todoListId = this.todoList.id;
            },
        },
        nameForAssigneeSearch: {
            handler(newVal) {
                const isLastValueSpace = newVal.slice(-1) === ' ';
                const newCodon = getSectionForAssignees(newVal);
                // const oldCodon = getSectionForAssignees(oldVal);
                const hasExclamations = newCodon.includes('!!');
                const regex = /!!/g;
                const newCodonWithoutExclamations = newCodon.replace(regex, '');
                // const oldCodonWithoutExclamations = oldCodon.replace(regex, '');

                if (hasExclamations) {
                    this.showAssigneePopup = true;
                    if (newCodonWithoutExclamations) {
                        this.assigneeSearch = newCodonWithoutExclamations;
                    }
                    if (isLastValueSpace && this.hasOneAssigneeOption) {
                        this.toggleAssignee();
                    }
                } else {
                    this.resetAssigneeSearch();
                    this.closeAssigneePopup();
                }
            },
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-todo-quick-form {

    &__data {
        @apply
            bg-cm-00
            px-3
            py-1
            rounded-md
            text-xs
            w-fit
        ;
    }
}

</style>
