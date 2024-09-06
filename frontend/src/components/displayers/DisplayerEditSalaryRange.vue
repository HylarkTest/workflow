<template>
    <div class="c-displayer-edit-salary-range">
        <div class="flex items-center flex-wrap">
            <div
                v-if="!fixedCurrency"
                class="mr-2 w-40 mb-2"
            >
                <CurrencyPicker
                    :modelValue="modifiableFieldValue?.currency"
                    v-bind="$attrs"
                    :placeholder="$t('labels.currency')"
                    @update:modelValue="updateDataValue($event, 'currency')"
                >
                </CurrencyPicker>
            </div>

            <div
                v-else
                class="mb-2 mr-2"
            >
                {{ currencySymbol }}
            </div>
            <div class="mb-2 w-32">
                <DropdownBox
                    v-model="salaryPeriod"
                    :options="periodOptions"
                    placeholder="Salary period"
                    :displayRule="periodDisplay"
                    bgColor="gray"
                >
                </DropdownBox>
            </div>
        </div>

        <FromToLine
            class="mb-2"
            inputClass="w-24"
        >
            <InputBox
                :modelValue="fromInput"
                v-bind="$attrs"
                placeholder=""
                @update:modelValue="updateFromToAmount($event, 'from')"
            >
            </InputBox>
        </FromToLine>

        <FromToLine
            class="mb-2"
            format="TO"
            inputClass="w-24"
        >
            <InputBox
                :modelValue="toInput"
                v-bind="$attrs"
                placeholder=""
                @update:modelValue="updateFromToAmount($event, 'to')"
            >
            </InputBox>
        </FromToLine>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import interactsWithMoneyEditDisplayers from '@/vue-mixins/displayers/interactsWithMoneyEditDisplayers.js';
import interactsWithSalaryEditDisplayers from '@/vue-mixins/displayers/interactsWithSalaryEditDisplayers.js';

import FromToLine from '@/components/displayers/FromToLine.vue';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerEditSalaryRange',
    components: {
        FromToLine,
    },
    mixins: [
        interactsWithDisplayersEdit,
        interactsWithMoneyEditDisplayers,
        interactsWithSalaryEditDisplayers,
    ],
    props: {
    },
    emits: [
        'update:dataValue',
    ],
    setup() {
        const {
            formatMoneyStandard,
            checkForValidChars,
            formatMoneyForDisplay,
        } = useMoneyFormat();

        return {
            formatMoneyStandard,
            checkForValidChars,
            formatMoneyForDisplay,
        };
    },
    data() {
        return {
            fromInput: null,
            toInput: null,
        };
    },
    computed: {
        fromAmount() {
            return this.modifiableFieldValue?.amount?.from;
        },
        toAmount() {
            return this.modifiableFieldValue?.amount?.to;
        },
    },
    methods: {
        updateAmount(amount, path = 'amount') {
            const fieldKey = path.includes('from') ? 'from' : 'to';
            const hasValidChars = this.checkForValidChars(amount);

            if (!hasValidChars) {
                const key = `${fieldKey}Input`;
                this[key] = this[key] || null;
                const inputAsNumber = parseFloat(this[key]) || null;
                this.updateDataValue(inputAsNumber, path);
            } else {
                const standard = this.formatMoneyStandard(amount);
                this[`${fieldKey}Input`] = amount;
                this.updateDataValue(standard, path);
            }
        },
        setInitial() {
            if (this.toAmount) {
                this.toInput = this.formatMoneyForDisplay(this.toAmount);
            }
            if (this.fromAmount) {
                this.fromInput = this.formatMoneyForDisplay(this.fromAmount);
            }
        },
    },
    watch: {
        dataValue() {
            if (!this.dataValue) {
                this.fromInput = null;
                this.toInput = null;
            }
        },
    },
    created() {
        this.setInitial();
    },
};
</script>

<style scoped>

/*.c-displayer-edit-salary-range {

} */

</style>
