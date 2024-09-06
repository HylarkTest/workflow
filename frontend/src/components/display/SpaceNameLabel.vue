<template>
    <span
        class="c-space-name-label"
        :class="spaceClass"
        :title="title"
    >
        <slot>
            {{ spaceName }}
        </slot>
    </span>
</template>

<script>
export default {
    name: 'SpaceNameLabel',
    props: {
        spaceName: {
            type: String,
            required: true,
        },
        size: {
            type: String,
            default: 'base',
            validator: (value) => {
                return ['sm', 'base'].includes(value);
            },
        },
    },
    computed: {
        spaceClass() {
            return `c-space-name-label--${this.size}`;
        },
        title() {
            return `${this.$t('common.space')}: ${this.spaceName}`;
        },
    },
};
</script>

<style>
.c-space-name-label {
    @apply
        bg-cm-100
        font-semibold
        uppercase
    ;

    &--base {
        @apply
            px-2
            py-0.5
            text-cm-400
            text-xssm
        ;
    }

    &--sm {
        @apply
            px-1.5
            py-px
            text-cm-500
            text-xxs
        ;
    }
}
</style>
