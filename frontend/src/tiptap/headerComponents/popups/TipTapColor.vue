<template>
    <div class="o-tip-tap-color o-tip-tap-editor__buttons">
        <TipTapPopupButton
            v-for="(option) in options"
            :key="option.key"
            :refName="option.key"
            :editor="editor"
            :option="option"
            :isActive="option.isActive"
            :isDeactivated="isGroupDeactivated"
            :backgroundColor="getColor(option.key)"
            :popupWidth="170"
        >
            <template #popup>
                <div class="grid grid-cols-5">
                    <button
                        v-for="swatch in getAllColors(option.key)"
                        :key="swatch"
                        class="o-tip-tap-color__swatch centered hover--grow"
                        :class="{ 'shadow-md': hasColor(option.key, swatch) }"
                        :style="{ backgroundColor: swatch }"
                        type="button"
                        @click.stop="option.action(swatch)"
                    >
                        <i
                            v-if="hasColor(option.key, swatch)"
                            class="fas fa-check text-cm-00 text-sm"
                        >
                        </i>
                        <i
                            v-else-if="getDefaultColor(option.key) === swatch"
                            class="fas fa-x text-rose-500 text-sm"
                        >
                        </i>
                    </button>
                </div>
            </template>
        </TipTapPopupButton>
    </div>
</template>

<script>
import TipTapPopupButton from '@/tiptap/headerComponents/buttons/TipTapPopupButton.vue';

import useTipTapEditorOptions from '@/composables/useTipTapEditorOptions.js';
import useTipTapEditorColors from '@/composables/useTipTapEditorColors.js';

export default {
    name: 'TipTapColor',
    components: {
        TipTapPopupButton,
    },
    props: {
        editor: {
            type: Object,
            required: true,
        },
        isGroupDeactivated: Boolean,
    },
    setup(props) {
        const {
            getOptionsGroup,
        } = useTipTapEditorOptions(props);

        const {
            getColor,
            hasColor,
            getAllColors,
            getDefaultColor,
        } = useTipTapEditorColors(props);

        return {
            getOptionsGroup,

            getColor,
            hasColor,
            getAllColors,
            getDefaultColor,
        };
    },
    computed: {
        options() {
            return this.getOptionsGroup('COLOR');
        },
    },
};
</script>

<style scoped>
.o-tip-tap-color {
    &__swatch {
        @apply
            border
            border-cm-main
            border-solid
            h-7
            hover:shadow
            m-0.5
            rounded-md
            w-7
        ;
    }
}
</style>
