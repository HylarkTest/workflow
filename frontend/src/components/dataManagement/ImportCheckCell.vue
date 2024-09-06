<template>
    <div class="o-import-check-cell">
        <DisplayerContainer
            v-if="showDisplayer"
            :dataInfo="dataInfo"
            :item="item"
            v-bind="scope"
        >
        </DisplayerContainer>

        <div
            v-if="rowError"
        >
            <div class="o-import-check-cell__error">

            </div>

            <div
                v-if="nameCell"
                class="relative"
            >
                <div
                    class="text-xs font-semibold text-rose-600 uppercase mb-2"
                >
                    {{ $t('imports.warnings.rowCannotBeImported') }}
                </div>

                <div
                    class="mt-2 text-xs"
                >
                    {{ rowError }}
                </div>
            </div>
        </div>

        <div
            v-if="errorOfType"
            class="centered flex-col"
        >
            <div
                class="text-xs font-semibold text-rose-600 uppercase mb-2"
            >
                {{ $t('imports.warnings.cannotBeImported') }}
            </div>

            <div
                class="o-import-check-cell__box"
            >
                {{ errorValue }}
            </div>

            <div
                v-for="(reason, index) in errorReasons"
                :key="index"
                class="mt-2 text-xs"
            >
                {{ reason }}
            </div>
        </div>
    </div>
</template>

<script setup>

import { computed } from 'vue';

const props = defineProps({
    scope: {
        type: Object,
        required: true,
    },
    item: {
        type: Object,
        required: true,
    },
    dataInfo: {
        type: Object,
        required: true,
    },
});

const nameCell = computed(() => {
    return props.dataInfo.id === 'SYSTEM_NAME';
});

const rowError = computed(() => {
    return props.item.rowError;
});

const itemErrors = computed(() => {
    return props.item.errors;
});

const hasErrors = computed(() => {
    return itemErrors.value?.length || 0;
});

const errorOfType = computed(() => {
    if (!hasErrors.value) {
        return false;
    }
    // Unsure about id or formattedId here, might be the same for now
    // It will not matter until multifields or more complex data
    // at which point probably formattedId
    const formattedId = props.dataInfo.formattedId;
    return itemErrors.value.find((error) => {
        return error.fieldId === formattedId;
    });
});

const errorValue = computed(() => {
    return errorOfType?.value.value;
});

const errorReasons = computed(() => {
    return errorOfType?.value.errors;
});

const showDisplayer = computed(() => {
    return !errorOfType.value;
});

</script>

<style scoped>

.o-import-check-cell {
    &__error {
        @apply
            absolute
            bg-rose-100
            h-full
            left-0
            p-1
            text-sm
            top-0
            w-full
        ;
    }

    &__box {
        @apply
            bg-rose-100
            border
            border-rose-400
            border-solid
            p-1
            rounded-md
            text-sm
        ;
    }
}

</style>
