import hasArrowControls from './hasArrowControls.js';

export default {
    mixins: [
        hasArrowControls,
    ],
    data() {
        return {
            dropdownOpenKey: 'dropdownVisible',
        };
    },
    computed: {
        arrowKeysActive() {
            return this[this.dropdownOpenKey];
        },
    },
    methods: {
        onKeypress(event) {
            let codes = ['ArrowUp', 'ArrowDown'];
            if (this.hoveredIndex !== null) {
                codes = codes.concat(['Enter', 'Space']);
            }
            if (codes.includes(event.code) && this.arrowKeysActive) {
                event.preventDefault();
                this.updateSelected(event);
                this.scrollOption();
            } else if (event.code === 'ArrowDown' && !this[this.dropdownOpenKey]) {
                this[this.dropdownOpenKey] = true;
            }
        },
        scrollOption() {
            const optionElement = this.getOptionElement(this.hoveredIndex);
            if (optionElement) {
                optionElement.scrollIntoView(false);
            }
        },
        getOptionElement(index) {
            const options = this.getOptionElements();
            return options && options[index];
        },
        shouldSkip(index) {
            const optionElement = this.getOptionElement(index);
            return optionElement && optionElement.disabled;
        },
        getOptionElements() {
            return this.$refs.options;
        },
    },
};
