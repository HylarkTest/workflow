<template>
    <div class="c-field-grouping-header">
        <component
            v-if="customComponent"
            :is="customComponent"
            v-bind="componentProps"
        >
        </component>
        <div
            v-else
            class="font-semibold text-xl"
        >
            {{ headerText }}
        </div>
    </div>
</template>

<script>

import { gql } from '@apollo/client';
import { formatCode } from '@/core/helpers/currencyHelpers.js';
import RatingInput from '@/components/inputs/RatingInput.vue';

export default {
    name: 'FieldGroupingHeader',
    components: {
    },
    mixins: [
    ],
    props: {
        header: {
            type: [String, null],
            required: true,
        },
        fieldId: {
            type: String,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        isRatingField() {
            return this.isFieldType('RATING');
        },
        isIconToggleField() {
            return this.isFieldType('BOOLEAN')
                && this.field.meta?.display === 'ICON_TOGGLE';
        },
        customComponent() {
            if (this.isRatingField) {
                return RatingInput;
            }
            if (this.isIconToggleField) {
                return 'i';
            }
            return null;
        },
        componentProps() {
            if (this.isRatingField) {
                return {
                    is: RatingInput,
                    disabled: true,
                    modelValue: parseInt(this.header, 10),
                };
            }
            if (this.isIconToggleField) {
                const textClass = this.header === '1' ? 'text-primary-600' : 'text-gray-400';
                return {
                    class: ['far', this.field.meta.symbol, textClass],
                };
            }
            return null;
        },
        field() {
            return this.mapping.fields.find((f) => f.id === this.fieldId);
        },
        fieldType() {
            return this.field.type;
        },
        headerText() {
            if (this.isFieldType('CURRENCY')) {
                return formatCode(this.header);
            }
            if (this.isFieldType('CATEGORY')) {
                return this.$apollo.provider.defaultClient.cache.readFragment({
                    id: `CategoryItem:${this.header}`,
                    fragment: gql`fragment CategoryItem on CategoryItem { id name }`,
                }).name;
            }
            if (this.isFieldType('SELECT')) {
                return this.field.options.valueOptions[this.header];
            }
            if (this.isFieldType('BOOLEAN')) {
                if (this.field.meta?.display === 'TOGGLE') {
                    return this.header === '1' ? this.$t('labels.on') : this.$t('labels.off');
                }
                return this.header === '1' ? this.$t('common.yes') : this.$t('common.no');
            }
            return this.header;
        },
    },
    methods: {
        isFieldType(type) {
            return this.fieldType === type;
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-grouping-header {
    @apply
        flex
        justify-between
    ;
}

</style>
