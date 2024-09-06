<template>
    <Modal
        class="o-todo-date-modal"
        containerClass="pt-0 pb-4 w-[300px]"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template #header>
            <i class="far fa-calendar-check mr-2"></i>
            {{ $t('todos.new.dueBy') }}
        </template>

        <div class="flex items-center flex-col">
            <div>
                <div
                    class="flex flex-wrap gap-2 mb-4"
                >
                    <button
                        v-for="option in dueOptions"
                        :key="option.id"
                        v-t="dateOptionLabel(option.id)"
                        type="button"
                        class="o-todo-date-modal__date bg-primary-600 hover:bg-primary-500 text-cm-00"
                        @click="dueSelection(option)"
                    >
                    </button>
                </div>
            </div>

            <DateRepeatPicker
                recurrenceTitlePath="todos.new.repeat"
                v-bind="$attrs"
                mode="DATE_TIME"
                :timeOptionsProp="{ forceDate: true }"
                :dateNullable="true"
                :showTimeClear="true"
                @update:dateTime="updateDateTime"
                @update:recurrence="updateRecurrence"
            >
            </DateRepeatPicker>
        </div>
    </Modal>
</template>

<script setup>

import DateRepeatPicker from '@/components/pickers/DateRepeatPicker.vue';

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
];

const emit = defineEmits([
    'closeModal',
    'update:dateTime',
    'update:recurrence',
]);

const dateOptionLabel = (id) => {
    return `labels.${_.camelCase(id)}`;
};

const updateRecurrence = (recurrence) => {
    emit('update:recurrence', recurrence);
};

const updateDateTime = (dateTime) => {
    emit('update:dateTime', dateTime);
};

const dueSelection = (option) => {
    const dateTime = getDateAfterPeriod(option.id);
    updateDateTime(dateTime);
};

const closeModal = () => {
    emit('closeModal');
};

</script>

<style scoped>

.o-todo-date-modal {
    &__date {
        transition: 0.2s ease-in-out;
        @apply
            px-3
            py-1
            rounded-full
            text-sm
        ;
    }
}

</style>
