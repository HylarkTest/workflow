import { getSymbol } from '@/core/helpers/currencyHelpers.js';

export default {
    computed: {
        currencySymbol() {
            return getSymbol(this.displayFieldValue?.currency);
        },
        amount() {
            return this.displayFieldValue?.amount;
        },
        from() {
            return this.amount?.from;
        },
        to() {
            return this.amount?.to;
        },
    },
};
