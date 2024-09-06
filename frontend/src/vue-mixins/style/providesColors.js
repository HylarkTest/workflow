import { accentColors, allColors } from '@/core/display/accentColors.js';

export default {
    computed: {
        scheme() {
            return this.$root.isInDarkMode ? 'dark' : 'light';
        },
        accentColor() {
            return this.$root.accentColor;
        },
    },
    methods: {
        getBgColor(color, intensity = '600') {
            if (color === 'gray') {
                return `bg-cm-${intensity}`;
            }
            return `bg-${color}-${intensity}`;
        },
        getTextColor(color, intensity = '600') {
            if (color === 'gray') {
                return `text-cm-${intensity}`;
            }
            return `text-${color}-${intensity}`;
        },
        getBorderColor(color, intensity = '600') {
            if (color === 'gray') {
                return `border-cm-${intensity}`;
            }
            return `border-${color}-${intensity}`;
        },
        getHoverBgColor(color, intensity = '500') {
            return `hover:bg-${color}-${intensity}`;
        },
        getShadowColor(color, intensity = '300/50') {
            return `shadow-${color}-${intensity}`;
        },
        mainHex(colorObj) {
            return this.getColorHex(colorObj);
        },
        complementaryVal(colorObj) {
            return colorObj.complementary;
        },
        complementaryHex(color, intensity = 'main') {
            let colorObj = color;

            if (_.isString(color)) {
                colorObj = this.findColorObject(color);
            }

            const complementaryVal = this.complementaryVal(colorObj);

            const complementaryObj = this.findColorObject(complementaryVal);

            return this.getColorHex(complementaryObj, intensity);
        },
        getColorHex(color, intensity = 'main') {
            let colorObj = color;

            if (_.isString(color)) {
                colorObj = this.findColorObject(color);
            }

            return colorObj[this.scheme][intensity];
        },
        findColorObject(colorVal) {
            return allColors.find((color) => {
                return color.val === colorVal;
            }) || allColors[0];
        },
        duotoneColors(color) {
            const secondary = this.getColorHex(color, '700');
            const primary = this.complementaryHex(color, '500');

            return {
                '--fa-primary-color': primary,
                '--fa-secondary-color': secondary,
            };
        },
        // getExtraColor(val, intensity = '600') {
        //     const obj = _.find(extraColors, { val });
        //     return obj[this.scheme][intensity];
        // },
    },
    created() {
        this.accentColors = accentColors;
    },
};
