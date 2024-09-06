<template>
    <div class="o-field-labels">
        <h4 class="settings-form__title">
            Labels
        </h4>

        <InputHeader>
            <ToggleButton
                v-model="labeledValues"
            >
            </ToggleButton>
            <template #header>
                Include a label option
            </template>
            <template #paragraph>
                {{ hasList
                    ? 'Every value in the list will have its own label field'
                    : 'This single value will have a label field'
                }}
            </template>
        </InputHeader>

        <InputHeader
            v-if="labeledValues"
            class="mt-8"
        >
            <ToggleButton
                :modelValue="labeled.required"
                @update:modelValue="$proxyEvent($event, labeled, 'required', 'update:labeled')"
            >
            </ToggleButton>
            <template #header>
                Make this label mandatory
            </template>
        </InputHeader>
        <div
            v-if="labeledValues"
            class="mt-8"
        >
            <h5 class="font-semibold mb-4 text-sm">
                Define label options or leave empty for free-text label
            </h5>
            <div class="flex flex-col items-start md:flex-row">
                <div class="flex-shrink">
                    <div
                        class="flex mr-10"
                    >
                        <InputLine
                            ref="labelInput"
                            v-model="currentLabel"
                            class="mb-4"
                            placeholder="Label option"
                            maxlength="40"
                            :disabled="reachedMax"
                            @keydown.enter.prevent="addLabel"
                        >
                        </InputLine>
                        <button
                            type="button"
                            class="o-field-labels__add bg-primary-600 circle-center hover:bg-primary-500"
                            :class="{ 'opacity-25 no-pointer': !currentLabel.length || reachedMax }"
                            @click="addLabel"
                        >
                            <i class="far fa-plus"></i>
                        </button>
                    </div>
                    <p
                        v-if="labelsLength && reachedMax"
                        class="o-field-labels__notice max-w-200p mb-4"
                    >
                        You have reached the maximum number of labels
                    </p>
                </div>
                <div
                    v-if="labelsLength"
                    class="flex-1"
                >
                    <div
                        v-for="(label, index) in labeled.labels"
                        :key="index"
                        class="flex text-sm mb-2"
                    >
                        <button
                            type="button"
                            class="mr-2"
                            @click="removeLabel(index)"
                        >
                            <i class="text-cm-500 hover:text-primary-600 fal fa-times">
                            </i>
                        </button>
                        <p>{{ label }}</p>
                    </div>
                </div>
                <p
                    v-else
                    class="o-field-labels__notice"
                >
                    No labels options have been added
                </p>
            </div>
        </div>
    </div>
</template>

<script>

import InputHeader from '@/components/display/InputHeader.vue';

import { arrRemoveIndex } from '@/core/utils.js';

const labeledObject = {
    labels: [],
    freeText: true,
    required: false,
};

export default {

    name: 'FieldLabels',
    components: {
        InputHeader,
    },
    mixins: [
    ],
    props: {
        labeled: {
            type: Object,
            default: null,
        },
        hasList: Boolean,
    },
    emits: [
        'update:labeled',
    ],
    data() {
        return {
            currentLabel: '',
        };
    },
    computed: {
        labelsLength() {
            return this.labeled.labels?.length;
        },
        reachedMax() {
            // 10 is the maximum
            return this.labelsLength > 9;
        },
        labeledValues: {
            get() {
                return !!this.labeled;
            },
            set(value) {
                // TODO: Make initial value as what it is set to (or default object);
                if (value) {
                    this.$emit('update:labeled', labeledObject);
                } else {
                    this.$emit('update:labeled', null);
                }
            },
        },
    },
    methods: {
        async addLabel() {
            if (this.currentLabel) {
                if (!this.labeled.labels.length) {
                    await this.$proxyEvent(false, this.labeled, 'freeText', 'update:labeled');
                }
                const newLabels = [...this.labeled.labels, this.currentLabel];
                this.$proxyEvent(newLabels, this.labeled, 'labels', 'update:labeled');
                this.currentLabel = '';
            }
        },
        showDelete(index) {
            return index !== (this.labelsLength - 1);
        },
        async removeLabel(index) {
            const labels = this.labeled.labels;
            const splicedLabels = arrRemoveIndex(labels, index);
            await this.$proxyEvent(splicedLabels, this.labeled, 'labels', 'update:labeled');
            if (!splicedLabels.length) {
                this.$proxyEvent(true, this.labeled, 'freeText', 'update:labeled');
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-field-labels {
    &__notice {
        @apply
            bg-primary-100
            leading-normal
            px-6
            py-3
            rounded
            text-primary-700
            text-sm
        ;
    }

    &__add {
        @apply
            cursor-pointer
            h-6
            min-w-6
            ml-3
            text-13p
            text-cm-00
            w-6
        ;
    }
}
</style>
