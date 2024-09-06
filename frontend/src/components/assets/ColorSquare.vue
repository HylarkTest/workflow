<template>
    <div
        v-blur="closeColorPicker"
        class="c-color-square"
    >
        <component
            ref="color"
            :is="isModifiable ? 'button' : 'div'"
            class="c-color-square__color"
            :class="'c-color-square__color--' + size"
            :style="{ borderColor: bgColor }"
            :type="isModifiable ? 'button' : ''"
            @click.stop="toggleColorPicker"
        >

        </component>

        <ColorPickerDropdown
            v-if="showColorPicker"
            :color="currentColor"
            :activator="$refs.color"
            nudgeDownProp="0.625rem"
            nudgeRightProp="-1.25rem"
            @update:color="$emit('update:currentColor', $event)"
        >
        </ColorPickerDropdown>
    </div>
</template>

<script>

import ColorPickerDropdown from '@/components/inputs/ColorPickerDropdown.vue';

export default {
    name: 'ColorSquare',
    components: {
        ColorPickerDropdown,
    },
    mixins: [
    ],
    props: {
        currentColor: {
            type: String,
            required: true,
        },
        isModifiable: Boolean,
        size: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'lg'].includes(val);
            },
        },
    },
    emits: [
        'update:currentColor',
    ],
    data() {
        return {
            showColorPicker: false,
        };
    },
    computed: {
        bgColor() {
            return this.$root.extraColorDisplay(this.currentColor);
        },
    },
    methods: {
        toggleColorPicker() {
            if (this.isModifiable) {
                this.showColorPicker = !this.showColorPicker;
            }
        },
        closeColorPicker() {
            this.showColorPicker = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-color-square {
    @apply
        flex
        relative
    ;

    &__color {
        @apply
            border-solid
            rounded
        ;

        &--sm {
            border-width: 2px;
            height:  10px;
            min-width:  10px;
            width:  10px;
        }

        &--lg {
            border-width: 4px;
            height:  18px;
            min-width:  18px;
            width:  18px;
        }
    }
}

</style>
