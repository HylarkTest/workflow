<template>
    <component
        :is="dropdownComponent"
        class="c-field-picker"
        :popupProps="{ maxHeightProp: '7.5rem' }"
        :displayRule="fieldsDisplay"
        :options="filteredFields"
        :inactiveOptionCondition="isFieldUnselectable"
        inactiveText="Each regular field can only be selected once"
        placeholder="Select a field"
        v-bind="$attrs"
    >
        <template
            #option="{ original }"
        >
            <div
                class="flex items-baseline w-full"
            >
                <p class="flex-1">
                    {{ original.name }}
                </p>

                <i
                    v-if="isList(original)"
                    class="fa-regular fa-bars mx-2 text-cm-400"
                    title="This is a list field"
                >
                </i>

                <div class="text-xs bg-cm-200 px-1 rounded text-cm-600 font-normal">
                    {{ getFieldTypeName(original) }}
                </div>
            </div>
        </template>

    </component>
</template>

<script>

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

export default {
    name: 'FieldPicker',
    components: {

    },
    mixins: [
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        mappingId: {
            type: String,
            required: true,
        },
        filterFieldTypes: {
            type: [Array, null],
            default: null,
        },
        showFieldConditionFn: {
            type: [Function, null],
            default: null,
        },
        unselectableFieldConditionFn: {
            type: [Function, null],
            default: null,
        },
    },
    apollo: {
        mapping: {
            query: MAPPING,
            variables() {
                return { id: this.mappingId };
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        fields() {
            return this.mapping?.fields || [];
        },
        filteredFields() {
            return this.fields.filter((field) => {
                return this.shouldFieldShow(field);
            });
        },
    },
    methods: {
        shouldFieldShow(field) {
            if (this.filterFieldTypes && !this.filterFieldTypes.includes(field.type)) {
                return false;
            }
            if (_.isFunction(this.showFieldConditionFn)) {
                return this.showFieldConditionFn(field);
            }
            return true;
        },
        isFieldUnselectable(field) {
            if (_.isFunction(this.unselectableFieldConditionFn)) {
                return this.unselectableFieldConditionFn(field);
            }
            return false;
        },
        isList(field) {
            return field.options?.list;
        },
        getFieldTypeName(field) {
            const camelType = _.camelCase(field.val);
            return this.$t(`fields.types.${camelType}`);
        },
    },
    created() {
        this.fieldsDisplay = (field) => field.name;
    },
};
</script>

<style scoped>

/*.c-field-picker {

} */

</style>
