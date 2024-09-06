<template>
    <div class="c-explanation">
        <slot
            name="title"
        >
            <div class="c-explanation__title">
                <i
                    class="far fa-question-circle mr-1"
                >
                </i>
                <p
                    v-t="mainTitle"
                >
                </p>
            </div>
        </slot>

        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'Explanation',
    components: {

    },
    mixins: [
    ],
    props: {
        mainTitle: {
            type: String,
            default: '',
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

.c-explanation {
    @apply
        bg-emerald-100
        p-4
        rounded-lg
        text-sm
    ;

    &__title {
        @apply
            flex
            font-semibold
            items-center
            mb-2
            text-emerald-800
        ;
    }
}

</style>
