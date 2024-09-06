<template>
    <div class="o-feedback-errors text-xs">
        <div
            v-for="(error, index) in errors"
            :key="index"
            class="flex my-2 items-center"
        >
            <i
                class="fa-solid fa-circle-xmark text-violet-600 mr-2"
            >
            </i>

            {{ error }}
        </div>
    </div>
</template>

<script>

export default {
    name: 'FeedbackErrors',
    components: {

    },
    mixins: [
    ],
    props: {
        errors: {
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

/*.o-feedback-errors {

} */

</style>
