<template>
    <div class="o-field-rules">
        <h4 class="settings-form__title">
            Rules
        </h4>
        <InputHeader
            v-if="hasRequired"
        >
            <ToggleButton
                :modelValue="rules.required"
                @update:modelValue="$proxyEvent($event, rules, 'required', 'update:rules')"
            >
            </ToggleButton>
            <template #header>
                Require this field
            </template>
        </InputHeader>
        <div
            v-if="instructions && instructions.selectRules"
            class="mt-8"
        >
            <h5 class="font-semibold mb-3 text-sm">
                {{ instructions.selectRules.text }}
            </h5>
            <div>
                <CheckHolder
                    v-for="(field, index) in whichOptions"
                    :key="index"
                    class="mb-1"
                    :val="field"
                    :value="(rules && rules[instructions.selectRules.key]) || []"
                    @input="$proxyEvent($event, rules, instructions.selectRules.key, 'update:rules')"
                >
                    {{ optionText(field) }}
                </CheckHolder>
            </div>
        </div>
        <div
            v-if="rules && rules.max && instructions"
            class="mt-8"
        >
            <h5 class="font-semibold mb-3 text-sm">
                {{ instructions.maxMessage || 'Maximum characters allowed in this field' }}
            </h5>
            <InputLine
                class="w-20"
                type="number"
                :max="instructions.max"
                min="1"
                :value="rules.max"
                @input="$proxyEvent($event, rules, 'max', 'update:rules')"
            >
            </InputLine>
        </div>
    </div>
</template>

<script>

import InputHeader from '@/components/display/InputHeader.vue';

export default {

    name: 'FieldRules',
    components: {
        InputHeader,
    },
    mixins: [
    ],
    props: {
        rules: {
            type: Object,
            default: null,
        },
        instructions: {
            type: Object,
            default: null,
        },
        limitedFields: {
            type: Array,
            default: null,
        },
    },
    emits: [
        'addAction',
        'deleteAction',
    ],
    data() {
        return {

        };
    },
    computed: {
        hasRequired() {
            return _(this.rules).has('required');
        },
        possibleFields() {
            return this.instructions.possibleFields;
        },
        whichOptions() {
            if (this.limitedFields && this.limitedFields.length) {
                return this.limitedFields;
            }
            return _.isArray(this.possibleFields) ? this.possibleFields : _.keys(this.possibleFields);
        },
    },
    methods: {
        optionText(field) {
            if (_.isArray(this.possibleFields)) {
                return field;
            }
            return this.possibleFields[field];
        },
    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-rules {

}
</style> -->
