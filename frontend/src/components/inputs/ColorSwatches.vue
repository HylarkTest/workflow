<template>
    <div class="c-color-swatches">

        <div class="flex mb-2 px-4">
            <button
                v-t="'colors.bright'"
                class="text-xssm button-rounded--sm"
                :class="selectedShadeClasses('BRIGHT')"
                type="button"
                @click="changeShade('BRIGHT')"
            >
            </button>

            <button
                v-t="'colors.regular'"
                class="text-xssm button-rounded--sm"
                :class="selectedShadeClasses('REGULAR')"
                type="button"
                @click="changeShade('REGULAR')"
            >
            </button>
        </div>

        <div class="c-color-swatches__colors px-4">
            <div
                v-for="(group, index) in optionsList"
                :key="index"
                class="flex"
            >
                <button
                    v-for="swatch in group"
                    :key="swatch.val"
                    class="h-7 w-7 m-0.5 rounded-md hover--grow hover:shadow centered"
                    :class="{ 'shadow-md': isSelected(swatch) }"
                    :style="{ backgroundColor: swatch.hex }"
                    type="button"
                    @click="setColor(swatch)"
                >
                    <i
                        v-if="isSelected(swatch)"
                        class="fas fa-check text-cm-00 text-sm"
                    >
                    </i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

import { extraList } from '@/core/display/accentColors.js';

export default {
    name: 'ColorSwatches',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        color: {
            type: String,
            default: '#00ad51',
        },
    },
    emits: [
        'update:color',
    ],
    data() {
        return {
            selectedShade: 'BRIGHT',
        };
    },
    computed: {
        optionsList() {
            return extraList[this.scheme][_.lowerCase(this.selectedShade)];
        },
    },
    methods: {
        isSelectedShade(shade) {
            return this.selectedShade === shade;
        },
        changeShade(shade) {
            this.selectedShade = shade;
        },

        setColor(color) {
            this.$emit('update:color', color.val);
        },
        isSelected(color) {
            return this.color === color.val;
        },
        selectedShadeClasses(shade) {
            return this.isSelectedShade(shade)
                ? 'text-cm-00 bg-primary-600'
                : 'text-cm-500 hover:bg-cm-200';
        },

    },
    created() {
    },
};
</script>

<style scoped>

.c-color-swatches {
    &__colors {
        max-height: 200px;
        overflow-y:  auto;
    }
}

</style>
