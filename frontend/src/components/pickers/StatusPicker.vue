<template>
    <MarkerPicker
        class="c-status-picker"
        dropdownComponent="DropdownFree"
        :group="group"
        placeholder="Add a status"
        type="STATUS"
        comparator="id"
        whichOptionSlot="wholeOption"
        inputSearchPlaceholder="Find a status..."
        :optionsPopupProps="{ widthProp: '9.375rem' }"
        :modelValue="modelValue"
        v-bind="$attrs"
        @update:modelValue="emitModelValue"
    >
        <template
            #selected="{
                display, selectedEvents, original, popupState,
            }"
        >

            <button
                type="button"
                @click="selectedEvents.click"
            >
                <StatusBasic
                    :status="original"
                    class="flex items-center"
                    :statusStyle="statusStyle"
                    :defaultText="display"
                >
                    <i
                        class="far ml-2"
                        :class="popupState ? 'fa-angle-up' : 'fa-angle-down'"
                    >
                    </i>
                </StatusBasic>
            </button>
        </template>

        <template
            #option="{ original, isSelected }"
        >
            <div class="relative">
                <StatusBasic
                    :status="original"
                    class="rounded-none py-2.5 text-center hover:opacity-90"
                    :statusStyle="statusStyle"
                >
                </StatusBasic>

                <div
                    v-if="isSelected"
                    class="c-status-picker__circle"
                >
                </div>
            </div>
        </template>

        <template
            v-if="modelValue"
            #popupEnd="{ selectedEvents }"
        >
            <button
                class="py-1 hover:bg-cm-100 w-full text-center text-xs"
                type="button"
                @click="clearStatus(selectedEvents)"
            >
                Clear status
            </button>
        </template>
    </MarkerPicker>
</template>

<script>

import MarkerPicker from '@/components/pickers/MarkerPicker.vue';

export default {
    name: 'StatusPicker',
    components: {
        MarkerPicker,
    },
    mixins: [
    ],
    props: {
        group: {
            type: [Object, null],
            default: null,
        },
        bgClass: {
            type: String,
            default: 'bg-cm-00',
        },
        statusStyle: {
            type: String,
            default: 'bold',
        },
        modelValue: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        bgColor(color) {
            return this.$root.extraColorDisplay(color);
        },
        clearStatus(selectedEvents) {
            this.$emit('update:modelValue', this.modelValue);
            selectedEvents.click();
        },
        emitModelValue(event) {
            this.$emit('update:modelValue', event);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-status-picker {
    &__circle {
        @apply
            absolute
            bg-primary-600
            border
            border-cm-00
            border-solid
            h-2
            left-1
            rounded-full
            top-1.5
            w-2
        ;
    }
}

</style>
