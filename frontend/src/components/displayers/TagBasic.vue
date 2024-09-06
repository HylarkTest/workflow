<template>
    <div
        class="c-tag-basic flex items-center rounded-full"
        :class="sizeClass"
        :style="{ backgroundColor: bgColor, color: textColor }"
    >

        <span
            class="font-semibold"
        >
            {{ tag.name }}
        </span>

        <ClearButton
            v-if="!hideRemove"
            positioningClass="ml-2"
            @click.stop="$emit('removeTag', tag)"
        >
        </ClearButton>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'TagBasic',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        tag: {
            type: Object,
            required: true,
        },
        hideRemove: Boolean,
        size: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
    },
    emits: [
        'removeTag',
    ],
    data() {
        return {

        };
    },
    computed: {
        color() {
            return this.tag.color;
        },
        textColor() {
            return this.$root.extraColorDisplay(this.color);
        },
        bgColor() {
            return this.$root.extraColorDisplay(this.color, '100');
        },
        sizeClass() {
            return `c-tag-basic--${this.size}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-tag-basic {
    &--sm {
        @apply
            px-2
            py-0.5
            text-xs
        ;
    }

    &--xs {
        @apply
            px-1.5
            text-xxsxs
        ;
    }
}

</style>
