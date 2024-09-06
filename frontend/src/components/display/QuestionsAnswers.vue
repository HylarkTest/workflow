<template>
    <div class="c-questions-answers">
        <QuestionAnswer
            v-for="(pair, index) in qaArr"
            :key="index"
            class="mb-6 last:mb-0"
        >
            <template
                #question
            >
                {{ $t(pair.questionPath) }}
            </template>
            <template
                #answer
            >
                {{ $t(pair.answerPath) }}
            </template>
        </QuestionAnswer>
    </div>
</template>

<script>

export default {
    name: 'QuestionsAnswers',
    components: {

    },
    mixins: [
    ],
    props: {
        qaArr: {
            type: Array,
            required: true,
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

/*.c-questions-answers {

} */

</style>
