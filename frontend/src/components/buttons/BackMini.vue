<template>
    <button
        class="c-back-mini transition-2eio"
        type="button"
    >
        <i class="fal fa-angle-left">
        </i>

        {{ $t('common.back') }}
    </button>
</template>

<script>

export default {
    name: 'BackMini',
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

.c-back-mini {
    @apply
        font-medium
        px-3
        py-2
        text-cm-600
        text-xs
        w-full
    ;

    &:hover {
        @apply
            text-primary-600
        ;
    }
}

</style>
