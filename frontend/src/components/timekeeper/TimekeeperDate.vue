<template>
    <div
        class="o-timekeeper-date relative"
        :class="{ unclickable: processing }"
    >
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
                :alertPosition="{ left: 0, top: '-30px' }"
            >
                {{ error }}
            </AlertTooltip>
        </transition>

        <div
            v-if="showDateDisplay"
            class="flex items-center"
        >
            <DateDisplay
                class="button-rounded bg-cm-100 inline-flex mr-2"
                :dateTime="dateTime"
                @click="openEdit"
            >
            </DateDisplay>

            <ActionButtons
                @edit="openEdit"
                @delete="$emit('removeDate')"
            >
            </ActionButtons>
        </div>

        <div
            v-else
            class="flex items-center"
        >
            <DateTimeInput
                :dateTime="dateTime"
                :timeOptionsProp="{ forceDate: true, forceTime: true }"
                @update:dateTime="$emit('update:dateTime', $event)"
            >
            </DateTimeInput>

            <button
                v-if="showCloseEdit"
                v-t="savedValue && dateTime ? 'common.cancel' : 'common.clear'"
                class="button--sm button-gray ml-6"
                type="button"
                @click="closeEdit"
            >
            </button>
        </div>
    </div>
</template>

<script>

import ActionButtons from '@/components/buttons/ActionButtons.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import DateDisplay from '@/components/time/DateDisplay.vue';
import DateTimeInput from '@/components/dateTimeInputs/DateTimeInput.vue';

export default {
    name: 'TimekeeperDate',
    components: {
        ActionButtons,
        AlertTooltip,
        DateDisplay,
        DateTimeInput,
    },
    mixins: [
    ],
    props: {
        dateTime: {
            type: [String, Object, null],
            required: true,
        },
        editing: Boolean,
        processing: Boolean,
        savedValue: {
            type: [String, null],
            required: true,
        },
        error: {
            type: String,
            default: null,
        },
    },
    emits: [
        'update:dateTime',
        'removeDate',
        'setEdit',
    ],
    data() {
        return {
        };
    },
    computed: {
        showDateDisplay() {
            return this.savedValue && !this.editing && !this.processing;
        },
        showCloseEdit() {
            return (this.savedValue || this.dateTime) && !this.processing;
        },
    },
    methods: {
        openEdit() {
            this.$emit('setEdit', true);
        },
        closeEdit() {
            this.$emit('setEdit', false);
        },
        emitDate(dateTime) {
            this.$emit('update:dateTime', dateTime);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-timekeeper-date {

} */

</style>
