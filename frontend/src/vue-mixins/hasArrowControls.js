export default {
    data() {
        return {
            hoveredIndex: null,
        };
    },
    computed: {
        optionsLength() {
            return 0;
        },
        arrowKeysActive() {
            return true;
        },
    },
    methods: {
        shouldSkip() {
            return false;
        },
        onKeypress(event) {
            let codes = ['ArrowUp', 'ArrowDown'];
            if (this.hoveredIndex !== null) {
                codes = codes.concat(['Enter', 'Space']);
            }
            if (codes.includes(event.code) && this.arrowKeysActive) {
                event.preventDefault();
                this.updateSelected(event);
            }
        },
        updateSelected(event) {
            const keyCode = event.code;
            if (keyCode === 'ArrowDown') {
                this.arrowDown(event);
            }
            if (keyCode === 'ArrowUp') {
                this.arrowUp(event);
            }
            if (keyCode === 'Enter' || keyCode === 'Space') {
                if (this.hoveredIndex !== null) {
                    this.onSelectOption(this.hoveredIndex, event);
                    this.hoveredIndex = null;
                }
            }
        },
        arrowDown(event) {
            if (this.onDownArrow(this.hoveredIndex, event) === false) {
                return;
            }
            if (this.hoveredIndex === null) {
                this.hoveredIndex = 0;
            } else if (this.hoveredIndex < this.optionsLength - 1) {
                this.hoveredIndex += 1;
            } else {
                this.hoveredIndex = 0;
            }
            if (this.shouldSkip(this.hoveredIndex)) {
                this.arrowDown(event);
            }
        },
        arrowUp(event) {
            if (this.onUpArrow(this.hoveredIndex, event) === false) {
                return;
            }
            const start = this.optionsLength - 1;
            if (this.hoveredIndex === null) {
                this.hoveredIndex = start;
            } else if (this.hoveredIndex !== 0) {
                this.hoveredIndex -= 1;
            } else {
                this.hoveredIndex = start;
            }
            if (this.shouldSkip(this.hoveredIndex)) {
                this.arrowUp(event);
            }
        },
        onDownArrow() {
            return true;
        },
        onUpArrow() {
            return true;
        },
        onSelectOption() {
            return true;
        },
        arrowKeyElement() {
            return this.$el;
        },
        addEventListener() {
            const el = this.arrowKeyElement();
            if (el) {
                el.addEventListener('keydown', this.onKeypress);
            }
        },
        removeEventListener() {
            const el = this.arrowKeyElement();
            if (el) {
                el.removeEventListener('keydown', this.onKeypress);
            }
        },
    },
    mounted() {
        this.addEventListener();
    },
    unmounted() {
        this.removeEventListener();
    },
};
