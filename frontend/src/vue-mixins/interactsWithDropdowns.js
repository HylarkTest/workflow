import DropdownDisplay from '@/components/dropdowns/DropdownDisplay.vue';
import DropdownOptions from '@/components/dropdowns/DropdownOptions.vue';
import DropdownLabel from '@/components/dropdowns/DropdownLabel.vue';

const popupPropsDefault = {
    popupStyle: {
        padding: '0',
    },
    maxHeightProp: '12.5rem',
};

export default {
    components: {
        DropdownDisplay,
        DropdownOptions,
        DropdownLabel,
    },
    props: {
        /* eslint-disable vue/require-prop-types */
        modelValue: {
            default: null,
        },
        /* eslint-enable vue/require-prop-types */
        inlineLabel: {
            type: [Object, null],
            default: null,
        },
        popupProps: {
            type: Object,
            default: () => ({}),
        },
        size: {
            type: String,
            default: 'base',
            validator(value) {
                return ['lg', 'base', 'sm'].includes(value);
            },
        },
        bgColor: {
            type: String,
            default: 'white',
            validator(value) {
                return ['white', 'gray'].includes(value);
            },
        },
        showDivider: Boolean,
    },
    data() {
        return {
        };
    },
    computed: {
        labelBeside() {
            return this.inlineLabel?.position === 'beside';
        },
        labelOutside() {
            return this.inlineLabel?.position === 'outside';
        },
        labelInside() {
            return this.inlineLabel?.position === 'inside';
        },
        optionsPopupProps() {
            return {
                ...popupPropsDefault,
                ...this.popupProps,
            };
        },
    },
};
