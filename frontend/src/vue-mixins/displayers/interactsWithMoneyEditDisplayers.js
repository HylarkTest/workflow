import CurrencyPicker from '@/components/pickers/CurrencyPicker.vue';

import { getSymbol } from '@/core/helpers/currencyHelpers.js';

export default {
    components: {
        CurrencyPicker,
    },
    computed: {
        currencySymbol() {
            return getSymbol(this.fixedCurrency);
        },
        fixedCurrency() {
            return this.dataInfo.info.options?.currency;
        },
    },
    methods: {
        updateFromToAmount(amount, fromToKey) {
            const path = `amount.${fromToKey}`;
            // updateAmount in components for now
            this.updateAmount(amount, path);
        },
    },
};
