<template>
    <div class="c-displayer-edit-salary flex items-center flex-wrap">

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
            class="mr-2 mb-2"
        >
            {{ currencySymbol }}
        </div>

        <div class="w-24 mr-2 mb-2">
            <InputBox
                :modelValue="salaryInput"
                :placeholder="$t('labels.amount')"
                v-bind="(({ formField, ...attrs }) => attrs)($attrs)"
                @update:modelValue="updateAmount"
            >
            </InputBox>
        </div>

        <div class="w-32 mb-2">
            <DropdownBox
                v-model="salaryPeriod"
                class="w-full"
                placeholder="Salary period"
                :options="periodOptions"
                :displayRule="periodDisplay"
                bgColor="gray"
            >
            </DropdownBox>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import interactsWithMoneyEditDisplayers from '@/vue-mixins/displayers/interactsWithMoneyEditDisplayers.js';
import interactsWithSalaryEditDisplayers from '@/vue-mixins/displayers/interactsWithSalaryEditDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerEditSalary',
    components: {
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
            salaryInput: null,
        };
    },
    computed: {
    },
    methods: {
        updateAmount(amount, path = 'amount') {
            const hasValidChars = this.checkForValidChars(amount);

            if (!hasValidChars) {
                this.salaryInput = this.salaryInput || null;
                const inputAsNumber = parseFloat(this.salaryInput) || null;
                this.updateDataValue(inputAsNumber, path);
            } else {
                const standard = this.formatMoneyStandard(amount);
                this.salaryInput = amount;
                this.updateDataValue(standard, path);
            }
        },
        setInitial() {
            const amount = this.modifiableFieldValue?.amount;
            if (amount) {
                this.salaryInput = this.formatMoneyForDisplay(amount);
            }
        },
    },
    watch: {
        dataValue() {
            if (!this.dataValue) {
                this.salaryInput = null;
            }
        },
    },
    created() {
        this.setInitial();
    },
};
</script>

<style scoped>

/*.c-displayer-edit-salary {

} */

</style>
