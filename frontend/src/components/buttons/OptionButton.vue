<template>
    <button
        class="c-option-button hover:bg-cm-100"
        :class="activeClass"
        type="button"
    >
        <slot></slot>
    </button>
</template>

<script>

export default {
    name: 'OptionButton',
    components: {

    },
    mixins: [
    ],
    props: {
        // label: {
        //     type: String,
        //     required: true,
        // },
        active: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        activeClass() {
            return this.active ? 'bg-primary-100 border-primary-600' : 'bg-cm-00 border-cm-400';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-option-button {
    @apply
        border
        border-solid
        flex
        items-center
        px-4
        py-3
    ;

    /*
    &__label {
        @apply
            block
            font-semibold
            lowercase
            mb-1
            text-cm-600
            text-xxs
        ;
    }
     */
}
</style>
