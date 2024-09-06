import { userPreferences } from '@/core/repositories/preferencesRepository.js';

import {
    createAccentClasses,
    defaultAccentColor,
} from '@/core/display/accentColors.js';

export default {
    data() {
        return {
            userPreferences,
        };
    },
    computed: {
        colorMode() {
            return this.userPreferences?.colorMode || 'LIGHT';
        },
    },
    methods: {
        setAccentColors(color) {
            const css = createAccentClasses(color, this.colorMode);
            const styleNode = document.getElementById('accent-colors');
            styleNode.innerHTML = css;
        },
    },
    created() {
        this.setAccentColors(defaultAccentColor);
    },
    unmounted() {
        this.setAccentColors(this.userPreferences?.accentColor || defaultAccentColor);
    },
};
