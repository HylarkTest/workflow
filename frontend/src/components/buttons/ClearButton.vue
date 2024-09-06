<template>
    <button
        class="c-clear-button circle-center"
        :class="[positioningClass, sizeClass, disabledClass]"
        :disabled="disabled"
        type="button"
    >
        <i
            class="fa-times"
            :class="thicknessClass"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'ClearButton',
    components: {

    },
    mixins: [
    ],
    props: {
        positioningClass: {
            type: String,
            default: '-right-3 -top-2 absolute',
        },
        size: {
            type: String,
            default: 'base',
        },
        disabled: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        sizeClass() {
            return `c-clear-button--${this.size}`;
        },
        disabledClass() {
            return { unclickable: this.disabled };
        },
        thicknessClass() {
            return this.size === 'sm' ? 'fa-solid' : 'fa-regular';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-clear-button {
    height: 14px;
    min-width: 14px;
    transition: background-color 0.2s ease-in-out;
    width: 14px;

    @apply
        bg-cm-700
        text-cm-00
        text-xs
    ;

    &--lg {
        height: 20px;
        min-width: 20px;
        width: 20px;

        @apply
            text-base
        ;
    }

    &--sm {
        font-size: 9px;
        height: 12px;
        min-width: 12px;
        width: 12px;
    }

    &:hover {
        @apply
            bg-cm-500
        ;
    }
}

</style>
