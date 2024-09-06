<template>
    <div class="c-displayer-edit-money flex items-center flex-wrap">
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

        <div class="mb-2 w-24">
            <!-- This code includes a quick one liner for removing a key from an
            object (when lodash isn't available). I thought it was pretty neat -->

            <InputBox
                :modelValue="moneyInput"
                :placeholder="$t('labels.amount')"
                v-bind="(({ formField, ...attrs }) => attrs)($attrs)"
                @update:modelValue="updateAmount"
            >
            </InputBox>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import interactsWithMoneyEditDisplayers from '@/vue-mixins/displayers/interactsWithMoneyEditDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerEditMoney',
    components: {
    },
    mixins: [
        interactsWithDisplayersEdit,
        interactsWithMoneyEditDisplayers,
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
            formatMoneyForDisplay,
            formatMoneyStandard,
            checkForValidChars,
        };
    },
    data() {
        return {
            moneyInput: null,
        };
    },
    computed: {
    },
    methods: {
        updateAmount(amount, path = 'amount') {
            const hasValidChars = this.checkForValidChars(amount);
            if (!hasValidChars) {
                this.moneyInput = this.moneyInput || null;
                const inputAsNumber = parseFloat(this.moneyInput) || null;
                this.updateDataValue(inputAsNumber, path);
            } else {
                const standard = this.formatMoneyStandard(amount);
                this.moneyInput = amount;
                this.updateDataValue(standard, path);
            }
        },
        setInitial() {
            const amount = this.modifiableFieldValue?.amount;
            if (amount) {
                this.moneyInput = this.formatMoneyForDisplay(amount);
            }
        },
    },
    watch: {
        dataValue() {
            if (!this.dataValue) {
                this.moneyInput = null;
            }
        },
    },
    created() {
        this.setInitial();
    },
};
</script>

<style scoped>

/*.c-displayer-edit-money {

} */

</style>
