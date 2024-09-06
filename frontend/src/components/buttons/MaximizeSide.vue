<template>
    <button
        type="button"
        class="c-maximize-side transition-2eio bg-primary-600 hover:bg-primary-500"
        :title="$t('common.maximize')"
    >
        <i
            class="far fa-list-radio text-xs"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'MaximizeSide',
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

.c-maximize-side {
    height: 30px;
    width: 30px;

    @apply
        flex
        items-center
        justify-center
        px-1
        rounded
        text-cm-00
    ;
}

</style>
