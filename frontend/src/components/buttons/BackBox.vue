<template>
    <button
        class="c-back-box"
        type="button"
        title="button"
    >
        <i class="fal fa-arrow-to-left"></i>

        <span
            v-if="$slots.default"
            class="ml-2 text-sm"
        >
            <slot></slot>
        </span>
    </button>
</template>

<script>

export default {
    name: 'BackBox',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-back-box {
    @apply
        bg-cm-300
        flex
        items-center
        mb-4
        p-2
    ;

    &:hover {
        @apply
            bg-cm-200
        ;
    }
}
</style>
