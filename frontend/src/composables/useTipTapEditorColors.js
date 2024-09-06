import { computed } from 'vue';

import useTipTapEditor from './useTipTapEditor.js';

import { colorMode } from '@/core/repositories/preferencesRepository.js';
import { accentColorsBasic } from '@/core/display/accentColors.js';
import { $t } from '@/i18n.js';

const attributeInfo = {
    textStyle: {
        intensity: 'main',
        defaultColor: {
            DARK: '#ffffff',
            LIGHT: '#000000',
        },
    },
    highlight: {
        intensity: 400,
        defaultColor: {
            DARK: '#000000',
            LIGHT: '#ffffff',
        },
    },
};

export default (props) => {
    const {
        getAttribute,
        runCommands,
    } = useTipTapEditor(props);

    const getDefaultColor = (attribute) => attributeInfo[attribute].defaultColor[colorMode.value];
    const getColor = (attribute) => getAttribute(attribute, 'color');
    const hasColor = (attribute, color) => getColor(attribute) === color;
    const getAllColors = (attribute) => {
        const intensity = attributeInfo[attribute].intensity;
        return [
            getDefaultColor(attribute),
            ...accentColorsBasic.map((color) => color.light[intensity]),
        ];
    };

    const colorOptions = computed(() => {
        return [
            {
                key: 'textStyle',
                text: $t('tiptap.textStyle'),
                icon: 'fal fa-palette',
                isActive: !!getAttribute('textStyle', 'color'),
                action: (swatch) => {
                    if (hasColor('textStyle', swatch) || swatch === getDefaultColor('textStyle')) {
                        runCommands((commands) => commands.unsetColor());
                    } else {
                        runCommands((commands) => commands.setColor(swatch));
                    }
                },
            },
            {
                key: 'highlight',
                text: $t('tiptap.highlight'),
                icon: 'fal fa-highlighter-line',
                isActive: !!getAttribute('highlight', 'color'),
                action: (swatch) => {
                    if (hasColor('highlight', swatch) || swatch === getDefaultColor('highlight')) {
                        runCommands((commands) => commands.unsetHighlight());
                    } else {
                        runCommands((commands) => commands.toggleHighlight({ color: swatch }));
                    }
                },
            },
        ];
    });

    return {
        getDefaultColor,
        getColor,
        hasColor,
        getAllColors,
        colorOptions,
    };
};
