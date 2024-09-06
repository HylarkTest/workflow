import Todo from '@/core/models/Todo.js';

import ExtrasButton from '@/components/buttons/ExtrasButton.vue';
import PriorityFlag from '@/components/assets/PriorityFlag.vue';
import TodoCheck from '@/components/todos/TodoCheck.vue';
import RecurrenceDisplay from '@/components/time/RecurrenceDisplay.vue';
import DateDisplay from '@/components/time/DateDisplay.vue';
import FeatureSource from '@/components/features/FeatureSource.vue';

import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';

import {
    deleteTodo,
    setPriority,
    toggleCompletion,
} from '@/core/repositories/todoRepository.js';

import { associateItem, removeItem } from '@/core/repositories/itemRepository.js';
import { removeMarker, setMarker } from '@/core/repositories/markerRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import EXTERNAL_TODOS from '@/graphql/todos/queries/ExternalTodos.gql';
import EXTERNAL_TODO from '@/graphql/todos/queries/ExternalTodo.gql';

const tagStyle = {
    shape: 'rounded',
    size: 'sm',
    fillColor: 'brandIntense',
    textColor: 'white',
    weight: 'bold',
};

export default {
    components: {
        PriorityFlag,
        TodoCheck,
        RecurrenceDisplay,
        DateDisplay,
        ExtrasButton,
        FeatureSource,
    },
    mixins: [
        interactsWithFeatureItem,
    ],
    props: {
        todo: {
            type: Todo,
            required: true,
        },
        list: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'updateTodo',
        'deleteTodo',
    ],
    data() {
        return {
        };
    },
    computed: {
        featureItem() {
            return this.todo;
        },
        isMicrosoftItem() {
            return this.featureItem.isMicrosoftItem();
        },
        nameClass() {
            let nameClass = '';
            if (this.isCompleted) {
                nameClass = nameClass.concat('line-through ');
            }
            if (!this.name) {
                nameClass = nameClass.concat('bg-cm-100 px-1 rounded-md');
            }
            return nameClass;
        },
        name() {
            return this.featureItem.name;
        },
        nameDisplay() {
            return this.name || 'Untitled todo';
        },
        isSelected() {
            return this.featureItem.id === this.selectedItem?.id;
        },
        isCompleted() {
            return this.featureItem.isCompleted();
        },
        priority() {
            return this.featureItem.priority;
        },
        hasPriority() {
            return this.featureItem.hasPriority();
        },
        dueBy() {
            return this.featureItem.dueBy;
        },
        recurrence() {
            return this.featureItem.recurrence;
        },
        allowedPriorities() {
            return this.featureItem.allowedPriorities();
        },
        omitTimeCondition() {
            // Microsoft Todos don't allow time in the "due by" field. So all
            // the dates have the time set to 00:00
            return this.isMicrosoftItem ? '00:00' : '23:59';
        },
        isOverdue() {
            return this.$dayjs().isAfter(this.dueBy);
        },
        markerGroups() {
            return this.featureItem.markerGroups;
        },
        grouped() {
            return _(this.markerGroups).groupBy((group) => {
                return group.group.type;
            }).value();
        },
        tags() {
            return this.grouped.TAG;
        },
        statuses() {
            return this.grouped.STATUS;
        },
        pipelines() {
            return this.grouped.PIPELINE;
        },
        associations() {
            return this.featureItem.associations;
        },
        hasAssociations() {
            return this.associations?.length;
        },
        hasAdditional() {
            return this.markerGroups?.length || this.hasAssociations;
        },
        isExternal() {
            return this.featureItem.isExternalItem();
        },
        showAssignees() {
            return this.isCollaborativeBase && !this.isExternal;
        },
    },
    methods: {
        updateTodo(form) {
            this.$emit('updateTodo', form);
        },
        async toggleCompletion() {
            this.updateProcessing('action', true);
            try {
                await toggleCompletion(this.featureItem, !this.isCompleted);
            } finally {
                this.updateProcessing('action', false);
            }
        },
        async selectPriority(priority) {
            this.updateProcessing('action', true);
            try {
                await setPriority(this.featureItem, priority);
            } finally {
                this.updateProcessing('action', false);
            }
        },
        selectTodo(todo) {
            if (this.isExternal) {
                const client = this.$apollo.getClient();
                client.mutate({
                    mutation: EXTERNAL_TODO,
                    variables: {
                        sourceId: this.todo.account.id,
                        todoListId: this.todo.list.id,
                        id: todo.id,
                    },
                })
                    .then(() => {
                        this.$emit('selectTodo', todo);
                    })
                    .catch((error) => {
                        if (!checkAndHandleMissingError(error, false)) {
                            throw error;
                        }
                        client.refetchQueries({ include: [EXTERNAL_TODO_LISTS, EXTERNAL_TODOS] });
                    });
            } else {
                this.$emit('selectTodo', todo);
            }
        },
        addAssociation(item) {
            associateItem(this.featureItem, item);
        },
        removeAssociation(item) {
            removeItem(this.featureItem, item);
        },
        addMarker(marker) {
            setMarker(this.featureItem, marker);
        },
        removeMarker(marker) {
            removeMarker(this.featureItem, marker);
        },
    },
    created() {
        this.tagStyle = tagStyle;
        this.deleteFunction = deleteTodo;
    },
};
