<template>
    <div class="c-displayer-edit-currency">
        <div
            class="mb-6"
        >
            <CurrencyPicker
                :modelValue="currencyVal"
                :showClear="showClear"
                v-bind="$attrs"
                @update:modelValue="updateDataValue"
            >
            </CurrencyPicker>
        </div>

        <div
            v-if="isMulti"
            class="list-value-parent"
        >
            <ValueBasic
                v-for="val in currencyVal"
                :key="val"
                @removeValue="removeValue(val, null)"
            >
                {{ formatCurrency(val) }}
            </ValueBasic>
        </div>
    </div>
</template>

<script>

import CurrencyPicker from '@/components/pickers/CurrencyPicker.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import { formatCode } from '@/core/helpers/currencyHelpers.js';

export default {
    name: 'DisplayerEditCurrency',
    components: {
        CurrencyPicker,
    },
    mixins: [
        interactsWithMultiDisplayers,
        interactsWithDisplayersEdit,
    ],
    props: {
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        currencyVal() {
            if (this.isMulti) {
                return this.modifiableFieldValue || [];
            }
            return this.modifiableFieldValue;
        },
        showClear() {
            return !this.isMulti;
        },
    },
    methods: {
        formatCurrency(code) {
            return formatCode(code);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-currency {

} */

</style>
