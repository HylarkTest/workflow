// import { toRefs } from 'vue';

// import useMoneyFormat from '@/composables/useMoneyFormat.js';

// export default function useDisplayerEdit() {
//     const {
//         checkForValidFormat,
//         formatMoneyForDisplay,
//     } = useMoneyFormat();

//     const emitAmountToForm = (amount, path = 'amount') => {
//         const isValidAmount = this.checkForValidFormat(amount);

//         const original = this.modifiableFieldValue?.amount || null;
//         if (!isValidAmount) {
//             this.moneyInput = original;
//             this.updateDataValue(original, path);
//         } else {
//             const standard = this.formatMoneyStandard(amount);
//             this.moneyInput = amount;
//             this.updateDataValue(standard, path);
//         }
//     };

//     return {
//         setMoneyOnForm,
//     };
// }
