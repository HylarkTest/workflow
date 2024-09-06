<template>
    <div class="c-feedback-page centered flex-col min-h-screen p-8">
        <BirdImage
            class="h-16 ml-3"
            whichBird="FlyingBird_300dpi.png"
        >
        </BirdImage>

        <div class="flex flex-col items-center">
            <p
                v-t="messagePath"
                class="mb-3 max-w-sm font-medium text-center"
            >
            </p>

            <RouterLink
                v-t="linkPath"
                :to="toUrl"
                class="button button-primary"
            >
            </RouterLink>
        </div>
    </div>
</template>

<script>

export default {
    name: 'FeedbackPage',
    components: {

    },
    mixins: [
    ],
    props: {
        messagePath: {
            type: String,
            required: true,
        },
        toUrl: {
            type: [Object, String],
            default: () => ({
                name: 'home',
            }),
        },
        linkPath: {
            type: [String],
            default: 'landing.general.home',
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

/*.c-feedback-page {

} */

</style>
