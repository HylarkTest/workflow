export default {
    methods: {
        bgClass(color, intensity = 600) {
            return color ? `bg-${color}-${intensity}` : '';
        },
        textClass(color) {
            return color ? `text-${color}-600` : '';
        },
        borderClass(color) {
            return color ? `border-${color}-600` : 'border-cm-600';
        },
        hoverBgClass(color, intensity = 100) {
            return color ? `hover:bg-${color}-${intensity}` : `hover:bg-cm-${intensity}`;
        },
        buttonClasses(color) {
            return [
                this.borderClass(color),
                this.textClass(color),
                this.hoverBgClass(color),
            ];
        },
    },
};
