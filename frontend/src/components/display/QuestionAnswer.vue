<template>
    <div class="c-question-answer">
        <h4
            class="font-semibold text-primary-700 text-smbase mb-1 leading-snug"
        >
            <slot name="question"></slot>
        </h4>

        <p
            class="text-sm text-gray-500 leading-tight"
        >
            <slot name="answer"></slot>
        </p>
    </div>
</template>

<script>

export default {
    name: 'QuestionAnswer',
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

/*.c-question-answer {

} */

</style>
