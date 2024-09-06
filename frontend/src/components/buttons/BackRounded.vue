<template>
    <button
        type="button"
        class="c-back-rounded"
        :class="colorClasses"
    >
        <i
            class="fal fa-angle-left mr-1"
        >
        </i>
        <span
            v-t="buttonTextPath"
        >
        </span>
    </button>
</template>

<script>

export default {
    name: 'BackRounded',
    components: {

    },
    mixins: [
    ],
    props: {
        colorClasses: {
            type: String,
            default: '',
        },
        buttonTextPath: {
            type: String,
            default: 'common.back',
        },
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

.c-back-rounded {
    transition: 0.2s ease-in-out;

    @apply
        font-semibold
        px-3
        py-1
        rounded-full
        shadow-lg
        text-sm
    ;
}

</style>
