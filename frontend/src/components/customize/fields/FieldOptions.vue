<template>
    <div class="o-field-options">
        <div class="mb-4">
            <div class="flex mb-4 items-center">
                <ToggleButton
                    v-model="hasLabel"
                    size="sm"
                    class="mr-2"
                >
                </ToggleButton>

                <p
                    class="text-sm"
                    @click="setLabel(!hasLabel)"
                >
                    Include a label
                </p>
            </div>

            <template v-if="hasLabel">
                <div class="mb-4">
                    <p
                        class="text-xssm font-semibold mb-1 text-cm-400"
                    >
                        {{ newLabel ? 'Select a label type' : 'Label type' }}
                    </p>
                    <div
                        v-if="isNew || labelNotSet"
                        class="flex"
                    >
                        <button
                            class="button-rounded--sm mx-1"
                            :class="freeTextLabel ? 'button-primary' : 'o-field-options__off'"
                            type="button"
                            @click="updateFreeText"
                        >
                            Free-text label
                        </button>
                        <button
                            class="button-rounded--sm mx-1"
                            :class="!freeTextLabel ? 'button-primary' : 'o-field-options__off'"
                            type="button"
                            @click="updateForm('labeled.freeText', false)"
                        >
                            Dropdown label
                        </button>
                    </div>
                    <div
                        v-else
                        class="button-rounded--sm bg-cm-200 inline-flex"
                    >
                        {{ fieldFreeText ? 'Free-text label' : 'Dropdown label' }}
                    </div>
                </div>

                <div v-if="!freeTextLabel">
                    <p
                        class="text-xssm font-semibold mb-1 text-cm-400"
                    >
                        Label dropdown options
                    </p>

                    <SetOptions
                        :options="labelOptions"
                        @update:options="updateForm('labeled.labels', $event)"
                    >
                    </SetOptions>
                </div>
            </template>
        </div>

        <div>
            <h5
                class="mb-2"
                :class="headerClass"
            >
                List or single field?
            </h5>

            <div v-if="isNew">
                <button
                    class="button-rounded--sm mx-1"
                    :class="!listOption ? 'button-primary' : 'o-field-options__off'"
                    type="button"
                    @click="updateForm('list', false)"
                >
                    Single
                </button>
                <button
                    class="button-rounded--sm mx-1"
                    :class="listOption ? 'button-primary' : 'o-field-options__off'"
                    type="button"
                    @click="updateForm('list', true)"
                >
                    List
                </button>
            </div>

            <div
                v-else
                class="button-rounded--sm bg-cm-200 inline-flex"
            >
                {{ field.options.list ? 'List' : 'Single' }}
            </div>

            <div
                v-if="listOption"
                class="mt-3"
            >
                <CheckHolder
                    :modelValue="isMetaListNumbered"
                    size="sm"
                    @update:modelValue="setMetaListDisplay($event, 'NUMBERED')"
                >
                    Make it a numbered list?
                </CheckHolder>
            </div>
        </div>
    </div>
</template>

<script>

import SetOptions from '@/components/assets/SetOptions.vue';

const labelObj = {
    labels: {},
    freeText: true,
};

export default {
    name: 'FieldOptions',
    components: {
        SetOptions,
    },
    mixins: [
    ],
    props: {
        options: {
            type: Object,
            required: true,
        },
        meta: {
            type: [Object, null],
            required: true,
        },
        headerClass: {
            type: String,
            default: 'header-uppercase',
        },
        isNew: Boolean,
        field: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:options',
        'update:meta',
    ],
    data() {
        return {

        };
    },
    computed: {
        fieldOptions() {
            return this.field.options;
        },
        newLabel() {
            return this.isNew || this.labelNotSet;
        },
        labelNotSet() {
            return !this.fieldOptions?.labeled;
        },
        listOption() {
            return this.options.list;
        },
        isListField() {
            return this.fieldOptions.list || this.listOption;
        },
        freeTextLabel() {
            return this.options?.labeled.freeText;
        },
        fieldFreeText() {
            return this.fieldOptions?.labeled?.freeText;
        },
        labelOptions() {
            return this.options?.labeled.labels;
        },
        hasLabel: {
            get() {
                return !!this.options.labeled;
            },
            set(val) {
                this.setLabel(val);
            },
        },
        isMetaListNumbered() {
            return this.meta?.listDisplay === 'NUMBERED';
        },

    },
    methods: {
        setLabel(val) {
            if (!val) {
                this.$emit('update:options', _.omit(this.options, 'labeled'));
            } else {
                const clone = _.clone(labelObj);
                this.updateForm('labeled', clone);
            }
        },
        updateForm(valKey, val) {
            this.$proxyEvent(val, this.options, valKey, 'update:options');
        },
        updateFreeText() {
            this.$proxyEvent({}, this.options, 'labeled.labels', 'update:options');
            this.$proxyEvent(true, this.options, 'labeled.freeText', 'update:options');
        },
        setMetaListDisplay(val, displayKey) {
            // For now just numbered or nothing, but later can do more with it
            if (!val) {
                const clonedMeta = _.cloneDeep(this.meta);
                delete clonedMeta.listDisplay;
                this.$emit('update:meta', clonedMeta);
            } else {
                this.$proxyEvent(displayKey, this.meta, 'listDisplay', 'update:meta');
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-field-options {
    &__off {
        @apply
            bg-cm-00
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }
}

</style>
