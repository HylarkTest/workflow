<template>
    <div class="c-currency-picker">
        <DropdownInput
            v-model:inputVal="filters.freeText"
            v-blur="selectIfOneResultOrReset"
            :modelValue="modelValue"
            class="w-full"
            property="code"
            :options="filteredCurrencies"
            :allOptions="currencies"
            :displayRule="currencyDisplay"
            :placeholder="placeholder"
            dropdownComponent="DropdownBox"
            :neverHighlighted="true"
            :showClear="showClear"
            v-bind="$attrs"
            @update:modelValue="selectCurrency"
        >
        </DropdownInput>
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>
import filterList from '@/core/filterList.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

import currencies from '@/../currency_symbols.json';
import { formatCode, getSymbol } from '@/core/helpers/currencyHelpers.js';

import { $t } from '@/i18n.js';

export default {
    name: 'CurrencyPicker',
    components: {
        AlertTooltip,
    },
    mixins: [
    ],
    props: {
        modelValue: {
            type: [String, Array, null],
            default: null,
        },
        error: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: () => $t('labels.selectCurrency'),
        },
        showClear: Boolean,
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        let freeText = '';
        if (!_.isArray(this.modelValue) && this.modelValue) {
            freeText = formatCode(this.modelValue);
        }
        return {
            filters: {
                freeText,
            },
        };
    },
    computed: {
        filteredCurrencies() {
            if (
                this.modelValue
                && !_.isArray(this.modelValue)
                && this.filters.freeText === formatCode(this.modelValue)
            ) {
                return [{ code: this.modelValue, symbol: getSymbol(this.modelValue) }];
            }
            return filterList(
                this.currencies,
                this.filters,
                {
                    keys: [{
                        name: 'value',
                        getFn: this.currencyName,
                    }],
                }
            );
        },
    },
    methods: {
        updateInput(currency) {
            this.filters.freeText = (currency && formatCode(currency)) || '';
        },
        selectCurrency(currency) {
            if (!_.isArray(this.modelValue)) {
                this.updateInput(currency);
            } else {
                this.filters.freeText = '';
            }
            this.$emit('update:modelValue', currency);
        },
        selectIfOneResultOrReset() {
            const emptyInput = !this.filters.freeText && !this.modelValue;
            if (emptyInput || _.isArray(this.modelValue)) {
                return;
            }
            if (this.filteredCurrencies.length === 1) {
                if (this.filteredCurrencies[0].code === this.modelValue.code) {
                    return;
                }
                this.selectCurrency(this.filteredCurrencies[0].code);
            } else {
                this.selectCurrency(this.modelValue);
            }
        },
        currencyName(currency) {
            return currency ? formatCode(currency.code) : '';
        },
    },
    watch: {
        modelValue(currency) {
            if (!_.isArray(currency)) {
                this.updateInput(currency);
            }
        },
    },
    created() {
        this.currencyDisplay = this.currencyName;
        this.currencies = _.map(currencies, (symbol, code) => ({ symbol, code }));
    },
};
</script>

<style scoped>

/*.c-currency-picker {

} */

</style>
